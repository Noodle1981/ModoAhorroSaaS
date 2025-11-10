<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntityEquipmentRequest;
use App\Http\Requests\UpdateEntityEquipmentRequest;
use App\Models\Entity;
use App\Models\EntityEquipment;
use App\Models\EquipmentCategory;
use App\Models\ProcessFactor;

class EntityEquipmentController extends Controller
{
    /**
     * (INDEX) Muestra el inventario de equipos de una entidad.
     */
    public function index(Entity $entity)
    {
        $this->authorize('viewAny', [EntityEquipment::class, $entity]);

        $equipments = $entity->equipments()->with('equipmentType.equipmentCategory')->latest()->get();

        return view('equipment.index', compact('entity', 'equipments'));
    }

    /**
     * (CREATE) Muestra el formulario para añadir un nuevo equipo a una entidad.
     */
    public function create(Entity $entity)
    {
        $this->authorize('create', [EntityEquipment::class, $entity]);

        $categories = EquipmentCategory::with('equipmentTypes')->orderBy('name')->get();
        
        $roomsData = $entity->details['rooms'] ?? [];
        $locations = collect($roomsData)->pluck('name')->filter()->unique();
        
        // Siempre incluir "Portátiles" en las ubicaciones disponibles
        if (!$locations->contains('Portátiles')) {
            $locations->prepend('Portátiles');
        }

        $equipments = $entity->equipments()->with('equipmentType.equipmentCategory')->latest()->get();

        return view('equipment.create', compact('entity', 'categories', 'locations', 'equipments'));
    }

    /**
     * (STORE) Guarda el nuevo equipo en la base de datos.
     */
    public function store(StoreEntityEquipmentRequest $request, Entity $entity)
    {
        $this->authorize('create', [EntityEquipment::class, $entity]);
        
        $data = $request->validated();
        
        // Si no se especificó fecha de activación, usar hoy por defecto
        if (!isset($data['activated_at'])) {
            $data['activated_at'] = now()->toDateString();
        }
        
        // Asignar factores y calcular energías automáticamente
        $equipment = $entity->equipments()->create($data);
        $this->assignFactorsAndCalculate($equipment);

        return redirect()->route('entities.equipment.index', $entity)
                         ->with('success', '¡Equipo añadido con éxito!');
    }

    /**
     * (SHOW) Muestra los detalles de un equipo específico.
     */
    public function show(EntityEquipment $equipment)
    {
        $this->authorize('view', $equipment);

        return view('equipment.show', compact('equipment'));
    }

    /**
     * (EDIT) Muestra el formulario para editar un equipo del inventario.
     */
    public function edit(EntityEquipment $equipment)
    {
        $this->authorize('update', $equipment);

        $categories = EquipmentCategory::with('equipmentTypes')->orderBy('name')->get();

        $roomsData = $equipment->entity->details['rooms'] ?? [];
        $locations = collect($roomsData)->pluck('name')->filter()->unique();
        
        // Siempre incluir "Portátiles" en las ubicaciones disponibles
        if (!$locations->contains('Portátiles')) {
            $locations->prepend('Portátiles');
        }

        return view('equipment.edit', compact('equipment', 'categories', 'locations'));
    }

    /**
     * (UPDATE) Actualiza un equipo en la base de datos.
     */
    public function update(UpdateEntityEquipmentRequest $request, EntityEquipment $equipment)
    {
        $this->authorize('update', $equipment);
        $equipment->update($request->validated());
        
        // Recalcular factores y energías si cambió tipo_de_proceso o parámetros de uso
        $this->assignFactorsAndCalculate($equipment);

        return redirect()->route('entities.equipment.index', $equipment->entity)
                         ->with('success', '¡Equipo actualizado con éxito!');
    }

    /**
     * (DESTROY) Elimina un equipo del inventario (SOFT DELETE por defecto).
     * Mantiene el histórico de snapshots.
     */
    public function destroy(EntityEquipment $equipment)
    {
        $this->authorize('delete', $equipment);
        $entity = $equipment->entity;
        
        // Soft delete (marca como deleted_at, Observer se encarga del resto)
        $equipment->delete();

        return redirect()->route('entities.equipment.index', $entity)
                         ->with('success', '✅ Equipo dado de baja. Se mantiene el histórico de consumo.');
    }

    /**
     * HARD DELETE: Eliminar permanentemente (solo si fue un error de carga).
     * ELIMINA todos los snapshots asociados.
     */
    public function forceDestroy(EntityEquipment $equipment)
    {
        $this->authorize('delete', $equipment);
        $entity = $equipment->entity;
        
        $equipmentName = $equipment->custom_name ?? $equipment->equipmentType->name;
        
        // Contar snapshots afectados
        $snapshotsCount = $equipment->snapshots()->count();
        
        // Force delete (elimina físicamente, Observer se encarga del resto)
        $equipment->forceDelete();

        return redirect()->route('entities.equipment.index', $entity)
                         ->with('warning', sprintf(
                             '⚠️ Equipo "%s" eliminado permanentemente. Se eliminaron %d snapshot(s) históricos.',
                             $equipmentName,
                             $snapshotsCount
                         ));
    }

    /**
     * Asigna automáticamente factor_carga y eficiencia según tipo_de_proceso,
     * y calcula energia_consumida_wh según la lógica del script Python.
     */
    private function assignFactorsAndCalculate(EntityEquipment $equipment)
    {
        // Si tiene tipo_de_proceso, buscar los factores
        if ($equipment->tipo_de_proceso) {
            $factor = ProcessFactor::where('tipo_de_proceso', $equipment->tipo_de_proceso)->first();
            if ($factor) {
                $equipment->factor_carga = $factor->factor_carga;
                $equipment->eficiencia = $factor->eficiencia;
            }
        }

        // Si no tiene factores asignados, usar defaults
        if (!$equipment->factor_carga) {
            $equipment->factor_carga = 1;
        }
        if (!$equipment->eficiencia) {
            $equipment->eficiencia = 1;
        }

        // Calcular energía consumida (Wh) según la lógica Python
        // EnergiaConsumida_Wh = horas_por_dia * factor_carga * cantidad * potencia_watts / eficiencia
        $powerWatts = $equipment->power_watts_override ?? $equipment->equipmentType?->default_power_watts ?? 0;
        $quantity = $equipment->quantity ?? 1;
        $avgDailyUseMinutes = $equipment->avg_daily_use_minutes_override ?? (($equipment->equipmentType?->default_avg_daily_use_hours ?? 0) * 60);
        $horasPorDia = $avgDailyUseMinutes / 60.0;

        $equipment->energia_consumida_wh = ($horasPorDia * $equipment->factor_carga * $quantity * $powerWatts) / $equipment->eficiencia;
        $equipment->energia_util_consumida_wh = $horasPorDia * $equipment->factor_carga * $quantity * $powerWatts * $equipment->eficiencia;

        // Guardar silenciosamente para no disparar observers innecesarios
        $equipment->saveQuietly();
    }
}