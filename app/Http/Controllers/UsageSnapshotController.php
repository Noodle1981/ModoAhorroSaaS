<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsageSnapshotRequest; // ¡Usaremos un Form Request!
use App\Models\Invoice;
use App\Models\EquipmentUsageSnapshot;
use App\Services\InventoryAnalysisService; // Necesitamos el servicio para los cálculos
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; // Importar Request

class UsageSnapshotController extends Controller
{
    /**
     * Muestra el formulario para confirmar el uso del inventario para un período de factura.
     */
    public function create(Invoice $invoice)
    {
        // 1. Autorización: Asegurarnos de que el usuario es dueño de esta factura.
        $this->authorize('view', $invoice);

        // 2. Obtenemos la entidad asociada a la factura.
        $entity = $invoice->contract->supply->entity;

        // 3. Obtenemos el inventario de equipos de esa entidad.
        $equipments = $entity->entityEquipments()->with('equipmentType')->get()->map(function ($equipment) use ($invoice) {
            
            // 4. Buscamos el último uso registrado para este equipo para pre-rellenar el campo.
            $lastSnapshot = EquipmentUsageSnapshot::where('entity_equipment_id', $equipment->id)
                ->latest('end_date')
                ->first();
            
            // Si no hay snapshot anterior, usamos el default del tipo de equipo.
            $equipment->previous_usage_minutes = $lastSnapshot->avg_daily_use_minutes ?? $equipment->equipmentType->default_avg_daily_use_minutes ?? 0;

            return $equipment;
        });

        // 5. Pasamos la factura y la lista de equipos a la vista.
        return view('snapshots.create', compact('invoice', 'equipments'));
    }

    /**
     * Almacena los datos de uso de los equipos para el período de la factura.
     */
    public function store(StoreUsageSnapshotRequest $request, Invoice $invoice, InventoryAnalysisService $analysisService)
    {
        // La validación ya se ejecutó gracias a StoreUsageSnapshotRequest
        $this->authorize('view', $invoice);
        
        $validatedSnapshots = $request->validated()['snapshots'];

        // Usamos una transacción para asegurar que todos los snapshots se guarden o ninguno.
        DB::transaction(function () use ($validatedSnapshots, $invoice, $analysisService) {
            foreach ($validatedSnapshots as $snapshotData) {
                // Obtenemos el equipo para tener todos sus datos
                $equipment = \App\Models\EntityEquipment::with('equipmentType.equipmentCategory.calculationFactor')->find($snapshotData['entity_equipment_id']);

                if (!$equipment) continue;

                // --- LÓGICA PARA CONVERTIR HORAS A MINUTOS ---
                $minutes = 0;
                if (isset($snapshotData['avg_daily_use_hours'])) {
                    $minutes = (int)($snapshotData['avg_daily_use_hours'] * 60);
                } elseif (isset($snapshotData['avg_daily_use_minutes'])) {
                    $minutes = (int)$snapshotData['avg_daily_use_minutes'];
                }

                // Preparamos los datos para guardar, incluyendo los datos "congelados" en el tiempo.
                $dataToStore = [
                    'entity_equipment_id' => $equipment->id,
                    'invoice_id' => $invoice->id,
                    'start_date' => $invoice->start_date,
                    'end_date' => $invoice->end_date,
                    'avg_daily_use_minutes' => $minutes,
                    'power_watts' => $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts ?? 0,
                    'has_standby_mode' => $equipment->has_standby_mode, // Asumimos que este valor viene del equipo principal
                ];

                // Calculamos el consumo para este período y lo guardamos.
                $periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;
                $calculatedConsumption = $analysisService->calculateConsumptionForEquipment($equipment, $periodDays, $minutes);
                $dataToStore['calculated_kwh_period'] = $calculatedConsumption['consumo_kwh_total_periodo'];
                
                EquipmentUsageSnapshot::create($dataToStore);
            }
        });

        return redirect()->route('entities.show', $invoice->contract->supply->entity_id)
                         ->with('success', 'El uso de los equipos ha sido registrado correctamente para este período.');
    }

