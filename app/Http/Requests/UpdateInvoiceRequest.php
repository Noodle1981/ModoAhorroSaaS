<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'invoice_date' => ['nullable', 'date'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'total_energy_consumed_kwh' => ['required', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            
            'energy_consumed_p1_kwh' => ['nullable', 'numeric', 'min:0'],
            'energy_consumed_p2_kwh' => ['nullable', 'numeric', 'min:0'],
            'energy_consumed_p3_kwh' => ['nullable', 'numeric', 'min:0'],
            'cost_for_energy' => ['nullable', 'numeric', 'min:0'],
            'cost_for_power' => ['nullable', 'numeric', 'min:0'],
            'taxes' => ['nullable', 'numeric', 'min:0'],
            'other_charges' => ['nullable', 'numeric', 'min:0'],
            'total_energy_injected_kwh' => ['nullable', 'numeric', 'min:0'],
            'surplus_compensation_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}