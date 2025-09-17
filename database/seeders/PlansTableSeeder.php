<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlansTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('plans')->insert([
            [
                'name' => 'Gratuito',
                'price' => 0.00,
                'max_entities' => 1,
                'allowed_entity_types' => json_encode(['hogar']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Base',
                'price' => 1500.00,
                'max_entities' => 3,
                'allowed_entity_types' => json_encode(['hogar', 'oficina', 'comercio']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gestor',
                'price' => 5000.00,
                'max_entities' => null, // Ilimitado
                'allowed_entity_types' => json_encode(['hogar', 'oficina', 'comercio']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}