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
        $company = $user->company;

        // If user has no company, deny.
        if (!$company) {
            return false;
        }

        // Use the relationship to get the active subscription and its plan.
        $activeSubscription = $company->activeSubscription;

        // If there's no active subscription or it has no plan, deny.
        if (!$activeSubscription || !$activeSubscription->plan) {
            return false;
        }

        $plan = $activeSubscription->plan;
        $entityCount = $company->entities()->count();

        // 1. Check if the user has reached the maximum number of entities for their plan.
        // A null value for max_entities means unlimited.
        if (!is_null($plan->max_entities) && $entityCount >= $plan->max_entities) {
            return false; // Deny: limit reached
        }

        // 2. Check if the entity type they are trying to create is allowed by their plan.
        $requestedType = request()->input('type');

        // If a type is requested and the plan has a specific list of allowed types...
        if ($requestedType && !empty($plan->allowed_entity_types)) {
            // ...check if the requested type is in the allowed list.
            if (!in_array($requestedType, $plan->allowed_entity_types)) {
                return false; // Deny: type not allowed
            }
        }

        // If all checks pass, allow creation.
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