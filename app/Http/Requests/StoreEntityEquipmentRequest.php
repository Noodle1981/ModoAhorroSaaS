<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntityEquipmentRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     * La autorización de pertenencia la maneja la Policy, aquí solo permitimos el intento.
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
        return [
        'equipment_type_id' => ['required', 'exists:equipment_types,id'],
        'location' => ['required', 'string', 'max:100'],
        'quantity' => ['required', 'integer', 'min:1'],
        'custom_name' => ['nullable', 'string', 'max:100'],
        'power_watts_override' => ['required', 'integer', 'min:0'], // <-- CAMBIO IMPORTANTE
        'avg_daily_use_hours_override' => ['nullable', 'numeric', 'min:0', 'max:24'],
    ];
    }
}