    /**
     * (EDIT) Muestra el formulario para editar/calibrar el uso de una factura existente.
     */
    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->load('snapshots.entityEquipment.equipmentType');
        
        $entity = $invoice->contract->supply->entity;
        $allEquipments = $entity->entityEquipments()->withTrashed()->with('equipmentType')->get();

        // Mapeamos los snapshots existentes para un acceso fácil en la vista
        $snapshotsByEquipmentId = $invoice->snapshots->keyBy('entity_equipment_id');

        return view('snapshots.edit', compact('invoice', 'allEquipments', 'snapshotsByEquipmentId'));
    }

    /**
     * (UPDATE) Actualiza los snapshots de uso para una factura.
     */
    public function update(StoreUsageSnapshotRequest $request, Invoice $invoice, InventoryAnalysisService $analysisService)
    {
        $this->authorize('update', $invoice);

        $validatedSnapshots = $request->validated()['snapshots'];

        DB::transaction(function () use ($validatedSnapshots, $invoice, $analysisService) {
            foreach ($validatedSnapshots as $snapshotData) {
                $equipment = \App\Models\EntityEquipment::with('equipmentType.equipmentCategory.calculationFactor')->find($snapshotData['entity_equipment_id']);
                if (!$equipment) continue;

                $minutes = 0;
                if (isset($snapshotData['avg_daily_use_hours'])) {
                    $minutes = (int)($snapshotData['avg_daily_use_hours'] * 60);
                } elseif (isset($snapshotData['avg_daily_use_minutes'])) {
                    $minutes = (int)$snapshotData['avg_daily_use_minutes'];
                }

                $periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;
                $calculatedConsumption = $analysisService->calculateConsumptionForEquipment($equipment, $periodDays, $minutes);

                EquipmentUsageSnapshot::updateOrCreate(
                    [
                        'invoice_id' => $invoice->id,
                        'entity_equipment_id' => $equipment->id,
                    ],
                    [
                        'start_date' => $invoice->start_date,
                        'end_date' => $invoice->end_date,
                        'avg_daily_use_minutes' => $minutes,
                        'power_watts' => $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts ?? 0,
                        'has_standby_mode' => $equipment->has_standby_mode,
                        'calculated_kwh_period' => $calculatedConsumption['consumo_kwh_total_periodo'],
                    ]
                );
            }
        });

        return redirect()->route('entities.show', $invoice->contract->supply->entity_id)
                         ->with('success', 'Calibración de consumo guardada exitosamente.');
    }

    /**
     * (API) Recalcula el consumo total estimado para la calibración en vivo.
     */
    public function recalculate(Request $request, InventoryAnalysisService $analysisService)
    {
        $validated = $request->validate([
            'snapshots' => ['required', 'array'],
            'snapshots.*.entity_equipment_id' => ['required', 'exists:entity_equipment,id'],
            'snapshots.*.avg_daily_use_hours' => ['nullable', 'numeric', 'min:0'],
            'snapshots.*.avg_daily_use_minutes' => ['nullable', 'integer', 'min:0'],
            'period_days' => ['required', 'integer', 'min:1'],
        ]);

        $totalEstimatedConsumption = 0;

        foreach ($validated['snapshots'] as $snapshotData) {
            $equipment = \App\Models\EntityEquipment::with('equipmentType.equipmentCategory.calculationFactor')->find($snapshotData['entity_equipment_id']);
            if (!$equipment) continue;

            $minutes = 0;
            if (isset($snapshotData['avg_daily_use_hours'])) {
                $minutes = (int)($snapshotData['avg_daily_use_hours'] * 60);
            } elseif (isset($snapshotData['avg_daily_use_minutes'])) {
                $minutes = (int)$snapshotData['avg_daily_use_minutes'];
            }

            $consumption = $analysisService->calculateConsumptionForEquipment($equipment, $validated['period_days'], $minutes);
            $totalEstimatedConsumption += $consumption['consumo_kwh_total_periodo'];
        }

        return response()->json([
            'estimated_consumption' => round($totalEstimatedConsumption, 2),
        ]);
    }
}