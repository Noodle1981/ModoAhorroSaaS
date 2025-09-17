<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // ¡Importante para las reglas complejas!

class UpdateEntityRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     *
     * La autorización real la manejará la EntityPolicy en el controlador,
     * pero aquí nos aseguramos de que el usuario al menos esté logueado.
     */
    public function authorize(): bool
    {
        return true; // O return auth()->check(); para ser más explícito.
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud de actualización.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // La lógica aquí es idéntica a StoreEntityRequest porque no tenemos
        // campos únicos que necesiten una regla especial. Sin embargo, te muestro
        // cómo lo harías si 'name' tuviera que ser único.

        // Obtenemos la entidad que estamos intentando actualizar desde la ruta.
        // Por ejemplo, si la URL es /entities/5/edit, esto nos dará la entidad con ID 5.
        $entityId = $this->route('entity')->id; 

        return [
            'name' => [
                'required', 
                'string', 
                'max:255',
                // Ejemplo de cómo manejarías una regla 'unique' en una actualización:
                // Rule::unique('entities')->ignore($entityId), 
                // Esto le dice a Laravel: "El nombre debe ser único en la tabla 'entities',
                // pero ignora la fila que tenga el ID que estamos editando".
            ],
            'type' => ['required', 'in:hogar,oficina,comercio'],
            'locality_id' => ['required', 'exists:localities,id'],
            'address_street' => ['nullable', 'string', 'max:255'],
            // Puedes mantener las mismas reglas para los detalles
            // 'details.bedrooms_count' => ['required_if:type,hogar', 'integer', 'min:0']
        ];
    }

    /**
     * (Opcional) Puedes personalizar los mensajes de error aquí si quieres.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la entidad es obligatorio.',
            'locality_id.required' => 'Debes seleccionar una localidad.',
            'type.in' => 'El tipo de entidad seleccionado no es válido.',
        ];
    }
}