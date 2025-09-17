<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Models\Contract;
use App\Models\Supply;
use App\Models\UtilityCompany;

class ContractController extends Controller
{
    /**
     * (CREATE) Muestra el formulario para crear un nuevo contrato para un suministro.
     */
    public function create(Supply $supply)
    {
        $this->authorize('create', [Contract::class, $supply]);
        
        $utilityCompanies = UtilityCompany::orderBy('name')->get();
        return view('contracts.create', compact('supply', 'utilityCompanies'));
    }

    /**
     * (STORE) Guarda el nuevo contrato en la base de datos.
     */
    public function store(StoreContractRequest $request, Supply $supply)
    {
        $this->authorize('create', [Contract::class, $supply]);

        $data = $request->validated();
        
        if ($data['is_active']) {
            $supply->contracts()->update(['is_active' => false]);
        }
        
        $supply->contracts()->create($data);

        return redirect()->route('supplies.show', $supply)
                         ->with('success', 'Contrato añadido exitosamente.');
    }

    /**
     * (SHOW) Muestra los detalles de un contrato específico.
     */
    public function show(Contract $contract)
    {
        $this->authorize('view', $contract);
        $contract->load('supply.entity', 'utilityCompany', 'invoices');
        return view('contracts.show', compact('contract'));
    }

    /**
     * (EDIT) Muestra el formulario para editar un contrato.
     */
    public function edit(Contract $contract)
    {
        $this->authorize('update', $contract);
        $utilityCompanies = UtilityCompany::orderBy('name')->get();
        return view('contracts.edit', compact('contract', 'utilityCompanies'));
    }

    /**
     * (UPDATE) Actualiza el contrato en la base de datos.
     */
    public function update(UpdateContractRequest $request, Contract $contract)
    {
        $this->authorize('update', $contract);
        
        $data = $request->validated();

        if ($data['is_active']) {
            $contract->supply->contracts()->where('id', '!=', $contract->id)->update(['is_active' => false]);
        }

        $contract->update($data);

        return redirect()->route('contracts.show', $contract)
                         ->with('success', 'Contrato actualizado exitosamente.');
    }

    /**
     * (DESTROY) Elimina el contrato de la base de datos.
     */
    public function destroy(Contract $contract)
    {
        $this->authorize('delete', $contract);
        
        $supply = $contract->supply;
        $contract->delete();

        return redirect()->route('supplies.show', $supply)
                         ->with('success', 'Contrato eliminado exitosamente.');
    }
}