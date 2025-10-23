<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan; // <-- Importamos el modelo Plan

class PlansTableSeeder extends Seeder
{
    public function run(): void
    {
        // Definimos los planes como un array
        $plans = [
            [
                'name' => 'Gratuito',
                'price' => 0.00,
                'max_entities' => 1,
                // Pasamos un array normal de PHP, el modelo se encarga de convertirlo a JSON
                'allowed_entity_types' => ['hogar'],
            ],
            [
                'name' => 'Base',
                'price' => 1500.00,
                'max_entities' => 3,
                'allowed_entity_types' => ['hogar', 'oficina', 'comercio'],
            ],
            [
                'name' => 'Gestor',
                'price' => 5000.00,
                'max_entities' => null, // Ilimitado
                'allowed_entity_types' => ['hogar', 'oficina', 'comercio'],
            ],
        ];

        // Recorremos el array y usamos updateOrCreate para cada plan
        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['name' => $planData['name']], // Atributo único para buscar
                $planData // El resto de los datos para crear o actualizar
            );
        }
    }
}
