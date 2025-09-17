<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentUsagePatternsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('equipment_usage_patterns')->insert([
            'entity_equipment_id' => 1, // Asume el "Aire del Living"
            'day_of_week' => 2, // Martes
            'start_time' => '21:00:00',
            'duration_minutes' => 180, // 3 horas
            'season' => 'summer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}