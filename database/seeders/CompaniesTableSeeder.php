<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company; // Usamos el modelo Eloquent

class CompaniesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Usamos updateOrCreate para evitar errores de duplicados si el seeder se ejecuta varias veces.
        // Busca una compañía con el tax_id especificado y la actualiza, o la crea si no existe.
        Company::updateOrCreate(
            [
                'tax_id' => '30-12345678-9' // Atributo único para buscar
            ],
            [
                'name' => 'Mi Primera Empresa Cliente', // Datos para crear o actualizar
                'address' => 'Av. Corrientes 1234',
                'phone' => '11-5555-4444',
            ]
        );
    }
}
