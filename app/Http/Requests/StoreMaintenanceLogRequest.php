<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización fina se realiza en el Controller via Policy sobre el equipo
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'entity_equipment_id' => ['required', 'integer', 'exists:entity_equipment,id'],
            'maintenance_task_id' => ['required', 'integer', 'exists:maintenance_tasks,id'],
            'performed_on_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'action_type' => ['nullable', 'string', 'max:100'],
            'smart_alert_id' => ['nullable', 'integer', 'exists:smart_alerts,id'],
        ];
    }
}
