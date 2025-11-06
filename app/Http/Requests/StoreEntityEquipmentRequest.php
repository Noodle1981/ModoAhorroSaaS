<?php

namespace App\Http\Requests;

use App\Models\EquipmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEntityEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepara los datos ANTES de la validación.
     */
    protected function prepareForValidation()
    {
        // Unificamos el tiempo de uso en minutos.
        if ($this->has('avg_daily_use_hours_override') && !empty($this->avg_daily_use_hours_override)) {
            // Si el usuario envió horas, las convertimos a minutos.
            $this->merge([
                'avg_daily_use_minutes_override' => $this->avg_daily_use_hours_override * 60,
            ]);
        }
        
        // Si el checkbox no vino en el request, aplicamos el default por categoría (si existe)
        $equipmentType = EquipmentType::with('equipmentCategory')->find($this->input('equipment_type_id'));
        $supportsStandbyByCategory = (bool) optional(optional($equipmentType)->equipmentCategory)->supports_standby;
        $incomingHasStandby = $this->has('has_standby_mode');

        $this->merge([
            // Si el usuario lo envió, usamos su valor booleano; si no, usamos el default por categoría
            'has_standby_mode' => $incomingHasStandby ? $this->boolean('has_standby_mode') : $supportsStandbyByCategory,
        ]);
    }

    public function rules(): array
    {
        $equipmentType = EquipmentType::find($this->input('equipment_type_id'));
        $isPortable = $equipmentType ? $equipmentType->is_portable : false;

        return [
            'equipment_type_id' => ['required', 'exists:equipment_types,id'],
            'location' => [Rule::requiredIf(!$isPortable), 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1'],
            'custom_name' => ['nullable', 'string', 'max:100'],
            'power_watts_override' => ['required', 'integer', 'min:0'],
            
            // Validamos ambos, pero solo uno será usado gracias a prepareForValidation
            'avg_daily_use_hours_override' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'avg_daily_use_minutes_override' => ['nullable', 'integer', 'min:0', 'max:1440'],

            'has_standby_mode' => ['nullable', 'boolean'],
        ];
    }
}