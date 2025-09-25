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
    
    <?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $entity->entityEquipments()->create($request->validated());

        // Redirigimos a la página de detalles de la entidad con un mensaje de éxito.
        return redirect()->route('entities.show', $entity)
                         ->with('success', '¡Equipo añadido con éxito!');
    }

    // ... (El resto de los métodos: update, show, destroy, que ya están bien)
}

    // Obtenemos los equipos existentes para mostrarlos en la tabla.
    $equipments = $entity->entityEquipments()->with('equipmentType.equipmentCategory')->latest()->get();

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
        $entity->entityEquipments()->create($request->validated());

        // Redirigimos a la página de detalles de la entidad con un mensaje de éxito.
        return redirect()->route('entities.show', $entity)
                         ->with('success', '¡Equipo añadido con éxito!');
    }

    // ... (El resto de los métodos: update, show, destroy, que ya están bien)
}