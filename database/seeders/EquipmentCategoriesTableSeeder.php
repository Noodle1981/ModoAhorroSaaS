<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentCategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('equipment_categories')->insert([
            ['name' => 'Climatización', 'calculation_method' => 'factor_carga', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Refrigeración', 'calculation_method' => 'eficiencia_energetica', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lavado', 'calculation_method' => 'uso_por_horas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cocina', 'calculation_method' => 'uso_por_horas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Entretenimiento', 'calculation_method' => 'uso_por_horas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Iluminación', 'calculation_method' => 'potencia_total', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Agua Caliente Sanitaria (ACS)', 'calculation_method' => 'demanda_termica', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}