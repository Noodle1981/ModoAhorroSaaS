<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determina si el usuario puede ver la factura.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // El usuario puede ver la factura si es dueño del contrato al que pertenece.
        // La cadena de relaciones es: Invoice -> Contract -> Supply -> Entity -> Company
        return $user->company_id === $invoice->contract->supply->entity->company_id;
    }

    /**
     * Determina si el usuario puede crear facturas para un contrato.
     */
    public function create(User $user, Contract $contract): bool
    {
        // El usuario puede crear una factura para un contrato si es dueño de ese contrato.
        return $user->company_id === $contract->supply->entity->company_id;
    }

    /**
     * Determina si el usuario puede actualizar la factura.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // La misma lógica que para ver.
        return $user->company_id === $invoice->contract->supply->entity->company_id;
    }

    /**
     * Determina si el usuario puede borrar la factura.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // La misma lógica que para ver.
        return $user->company_id === $invoice->contract->supply->entity->company_id;
    }
}