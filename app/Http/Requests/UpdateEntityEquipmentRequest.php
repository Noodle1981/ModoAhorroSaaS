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
            'is_daily_use' => $this->boolean('is_daily_use'),
        ]);

        $isDaily = $this->boolean('is_daily_use');
        if ($isDaily) {
            $this->merge([
                'usage_days_per_week' => 7,
                'usage_weekdays' => [1,2,3,4,5,6,7],
            ]);
        } else {
            if (is_array($this->usage_weekdays) && !empty($this->usage_weekdays)) {
                $this->merge([
                    'usage_days_per_week' => count($this->usage_weekdays)
                ]);
            }
            if ($this->has('minutes_per_session') && $this->minutes_per_session !== null && $this->usage_days_per_week) {
                $derived = (int)$this->minutes_per_session * (int)$this->usage_days_per_week / 7;
                $this->merge(['avg_daily_use_minutes_override' => (int)round($derived)]);
            }
        }
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
            'avg_daily_use_minutes_override' => ['nullable', 'integer', 'min:0', 'max:1440'],
            // Frecuencia
            'is_daily_use' => ['nullable', 'boolean'],
            'usage_days_per_week' => ['nullable', 'integer', 'min:0', 'max:7'],
            'usage_weekdays' => ['nullable', 'array'],
            'usage_weekdays.*' => ['integer', 'min:1', 'max:7'],
            'minutes_per_session' => ['nullable', 'integer', 'min:0', 'max:1440'],
        ];
    }
}