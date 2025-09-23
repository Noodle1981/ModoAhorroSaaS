<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsageSnapshotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Validamos que 'snapshots' sea un array
            'snapshots' => ['required', 'array'],
            // Y validamos las reglas para CADA elemento del array
            'snapshots.*.entity_equipment_id' => ['required', 'exists:entity_equipment,id'],
            'snapshots.*.avg_daily_use_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'snapshots.*.avg_daily_use_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
        ];
    }
}