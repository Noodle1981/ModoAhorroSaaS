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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:hogar,oficina,comercio'], // Asegura que solo se usen los tipos permitidos
            'locality_id' => ['required', 'exists:localities,id'], // Asegura que la localidad exista en nuestra BD
            'address_street' => ['nullable', 'string', 'max:255'],
            // Aquí puedes añadir validación para los campos del JSON 'details'
            // Ej: 'details.bedrooms_count' => ['required_if:type,hogar', 'integer', 'min:0']
        ];
    }
}