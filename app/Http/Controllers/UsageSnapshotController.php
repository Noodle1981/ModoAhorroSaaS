<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\EntityEquipment;
use App\Models\EquipmentUsageSnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsageSnapshotController extends Controller
{
    /**
     * Muestra el formulario para ajustar el uso de equipos para un período específico (factura).
     */
    public function create(Invoice $invoice)
    {
        // Obtenemos la entidad a través del suministro del contrato de la factura
        $entity = $invoice->contract->supply->entity;
        
        // Verificamos que el usuario tenga permiso para ver esta entidad
        $this->authorize('view', $entity);
        
        // Obtenemos todos los equipos de la entidad
        $equipments = $entity->equipments()->with(['equipmentType', 'equipmentType.equipmentCategory'])->get();
        
        // Si no hay equipos, redirigimos a agregar equipos
        if ($equipments->isEmpty()) {
            return redirect()
                ->route('entities.equipment.index', $entity)
                ->with('warning', 'Primero debes agregar equipos a tu inventario antes de ajustar el consumo.');
        }
        
        // Obtenemos los snapshots existentes para esta factura (si ya se ajustaron antes)
        $existingSnapshots = EquipmentUsageSnapshot::where('invoice_id', $invoice->id)
            ->get()
            ->keyBy('entity_equipment_id');
        
        // Calculamos los días del período
        $periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;
        
        return view('snapshots.create', [
            'invoice' => $invoice,
            'entity' => $entity,
            'equipments' => $equipments,
            'existingSnapshots' => $existingSnapshots,
            'periodDays' => $periodDays,
        ]);
    }

    /**
     * Guarda los ajustes de uso de equipos para el período de la factura.
     */
    public function store(Request $request, Invoice $invoice)
    {
        $entity = $invoice->contract->supply->entity;
        $this->authorize('view', $entity);
        
        // Validamos los datos
        $request->validate([
            'equipments' => 'required|array',
            'equipments.*.entity_equipment_id' => 'required|exists:entity_equipments,id',
            'equipments.*.avg_daily_use_minutes' => 'required|numeric|min:0|max:1440',
            'equipments.*.has_standby_mode' => 'nullable|boolean',
        ]);
        
        DB::transaction(function () use ($request, $invoice) {
            // Soft-delete snapshots anteriores de esta factura (mantener historial)
            EquipmentUsageSnapshot::where('invoice_id', $invoice->id)->delete();
            
            // Creamos los nuevos snapshots
            foreach ($request->equipments as $equipmentData) {
                $entityEquipment = EntityEquipment::with('equipmentType')->find($equipmentData['entity_equipment_id']);
                
                // Obtenemos la potencia real (override o default)
                $powerWatts = $entityEquipment->power_watts_override ?? $entityEquipment->equipmentType->default_power_watts;
                
                // Determinar si el usuario activó standby para este período
                $hasStandby = isset($equipmentData['has_standby_mode']) ? (bool)$equipmentData['has_standby_mode'] : (bool)($entityEquipment->has_standby_mode ?? false);

                // Calculamos el consumo del período (activo + standby si corresponde)
                $periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;
                $dailyUseHours = $equipmentData['avg_daily_use_minutes'] / 60;
                $totalHours = $dailyUseHours * $periodDays;
                $activeKwh = ($powerWatts / 1000) * $totalHours;

                $standbyWatts = $hasStandby ? ($entityEquipment->equipmentType->standby_power_watts ?? 0) : 0;
                $standbyHours = max(0, ($periodDays * 24) - $totalHours);
                $standbyKwh = ($standbyWatts / 1000) * $standbyHours * max(1, (int)$entityEquipment->quantity);

                $calculatedKwh = $activeKwh + $standbyKwh;
                
                EquipmentUsageSnapshot::create([
                    'entity_equipment_id' => $equipmentData['entity_equipment_id'],
                    'invoice_id' => $invoice->id,
                    'start_date' => $invoice->start_date,
                    'end_date' => $invoice->end_date,
                    'avg_daily_use_minutes' => $equipmentData['avg_daily_use_minutes'],
                    'power_watts' => $powerWatts,
                    'has_standby_mode' => $hasStandby,
                    'calculated_kwh_period' => $calculatedKwh,
                ]);
            }
        });
        
        return redirect()
            ->route('entities.show', $entity)
            ->with('success', 'Ajustes de equipos guardados exitosamente. El análisis se ha actualizado.');
    }
}
