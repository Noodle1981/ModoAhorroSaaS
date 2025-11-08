<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntityEquipmentRequest extends FormRequest
{
    /**
     * Prepara los datos ANTES de la validación.
     */
    protected function prepareForValidation()
    {
        if ($this->has('avg_daily_use_hours_override') && !empty($this->avg_daily_use_hours_override)) {
            $this->merge([
                'avg_daily_use_minutes_override' => $this->avg_daily_use_hours_override * 60,
            ]);
        }

        $this->merge([
            'has_standby_mode' => $this->boolean('has_standby_mode'),
        ]);
    }
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
        return [
            'equipment_type_id' => ['required', 'exists:equipment_types,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'custom_name' => ['nullable', 'string', 'max:100'],
            'power_watts_override' => ['nullable', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:100'],
            'has_standby_mode' => ['nullable', 'boolean'],
        ];
    }
}