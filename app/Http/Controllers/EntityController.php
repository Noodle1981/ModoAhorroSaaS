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
        // La validación ya se ejecutó gracias al StoreEntityRequest.
        // Creamos la entidad y la asociamos a la compañía del usuario.
        Auth::user()->company->entities()->create($request->validated());

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
        // La validación y autorización ya se ejecutaron.
        $entity->update($request->validated());

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