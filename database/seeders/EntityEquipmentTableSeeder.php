<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntityEquipmentTableSeeder extends Seeder
{
    public function run(): void
{
    DB::table('entity_equipment')->insert([
        'entity_id' => 1,
        'equipment_type_id' => 1, // Asume Aire Acondicionado Split
        'quantity' => 1,
        'custom_name' => 'Aire del Living',
        'location' => 'Living', // Ahora es un simple string
        'power_watts_override' => 1100,
        'avg_daily_use_minutes_override' => 240, // 4 horas
        'has_standby_mode' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
}