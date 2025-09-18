<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentCategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Borramos los datos viejos para evitar duplicados si se corre varias veces
        DB::table('equipment_categories')->delete();
        
        DB::table('equipment_categories')->insert([
            // IDs del 1 al 12, en orden
            ['id' => 1, 'name' => 'Climatización', 'calculation_method' => 'factor_carga'],
            ['id' => 2, 'name' => 'Refrigeración', 'calculation_method' => 'eficiencia_energetica'],
            ['id' => 3, 'name' => 'Lavado', 'calculation_method' => 'uso_por_horas'],
            ['id' => 4, 'name' => 'Cocina', 'calculation_method' => 'uso_por_horas'],
            ['id' => 5, 'name' => 'Entretenimiento', 'calculation_method' => 'uso_por_horas'],
            ['id' => 6, 'name' => 'Iluminación', 'calculation_method' => 'potencia_total'],
            ['id' => 7, 'name' => 'Agua Caliente Sanitaria (ACS)', 'calculation_method' => 'demanda_termica'],
            ['id' => 8, 'name' => 'Otros', 'calculation_method' => 'uso_por_horas'],
            ['id' => 9, 'name' => 'Herramientas', 'calculation_method' => 'factor_carga'],
            ['id' => 10, 'name' => 'Cuidado Personal', 'calculation_method' => 'uso_por_horas'],
            ['id' => 11, 'name' => 'Limpieza de Cocina', 'calculation_method' => 'uso_por_horas'],
            ['id' => 12, 'name' => 'Seguridad y Hogar Inteligente', 'calculation_method' => 'consumo_agregado'],
        ]);
    }
}