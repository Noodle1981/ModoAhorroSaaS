<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
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
     */
    public function rules(): array
    {
        // Mismas reglas que en Store, ya que todos los campos de un contrato pueden ser editables.
        return [
            'utility_company_id' => ['required', 'exists:utility_companies,id'],
            'contract_identifier' => ['nullable', 'string', 'max:100'],
            'rate_name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['required', 'boolean'],
            'contracted_power_kw_p1' => ['nullable', 'numeric', 'min:0'],
            'contracted_power_kw_p2' => ['nullable', 'numeric', 'min:0'],
            'contracted_power_kw_p3' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}