<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreEntityRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     * La lógica se basa en las limitaciones del plan del usuario.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        // Asumimos que el usuario tiene una suscripción y un plan.
        // Es buena idea asegurarse de que no sean nulos en el flujo de registro.
        if (!$user->subscription || !$user->subscription->plan) {
            return false;
        }
        
        $plan = $user->subscription->plan;

        // Si max_entities es null (ilimitado), siempre está autorizado para crear más.
        if (is_null($plan->max_entities)) {
            return true;
        }

        // Comprueba si el usuario ha alcanzado o superado el número máximo de entidades.
        if ($user->company && $user->company->entities()->count() >= $plan->max_entities) {
            // Si ya alcanzó el límite, no puede crear más.
            return false;
        }

        // Si no ha alcanzado el límite, puede intentar crear una.
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     * Las reglas se adaptan dinámicamente al plan del usuario.
     */
    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user = $this->user();
        $plan = $user->subscription->plan;
        
        // El cast en el modelo Plan ya convierte este campo en un array, por lo que no necesitamos json_decode.
        $allowed_types = $plan->allowed_entity_types;

        return [
            'name' => ['required', 'string', 'max:255'],
            // La regla 'in' se genera dinámicamente con los tipos permitidos para el plan.
            'type' => ['required', 'in:' . implode(',', $allowed_types)],
            'locality_id' => ['required', 'exists:localities,id'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'array'],
            'details.rooms' => ['nullable', 'array'],
            'details.rooms.*.name' => ['required', 'string', 'max:100'],
            'details.occupants' => ['nullable', 'integer', 'min:1'],
            'details.area_m2' => ['nullable', 'numeric', 'min:1'],
            'details.mixed_use' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Mensajes de error personalizados para las validaciones.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la entidad es obligatorio.',
            'type.required' => 'Debes seleccionar un tipo de entidad.',
            'type.in' => 'El tipo seleccionado no está permitido en tu plan actual.',
            'locality_id.required' => 'Debes seleccionar una localidad.',
            'locality_id.exists' => 'La localidad seleccionada no es válida.',
        ];
    }
}
