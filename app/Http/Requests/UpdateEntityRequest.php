<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntityRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        // La autorización de pertenencia la maneja la Policy.
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            // ===============================================
            // REGLAS PARA LOS CAMPOS PRINCIPALES (Fuera del JSON)
            // ===============================================
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:hogar,oficina,comercio'],
            'locality_id' => ['required', 'exists:localities,id'],
            'address_street' => ['nullable', 'string', 'max:255'],
            
            // =================================================================
            // REGLAS PARA LOS CAMPOS ANIDADOS EN 'details' (NUEVA ESTRUCTURA)
            // =================================================================
            'details' => ['nullable', 'array'],

            // Campos generales dentro de 'details' que aún podríamos querer
            'details.property_type' => ['nullable', 'string', 'max:50'],
            'details.occupants_count' => ['nullable', 'integer', 'min:1'],
            
            // Reglas para nuestro nuevo sistema de habitaciones dinámicas
            'details.rooms' => ['nullable', 'array'], // Debe ser un array de habitaciones
            
            // Esta es la regla clave: para CADA (*) item dentro del array 'details.rooms',
            // la clave 'name' debe existir, ser un texto y tener máximo 100 caracteres.
            'details.rooms.*.name' => ['required', 'string', 'max:100'],

            // Reglas para otros campos específicos para ciertos de elementos en la identidad

            'details.electronic_devices_count' => ['nullable', 'integer', 'min:0'],
        ];
    }
}