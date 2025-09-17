<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Asumiendo que Climatización es category_id=1, Refrigeración=2, Lavado=3
        DB::table('equipment_types')->insert([
            // Climatización
            ['category_id' => 1, 'name' => 'Aire Acondicionado Split 3000 Frigorías', 'default_power_watts' => 1100, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 1, 'name' => 'Caloventor 2000W', 'default_power_watts' => 2000, 'created_at' => now(), 'updated_at' => now()],
            // Refrigeración
            ['category_id' => 2, 'name' => 'Heladera con Freezer Cíclica', 'default_power_watts' => 150, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 2, 'name' => 'Freezer Vertical', 'default_power_watts' => 250, 'created_at' => now(), 'updated_at' => now()],
            // Lavado
            ['category_id' => 3, 'name' => 'Lavarropas Automático 8kg', 'default_power_watts' => 2200, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}