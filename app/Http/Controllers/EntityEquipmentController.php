<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntityEquipmentRequest;
use App\Http\Requests\UpdateEntityEquipmentRequest;
use App\Models\Entity;
use App\Models\EntityEquipment;
use App\Models\EquipmentCategory; // Importante
use App\Models\EquipmentType;

class EntityEquipmentController extends Controller
{
    /**
     * (CREATE) Muestra el formulario para añadir un nuevo equipo a una entidad.
     */
    public function create(Entity $entity)
    {
        $this->authorize('create', [EntityEquipment::class, $entity]);

        $categories = EquipmentCategory::with('equipmentTypes')->orderBy('name')->get();
        $locations = EntityEquipment::getUniqueLocationsForEntity($entity);

        return view('equipment.create', compact('entity', 'categories', 'locations'));
    }

    /**
     * (EDIT) Muestra el formulario para editar un equipo del inventario.
     */
    public function edit(EntityEquipment $equipment)
    {
        $this->authorize('update', $equipment);

        $categories = EquipmentCategory::with('equipmentTypes')->orderBy('name')->get();
        $locations = EntityEquipment::getUniqueLocationsForEntity($equipment->entity);

        return view('equipment.edit', compact('equipment', 'categories', 'locations'));
    }

    /**
     * (STORE) Guarda el nuevo equipo en la base de datos.
     */
    public function store(StoreEntityEquipmentRequest $request, Entity $entity)
    {
        $this->authorize('create', [EntityEquipment::class, $entity]);
        $entity->entityEquipment()->create($request->validated());
        return redirect()->route('entities.show', $entity)->with('success', 'Equipo añadido al inventario.');
    }

    /**
     * (UPDATE) Actualiza el equipo en la base de datos.
     */
    public function update(UpdateEntityEquipmentRequest $request, EntityEquipment $equipment)
    {
        $this->authorize('update', $equipment);
        $equipment->update($request->validated());
        return redirect()->route('entities.show', $equipment->entity)->with('success', 'Equipo actualizado.');
    }

    /**
     * (SHOW) Muestra los detalles de un equipo específico del inventario.
     */
    public function show(EntityEquipment $equipment)
    {
        $this->authorize('view', $equipment);
        $equipment->load('entity', 'equipmentType.equipmentCategory'); 
        return view('equipment.show', compact('equipment'));
    }
    
    /**
     * (DESTROY) Elimina el equipo del inventario.
     */
    public function destroy(EntityEquipment $equipment)
    {
        $this->authorize('delete', $equipment);
        $entity = $equipment->entity;
        $equipment->delete();
        return redirect()->route('entities.show', 'entity')->with('success', 'Equipo eliminado.');
    }
}