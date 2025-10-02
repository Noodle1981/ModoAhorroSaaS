<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntityEquipmentRequest;
use App\Http\Requests\UpdateEntityEquipmentRequest;
use App\Models\Entity;
use App\Models\EntityEquipment;
use App\Models\EquipmentCategory;
use Illuminate\Http\Request;

class EntityEquipmentController extends Controller
{
    /**
     * (CREATE) Muestra el formulario para añadir un nuevo equipo a una entidad.
     */
    public function create(Request $request, Entity $entity)
    {
        $this->authorize('create', [EntityEquipment::class, $entity]);

        $type = $request->query('type'); // 'fixed' or 'portable'

        $categories = EquipmentCategory::with(['equipmentTypes' => function ($query) use ($type) {
            if ($type === 'fixed') {
                $query->where('is_portable', false);
            } elseif ($type === 'portable') {
                $query->where('is_portable', true);
            }
        }])->orderBy('name')->get();

        // Remove categories that ended up with no equipment types after filtering
        $categories = $categories->filter(fn ($category) => $category->equipmentTypes->isNotEmpty());

        // --- LÓGICA PARA UBICACIONES SEGÚN EL TIPO ---
        if ($type === 'portable') {
            $locations = ['Portátil']; // Ubicación fija para equipos portátiles
        } else {
            $roomsData = $entity->details['rooms'] ?? [];
            $locations = collect($roomsData)->pluck('name')->filter()->unique()->all();
        }

        // Obtenemos los equipos existentes para mostrarlos en la tabla.
        $equipments = $entity->entityEquipments()->with('equipmentType.equipmentCategory')->latest()->get();

        return view('equipment.create', compact('entity', 'categories', 'locations', 'equipments', 'type'));
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
        
        // 1. Crear el nuevo equipo
        $newEquipment = $entity->entityEquipments()->create($request->validated());

        // 2. Si es un reemplazo, actualizar el equipo antiguo
        if ($request->has('replacing')) {
            $oldEquipment = EntityEquipment::find($request->input('replacing'));
            if ($oldEquipment && $oldEquipment->entity_id === $entity->id) { // Doble chequeo de pertenencia
                $this->authorize('delete', $oldEquipment);
                
                $oldEquipment->replaced_by_equipment_id = $newEquipment->id;
                $oldEquipment->save();
                $oldEquipment->delete(); // Soft delete
            }
        }

        // 3. Redirigir a la página de detalles de la entidad
        return redirect()->route('entities.show', $entity)
                         ->with('success', '¡Equipo añadido con éxito!');
    }

    /**
     * (PRE-DESTROY) Muestra la página intermedia para decidir cómo eliminar un equipo.
     */
    public function preDestroy(EntityEquipment $equipment)
    {
        $this->authorize('delete', $equipment);
        return view('equipment.pre-destroy', compact('equipment'));
    }

    /**
     * (DESTROY) Elimina (soft delete) un equipo del inventario.
     */
    public function destroy(EntityEquipment $equipment)
    {
        $this->authorize('delete', $equipment);
        
        $entity = $equipment->entity;
        $equipment->delete();

        return redirect()->route('entities.show', $entity)
                         ->with('success', 'El equipo ha sido eliminado correctamente.');
    }

    // ... (El resto de los métodos: update, show, destroy, que ya están bien)
}
