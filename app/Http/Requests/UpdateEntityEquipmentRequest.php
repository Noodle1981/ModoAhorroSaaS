<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntityEquipmentRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Mismas reglas que para la creación.
        return [
            'equipment_type_id' => ['required', 'exists:equipment_types,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'custom_name' => ['nullable', 'string', 'max:100'],
            'power_watts_override' => ['nullable', 'integer', 'min:0'],
            'avg_daily_use_hours_override' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'location' => ['nullable', 'string', 'max:100'],

        ];
    }
}