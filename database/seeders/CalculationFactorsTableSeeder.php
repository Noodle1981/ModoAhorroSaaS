<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CalculationFactorsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('calculation_factors')->insert([
            // Esto es tu tablaFQE convertida a Seeder!
            ['method_name' => 'Motor', 'load_factor' => 0.7, 'efficiency_factor' => 0.9, 'created_at' => now(), 'updated_at' => now()],
            ['method_name' => 'Resistencia', 'load_factor' => 1.0, 'efficiency_factor' => 0.6, 'created_at' => now(), 'updated_at' => now()],
            ['method_name' => 'Electrónico', 'load_factor' => 0.7, 'efficiency_factor' => 0.8, 'created_at' => now(), 'updated_at' => now()],
            ['method_name' => 'Motor & Resistencia', 'load_factor' => 0.8, 'efficiency_factor' => 0.82, 'created_at' => now(), 'updated_at' => now()],
            ['method_name' => 'Magnetrón', 'load_factor' => 0.7, 'efficiency_factor' => 0.6, 'created_at' => now(), 'updated_at' => now()],
            ['method_name' => 'Electroluminiscencia', 'load_factor' => 1.0, 'efficiency_factor' => 0.9, 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Ahora, cambiamos el calculation_method de nuestras categorías para que coincida exactamente.
        // Esto se podría hacer en su propio seeder, pero lo pongo aquí por claridad.
        DB::table('equipment_categories')->where('id', 1)->update(['calculation_method' => 'Motor']); // Climatización
        DB::table('equipment_categories')->where('id', 2)->update(['calculation_method' => 'Motor']); // Refrigeración
        // ... etc
    }
}