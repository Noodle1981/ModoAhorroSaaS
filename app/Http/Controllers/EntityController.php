<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntityRequest; // Usamos nuestro Form Request
use App\Http\Requests\UpdateEntityRequest; // Usamos nuestro Form Request
use App\Models\Entity;
use App\Models\Locality;
use Illuminate\Support\Facades\Auth;

class EntityController extends Controller
{
    /**
     * Aplica el middleware de autorización a todos los métodos.
     * Esto llama automáticamente a nuestra EntityPolicy.
     */
    public function __construct()
    {
        $this->authorizeResource(Entity::class, 'entity');
    }

    /**
     * Muestra una lista de las entidades del usuario.
     */
    public function index()
    {
        // Obtenemos las entidades que pertenecen a la compañía del usuario logueado.
        $entities = Auth::user()->company->entities;

        return view('entities.index', compact('entities'));
    }

    /**
     * Muestra el formulario para crear una nueva entidad.
     */
    public function create()
    {
        // Pasamos las localidades a la vista para poder mostrarlas en un dropdown.
        $localities = Locality::orderBy('name')->get();

        return view('entities.create', compact('localities'));
    }

    /**
     * Guarda una nueva entidad en la base de datos.
     */
     public function store(StoreEntityRequest $request)
    {
        // 1. La validación ya se ejecutó gracias a StoreEntityRequest.
        // Obtenemos los datos limpios y validados.
        $data = $request->validated();
        
        // 2. Extraemos el array 'details' para procesarlo por separado.
        $detailsData = $data['details'] ?? [];
        
        // 3. (La misma lógica que en update)
        // Si se enviaron habitaciones, nos aseguramos de que se guarden
        // como un array JSON `[]` reindexando las claves.
        if (isset($detailsData['rooms'])) {
            $detailsData['rooms'] = array_values($detailsData['rooms']);
        }

        // 4. Preparamos el array final para la creación del modelo.
        // Unimos los datos principales con el array 'details' ya procesado.
        $entityData = [
            'name' => $data['name'],
            'type' => $data['type'],
            'locality_id' => $data['locality_id'],
            'address_street' => $data['address_street'] ?? null,
            'details' => $detailsData, // El modelo se encargará de convertirlo a JSON
        ];

        // 5. Creamos la entidad y la asociamos a la compañía del usuario logueado.
        Auth::user()->company->entities()->create($entityData);

        // 6. Redirigimos con un mensaje de éxito.
        return redirect()->route('entities.index')
                         ->with('success', 'Entidad creada exitosamente.');
    }

    /**
     * Muestra los detalles de una entidad específica.
     */
    public function show(Entity $entity)
    {
        // La autorización ya se ejecutó gracias a __construct().
        // El análisis de inventario puede ir aquí como lo tenías.
        // $inventoryReport = $analysisService->calculateForEntity($entity);

        return view('entities.show', compact('entity'));
    }

    /**
     * Muestra el formulario para editar una entidad existente.
     */
    public function edit(Entity $entity)
    {
        // La autorización ya se ejecutó.
        $localities = Locality::orderBy('name')->get();
        return view('entities.edit', compact('entity', 'localities'));
    }

    /**
     * Actualiza una entidad en la base de datos.
     */

public function update(UpdateEntityRequest $request, Entity $entity)
{
    // 1. Obtenemos los datos ya validados.
    $data = $request->validated();
    
    // 2. Extraemos el array 'details'.
    $detailsData = $data['details'] ?? [];
    
    // --- ¡ESTA ES LA LÍNEA MÁGICA DE LA SOLUCIÓN! ---
    // 3. Si existe la clave 'rooms', usamos array_values() para reindexar
    //    el array. Esto descarta las claves de timestamp (1758...) y las
    //    reemplaza con índices numéricos secuenciales (0, 1, 2...).
    //    Ahora, json_encode() lo convertirá en un array JSON `[]`.
    if (isset($detailsData['rooms'])) {
        $detailsData['rooms'] = array_values($detailsData['rooms']);
    }
    
    // 4. Asignamos los datos principales y el array de 'details' ya procesado.
    $entity->name = $data['name'];
    $entity->type = $data['type'];
    $entity->locality_id = $data['locality_id'];
    $entity->address_street = $data['address_street'];
    $entity->details = $detailsData;

    // 5. Guardamos todo. El modelo se encargará de la conversión a JSON.
    $entity->save();

    return redirect()->route('entities.index')
                     ->with('success', 'Entidad actualizada exitosamente.');
}

    /**
     * Elimina una entidad de la base de datos.
     */
    public function destroy(Entity $entity)
    {
        // La autorización ya se ejecutó.
        $entity->delete();

        return redirect()->route('entities.index')
                         ->with('success', 'Entidad eliminada exitosamente.');
    }
}