<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntityRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        // Cualquiera que esté logueado puede intentar crear una entidad.
        // La lógica de si puede o no según su plan irá en el controlador.
        return true; 
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
             // Campos que SÍ se guardan en la tabla 'entities'
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:hogar,oficina,comercio'],
            'locality_id' => ['required', 'exists:localities,id'],
            'address_street' => ['nullable', 'string', 'max:255'],
            
            // Campo que NO se guarda directamente pero se usa para la cascada.
            // Lo validamos para asegurarnos de que el usuario seleccionó algo.
            'province_id' => ['required', 'exists:provinces,id'],
            
            // Campos del JSON 'details'
            'details' => ['nullable', 'array'],
            'details.occupants_count' => ['nullable', 'integer', 'min:1'],
            'details.surface_area' => ['nullable', 'numeric', 'min:0'],
            'details.rooms' => ['nullable', 'array'],
            'details.rooms.*.name' => ['sometimes', 'required', 'string', 'max:100'],
        ];
    }
}
