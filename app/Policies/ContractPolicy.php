<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\Supply;
use App\Models\User;

class ContractPolicy
{
    /**
     * Determina si el usuario puede ver cualquier contrato para un suministro.
     */
    public function viewAny(User $user, Supply $supply): bool
    {
        return $user->company_id === $supply->entity->company_id;
    }

    /**
     * Determina si el usuario puede ver el contrato.
     */
    public function view(User $user, Contract $contract): bool
    {
        // Un usuario puede ver un contrato si es dueño del suministro al que pertenece.
        return $user->company_id === $contract->supply->entity->company_id;
    }

    /**
     * Determina si el usuario puede crear contratos para un suministro.
     */
    public function create(User $user, Supply $supply): bool
    {
        // Un usuario puede crear un contrato para un suministro si es dueño de ese suministro.
        return $user->company_id === $supply->entity->company_id;
    }

    /**
     * Determina si el usuario puede actualizar el contrato.
     */
    public function update(User $user, Contract $contract): bool
    {
        // La regla clave: ¿Pertenece la compañía del usuario al "abuelo" del contrato?
        return $user->company_id === $contract->supply->entity->company_id;
    }

    /**
     * Determina si el usuario puede borrar el contrato.
     */
    public function delete(User $user, Contract $contract): bool
    {
        return $user->company_id === $contract->supply->entity->company_id;
    }
}