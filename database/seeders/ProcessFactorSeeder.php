<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcessFactor;

class ProcessFactorSeeder extends Seeder
{
    public function run(): void
    {
        $factors = [
            ['tipo_de_proceso' => 'Motor', 'factor_carga' => 0.7, 'eficiencia' => 0.9],
            ['tipo_de_proceso' => 'Resistencia', 'factor_carga' => 1, 'eficiencia' => 0.6],
            ['tipo_de_proceso' => 'Electrónico', 'factor_carga' => 0.7, 'eficiencia' => 0.8],
            ['tipo_de_proceso' => 'Motor & Resistencia', 'factor_carga' => 0.8, 'eficiencia' => 0.82],
            ['tipo_de_proceso' => 'Magnetrón', 'factor_carga' => 0.7, 'eficiencia' => 0.6],
            ['tipo_de_proceso' => 'Electroluminiscencia', 'factor_carga' => 1, 'eficiencia' => 0.9],
        ];

        foreach ($factors as $factor) {
            ProcessFactor::create($factor);
        }
    }
}
