<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsageSnapshotRequest; // ¡Usaremos un Form Request!
use App\Models\Invoice;
use App\Models\EquipmentUsageSnapshot;
use App\Services\InventoryAnalysisService; // Necesitamos el servicio para los cálculos

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
        $equipments = $entity->entityEquipment()->with('equipmentType')->get()->map(function ($equipment) use ($invoice) {
            
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

                // Preparamos los datos para guardar, incluyendo los datos "congelados" en el tiempo.
                $dataToStore = [
                    'entity_equipment_id' => $equipment->id,
                    'invoice_id' => $invoice->id,
                    'start_date' => $invoice->start_date,
                    'end_date' => $invoice->end_date,
                    'avg_daily_use_minutes' => $snapshotData['avg_daily_use_minutes'],
                    'power_watts' => $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts ?? 0,
                    'has_standby_mode' => $equipment->has_standby_mode, // Asumimos que este valor viene del equipo principal
                ];

                // Calculamos el consumo para este período y lo guardamos.
                $periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;
                $calculatedProfile = $analysisService->calculateEnergyProfileForPeriod($equipment, $periodDays);
                $dataToStore['calculated_kwh_period'] = $calculatedProfile->consumo_kwh_total_periodo;
                
                EquipmentUsageSnapshot::create($dataToStore);
            }
        });

        return redirect()->route('contracts.show', $invoice->contract)
                         ->with('success', 'El uso de los equipos ha sido registrado correctamente para este período.');
    }
}