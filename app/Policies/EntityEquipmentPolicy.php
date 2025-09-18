<?php

namespace App\Policies;

use App\Models\Entity;
use App\Models\EntityEquipment;
use App\Models\User;

class EntityEquipmentPolicy
{
    /**
     * Determina si el usuario puede ver cualquier equipo para una entidad.
     */
    public function viewAny(User $user, Entity $entity): bool
    {
        return $user->company_id === $entity->company_id;
    }

    /**
     * Determina si el usuario puede ver el equipo.
     */
    public function view(User $user, EntityEquipment $entityEquipment): bool
    {
        // El usuario puede ver un equipo si pertenece a una de sus entidades.
        return $user->company_id === $entityEquipment->entity->company_id;
    }

    /**
     * Determina si el usuario puede crear equipos para una entidad.
     */
    public function create(User $user, Entity $entity): bool
    {
        // El usuario puede crear un equipo PARA una entidad si esa entidad le pertenece.
        return $user->company_id === $entity->company_id;
    }

    /**
     * Determina si el usuario puede actualizar el equipo.
     */
    public function update(User $user, EntityEquipment $entityEquipment): bool
    {
        // La misma lógica que para ver.
        return $user->company_id === $entityEquipment->entity->company_id;
    }

    /**
     * Determina si el usuario puede borrar el equipo.
     */
    public function delete(User $user, EntityEquipment $entityEquipment): bool
    {
        // La misma lógica que para ver.
        return $user->company_id === $entityEquipment->entity->company_id;
    }
}