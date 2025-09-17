<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RatePricesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rate_prices')->insert([
            'rate_id' => 1, // Asume Tarifa T1-R2 de Edenor
            'price_energy_p1' => 35.50, // Precio inventado, poner el real
            'price_power_p1' => 800.00, // Precio inventado
            'valid_from' => '2024-05-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}