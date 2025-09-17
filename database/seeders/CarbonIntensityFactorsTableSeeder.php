<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarbonIntensityFactorsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('carbon_intensity_factors')->insert([
            [
                'region' => 'Argentina',
                'energy_type' => 'electricity',
                'factor' => 0.380, // Valor de ejemplo, buscar el último publicado por CAMMESA/Secretaría de Energía
                'unit' => 'kgCO2e/kWh',
                'valid_from' => '2024-01-01',
                'valid_to' => '2024-12-31',
                'source' => 'Secretaría de Energía (Inventado)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'region' => 'Argentina',
                'energy_type' => 'natural_gas',
                'factor' => 2.055, // Valor de ejemplo, buscar el oficial
                'unit' => 'kgCO2e/m3',
                'valid_from' => '2024-01-01',
                'valid_to' => '2024-12-31',
                'source' => 'Secretaría de Energía (Inventado)',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}