<?php

namespace App\Http\Requests;

use App\Models\Supply;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupplyRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        // La autorización real (si el usuario es dueño de la entidad)
        // ya la manejamos en el controlador con la Policy. Aquí damos paso.
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            // El 'type' no lo necesitamos validar aquí porque viene de un <select>
            // y no del 'entity_id' que estaba antes.
            'type' => ['required', 'in:electricity,gas,water'],
            
            // El identificador debe ser único en toda la tabla de suministros.
            'supply_point_identifier' => ['required', 'string', 'max:100', 'unique:'.Supply::class],
        ];
    }
}