<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentCategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Actualizamos solo supports_standby sin borrar categorías existentes
        // para evitar romper relaciones con equipment_types
        
        DB::table('equipment_categories')->updateOrInsert(
            ['id' => 1],
            ['name' => 'Climatización', 'calculation_method' => 'factor_carga', 'supports_standby' => true]
        );
        DB::table('equipment_categories')->updateOrInsert(
            ['id' => 2],
            ['name' => 'Refrigeración', 'calculation_method' => 'eficiencia_energetica', 'supports_standby' => false]
        );
        DB::table('equipment_categories')->updateOrInsert(
            ['id' => 3],
            ['name' => 'Lavado', 'calculation_method' => 'uso_por_horas', 'supports_standby' => false]
        );
        DB::table('equipment_categories')->updateOrInsert(
            ['id' => 4],
            ['name' => 'Cocina', 'calculation_method' => 'uso_por_horas', 'supports_standby' => true]
        );
        DB::table('equipment_categories')->updateOrInsert(
            ['id' => 5],
            ['name' => 'Entretenimiento', 'calculation_method' => 'uso_por_horas', 'supports_standby' => true]
        );
        DB::table('equipment_categories')->updateOrInsert(
            ['id' => 6],
            ['name' => 'Iluminación', 'calculation_method' => 'potencia_total', 'supports_standby' => false]
        );
        DB::table('equipment_categories')->updateOrInsert(
            ['id' => 7],
            ['name' => 'Agua Caliente Sanitaria (ACS)', 'calculation_method' => 'demanda_termica', 'supports_standby' => false]
        );
        DB::table('equipment_categories')->updateOrInsert(
            ['id' => 8],
            ['name' => 'Otros', 'calculation_method' => 'uso_por_horas', 'supports_standby' => false]
        );
        DB::table('equipment_categories')->updateOrInsert(
            ['id' => 9],
            ['name' => 'Herramientas', 'calculation_method' => 'factor_carga', 'supports_standby' => false]
        );
        DB::table('equipment_categories')->updateOrInsert(
            ['id' => 10],
            ['name' => 'Cuidado Personal', 'calculation_method' => 'uso_por_horas', 'supports_standby' => false]
        );
        DB::table('equipment_categories')->updateOrInsert(
            ['id' => 11],
            ['name' => 'Limpieza de Cocina', 'calculation_method' => 'uso_por_horas', 'supports_standby' => false]
        );
        DB::table('equipment_categories')->updateOrInsert(
            ['id' => 12],
            ['name' => 'Seguridad y Hogar Inteligente', 'calculation_method' => 'consumo_agregado', 'supports_standby' => true]
        );
    }
}