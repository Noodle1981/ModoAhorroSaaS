<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaintenanceTasksTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('maintenance_tasks')->insert([
            'equipment_type_id' => 1, // Para el Aire Acondicionado
            'name' => 'Limpieza de Filtros',
            'description' => 'Limpiar los filtros de la unidad interior para mejorar la eficiencia y la calidad del aire.',
            'recommended_frequency_days' => 30,
            'recommended_season' => 'summer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}