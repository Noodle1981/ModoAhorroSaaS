<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntityEquipmentRequest;
use App\Http\Requests\UpdateEntityEquipmentRequest;
use App\Models\Entity;
use App\Models\EntityEquipment;
use App\Models\EquipmentType;

class EntityEquipmentController extends Controller
{
    /**
     * (CREATE) Muestra el formulario para añadir un nuevo equipo a una entidad.
     */
    public function create(Entity $entity)
    {
        $this->authorize('create', [EntityEquipment::class, $entity]);

        $equipmentTypes = EquipmentType::orderBy('name')->get();
        return view('equipment.create', compact('entity', 'equipmentTypes'));
    }

    /**
     * (STORE) Guarda el nuevo equipo en la base de datos.
     */
    public function store(StoreEntityEquipmentRequest $request, Entity $entity)
    {
        $this->authorize('create', [EntityEquipment::class, $entity]);

        $entity->entityEquipment()->create($request->validated());

        return redirect()->route('entities.show', $entity)
                         ->with('success', 'Equipo añadido al inventario exitosamente.');
    }

    /**
     * (SHOW) Muestra los detalles de un equipo específico del inventario.
     */
    public function show(EntityEquipment $equipment)
    {
        $this->authorize('view', $equipment);
        $equipment->load('entity', 'equipmentType');
        return view('equipment.show', compact('equipment'));
    }

    /**
     * (EDIT) Muestra el formulario para editar un equipo del inventario.
     */
    public function edit(EntityEquipment $equipment)
    {
        $this->authorize('update', $equipment);
        $equipmentTypes = EquipmentType::orderBy('name')->get();
        return view('equipment.edit', compact('equipment', 'equipmentTypes'));
    }

    /**
     * (UPDATE) Actualiza el equipo en la base de datos.
     */
    public function update(UpdateEntityEquipmentRequest $request, EntityEquipment $equipment)
    {
        $this->authorize('update', $equipment);
        
        $equipment->update($request->validated());

        return redirect()->route('equipment.show', $equipment)
                         ->with('success', 'Equipo actualizado exitosamente.');
    }

    /**
     * (DESTROY) Elimina el equipo del inventario.
     */
    public function destroy(EntityEquipment $equipment)
    {
        $this->authorize('delete', $equipment);
        
        $entity = $equipment->entity;
        $equipment->delete();

        return redirect()->route('entities.show', $entity)
                         ->with('success', 'Equipo eliminado del inventario exitosamente.');
    }
}