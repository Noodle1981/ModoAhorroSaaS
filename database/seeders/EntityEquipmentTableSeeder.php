<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntityEquipmentTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('entity_equipment')->insert([
            'entity_id' => 1, // Asume la entidad "Casa Principal"
            'equipment_type_id' => 1, // Asume "Aire Acondicionado Split 3000 Frigorías"
            'quantity' => 1,
            'custom_name' => 'Aire del Living',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}