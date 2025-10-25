<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntityEquipmentRequest;
use App\Http\Requests\UpdateEntityEquipmentRequest;
use App\Models\Entity;
use App\Models\EntityEquipment;
use App\Models\EquipmentCategory;

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
        $locations = collect($roomsData)->pluck('name')->filter()->unique()->all();

        $equipments = $entity->equipments()->with('equipmentType.equipmentCategory')->latest()->get();

        return view('equipment.create', compact('entity', 'categories', 'locations', 'equipments'));
    }

    /**
     * (STORE) Guarda el nuevo equipo en la base de datos.
     */
    public function store(StoreEntityEquipmentRequest $request, Entity $entity)
    {
        $this->authorize('create', [EntityEquipment::class, $entity]);
        $entity->equipments()->create($request->validated());

        return redirect()->route('entities.show', $entity)
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
        $locations = collect($roomsData)->pluck('name')->filter()->unique()->all();

        return view('equipment.edit', compact('equipment', 'categories', 'locations'));
    }

    /**
     * (UPDATE) Actualiza un equipo en la base de datos.
     */
    public function update(UpdateEntityEquipmentRequest $request, EntityEquipment $equipment)
    {
        $this->authorize('update', $equipment);
        $equipment->update($request->validated());

        return redirect()->route('entities.equipment.index', $equipment->entity)
                         ->with('success', '¡Equipo actualizado con éxito!');
    }

    /**
     * (DESTROY) Elimina un equipo del inventario.
     */
    public function destroy(EntityEquipment $equipment)
    {
        $this->authorize('delete', $equipment);
        $entity = $equipment->entity;
        $equipment->delete();

        return redirect()->route('entities.equipment.index', $entity)
                         ->with('success', '¡Equipo eliminado con éxito!');
    }
}