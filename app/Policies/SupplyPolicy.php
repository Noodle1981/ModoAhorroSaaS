<?php

namespace App\Policies;

use App\Models\Supply;
use App\Models\User;
use App\Models\Entity; // Necesitamos Entity para la creación

class SupplyPolicy
{
    /**
     * Determina si el usuario puede ver cualquier suministro para una entidad.
     */
    public function viewAny(User $user, Entity $entity): bool
    {
        return $user->company_id === $entity->company_id;
    }

    /**
     * Determina si el usuario puede ver el suministro.
     */
    public function view(User $user, Supply $supply): bool
    {
        // El usuario puede ver el suministro si pertenece a una de sus entidades.
        return $user->company_id === $supply->entity->company_id;
    }

    /**
     * Determina si el usuario puede crear suministros.
     */
    public function create(User $user, Entity $entity): bool
    {
        // El usuario puede crear un suministro PARA una entidad si esa entidad le pertenece.
        return $user->company_id === $entity->company_id;
    }

    /**
     * Determina si el usuario puede actualizar el suministro.
     */
    public function update(User $user, Supply $supply): bool
    {
        return $user->company_id === $supply->entity->company_id;
    }

    /**
     * Determina si el usuario puede borrar el suministro.
     */
    public function delete(User $user, Supply $supply): bool
    {
        return $user->company_id === $supply->entity->company_id;
    }

    // ... (puedes dejar los métodos restore y forceDelete que genera Laravel)
}