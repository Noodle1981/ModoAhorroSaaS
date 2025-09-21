<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Contract;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    /**
     * (CREATE) Muestra el formulario para crear una nueva factura para un contrato.
     */
    public function create(Contract $contract)
    {
        // Autorizamos: ¿puede este usuario crear una factura PARA ESTE contrato?
        $this->authorize('create', [Invoice::class, $contract]);
        

        return view('invoices.create', compact('contract'));
    }

    /**
     * (STORE) Guarda la nueva factura en la base de datos.
     */
    public function store(StoreInvoiceRequest $request, Contract $contract)
    {
        $this->authorize('create', [Invoice::class, $contract]);
        
        // La validación ya se ejecutó gracias a StoreInvoiceRequest
        $data = $request->validated();
        
        // Aquí podrías añadir lógica de cálculo de huella de carbono antes de guardar
        // $data['co2_footprint_kg'] = $carbonCalculatorService->calculate(...);

         // Creamos la factura como antes
    $invoice = $contract->invoices()->create($data);

    // --- ¡EL CAMBIO IMPORTANTE! ---
    // En lugar de redirigir al detalle del contrato, redirigimos
    // al nuevo formulario para crear el snapshot de uso, pasándole
    // la factura que acabamos de crear.
    return redirect()->route('snapshots.create', $invoice)
                     ->with('success', 'Factura cargada. Ahora, por favor, confirma el uso de tus equipos para este período.');
    }

    /**
     * (SHOW) Muestra los detalles de una factura específica.
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load('contract.supply.entity');
        return view('invoices.show', compact('invoice'));
    }

    /**
     * (EDIT) Muestra el formulario para editar una factura.
     */
    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        return view('invoices.edit', compact('invoice'));
    }

    /**
     * (UPDATE) Actualiza la factura en la base de datos.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->update($request->validated());

        return redirect()->route('invoices.show', $invoice)
                         ->with('success', 'Factura actualizada exitosamente.');
    }

    /**
     * (DESTROY) Elimina la factura de la base de datos.
     */
    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);
        
        $contract = $invoice->contract; // Guardamos el contrato para la redirección
        $invoice->delete();

        return redirect()->route('contracts.show', $contract)
                         ->with('success', 'Factura eliminada exitosamente.');
    }
}