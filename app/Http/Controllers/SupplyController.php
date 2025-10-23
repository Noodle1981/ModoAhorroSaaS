<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplyRequest;
use App\Http\Requests\UpdateSupplyRequest;
use App\Models\Entity;
use App\Models\Supply;

class SupplyController extends Controller
{
    // Hemos quitado el __construct() para manejar la autorización
    // explícitamente en cada método, lo que es más claro.

    public function index(Entity $entity)
    {
        $this->authorize('viewAny', [Supply::class, $entity]);
        $supplies = $entity->supplies()->with('rate')->get();
        return view('supplies.index', compact('entity', 'supplies'));
    }

    public function create(Entity $entity)
    {
        // Autorizamos ANTES de hacer nada.
        $this->authorize('create', [Supply::class, $entity]);
        return view('supplies.create', compact('entity'));
    }

    public function store(StoreSupplyRequest $request, Entity $entity)
    {
        $this->authorize('create', [Supply::class, $entity]);
        $data = $request->validated();
        $data['entity_id'] = $entity->id;
        Supply::create($data);
        return redirect()->route('entities.show', $entity)->with('success', 'Suministro añadido.');
    }

    public function show(Supply $supply)
    {
        $this->authorize('view', $supply);
        $supply->load('entity', 'contracts.utilityCompany', 'contracts.invoices');
        return view('supplies.show', compact('supply'));
    }

    public function edit(Supply $supply)
    {
        // ----> ¡ESTA ES LA LÍNEA MÁS IMPORTANTE PARA TU ERROR ACTUAL! <----
        $this->authorize('update', $supply); // Llama a la policy ANTES de mostrar la vista.
        return view('supplies.edit', compact('supply'));
    }

    public function update(UpdateSupplyRequest $request, Supply $supply)
    {
        $this->authorize('update', $supply);
        $supply->update($request->validated());
        return redirect()->route('supplies.show', $supply)->with('success', 'Suministro actualizado.');
    }

    public function destroy(Supply $supply)
    {
        $this->authorize('delete', $supply);
        $entity = $supply->entity;
        $supply->delete();
        return redirect()->route('entities.show', $entity)->with('success', 'Suministro eliminado.');
    }
}