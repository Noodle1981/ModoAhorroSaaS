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
     * (CREATE) Muestra el formulario para añadir un nuevo equipo a una entidad.
     */
    public function create(Entity $entity)
   {
    $this->authorize('create', [EntityEquipment::class, $entity]);

    $categories = EquipmentCategory::with('equipmentTypes')->orderBy('name')->get();
    
    // --- ¡LÓGICA CORRECTA PARA UBICACIONES! ---
    // Leemos el array 'rooms' directamente del JSON 'details' de la entidad.
    $roomsData = $entity->details['rooms'] ?? [];
    // Extraemos solo los nombres para pasarlos a la vista.
    $locations = collect($roomsData)->pluck('name')->filter()->unique()->all();

    // Obtenemos los equipos existentes para mostrarlos en la tabla.
    $equipments = $entity->entityEquipment()->with('equipmentType.equipmentCategory')->latest()->get();

    return view('equipment.create', compact('entity', 'categories', 'locations', 'equipments'));
}

    /**
     * (EDIT) Muestra el formulario para editar un equipo del inventario.
     */
    public function edit(EntityEquipment $equipment)
  {
    $this->authorize('update', $equipment);

    $categories = EquipmentCategory::with('equipmentTypes')->orderBy('name')->get();

    // --- ¡MISMA LÓGICA CORRECTA AQUÍ! ---
    $roomsData = $equipment->entity->details['rooms'] ?? [];
    $locations = collect($roomsData)->pluck('name')->filter()->unique()->all();

    return view('equipment.edit', compact('equipment', 'categories', 'locations'));
}

    /**
     * (STORE) Guarda el nuevo equipo en la base de datos.
     */
    public function store(StoreEntityEquipmentRequest $request, Entity $entity)
    {
        $this->authorize('create', [EntityEquipment::class, $entity]);
        $entity->entityEquipment()->create($request->validated());

        // Redirigimos a la página de detalles de la entidad con un mensaje de éxito.
        return redirect()->route('entities.show', $entity)
                         ->with('success', '¡Equipo añadido con éxito!');
    }

    // ... (El resto de los métodos: update, show, destroy, que ya están bien)
}