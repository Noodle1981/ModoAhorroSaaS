<?php

namespace App\Policies;

use App\Models\Entity;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EntityPolicy
{
    /**
     * Determina si el usuario puede ver cualquier entidad (en la página de índice).
     * Siempre es true si el usuario está logueado.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede ver una entidad específica.
     */
    public function view(User $user, Entity $entity): bool
    {
        // El usuario puede ver la entidad SI el company_id del usuario
        // es el mismo que el company_id de la entidad.
        return $user->company_id === $entity->company_id;
    }

    /**
     * Determina si el usuario puede crear una entidad.
     */
    public function create(User $user): bool
    {
        // La lógica de si puede o no según su plan la manejamos en el FormRequest.
        // Aquí, simplemente decimos que cualquier usuario logueado puede INTENTAR crearla.
        return true;
    }

    /**
     * Determina si el usuario puede actualizar la entidad.
     */
    public function update(User $user, Entity $entity): bool
    {
        // La lógica es la misma que para ver.
        return $user->company_id === $entity->company_id;
    }

    /**
     * Determina si el usuario puede borrar la entidad.
     */
    public function delete(User $user, Entity $entity): bool
    {
        // La lógica es la misma que para ver.
        return $user->company_id === $entity->company_id;
    }

    /**

     * Determina si el usuario puede restaurar una entidad borrada (soft deleted).
     */
    public function restore(User $user, Entity $entity): bool
    {
        // Opcional, pero buena práctica tenerlo.
        return $user->company_id === $entity->company_id;
    }

    /**
     * Determina si el usuario puede borrar permanentemente una entidad.
     */
    public function forceDelete(User $user, Entity $entity): bool
    {
        // Opcional, pero buena práctica tenerlo.
        return $user->company_id === $entity->company_id;
    }
}