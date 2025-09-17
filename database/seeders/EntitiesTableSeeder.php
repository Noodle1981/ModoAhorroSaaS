<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntitiesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('entities')->insert([
            [
                'company_id' => 1, // Asume que existe la compañía con ID 1
                'locality_id' => 1, // Asume que existe una localidad con ID 1
                'name' => 'Casa Principal',
                'type' => 'hogar',
                'address_street' => 'Calle Falsa 123',
                'address_postal_code' => 'C1425',
                'details' => json_encode([
                    'property_type' => 'casa',
                    'bedrooms_count' => 3,
                    'occupants_count' => 4
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}