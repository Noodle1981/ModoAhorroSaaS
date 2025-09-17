<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RatesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rates')->insert([
            [
                'utility_company_id' => 1, // Asume que Edenor es ID 1
                'name' => 'Tarifa 1 - Residencial (T1-R2)',
                'type' => 'electricity',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'utility_company_id' => 3, // Asume que Metrogas es ID 3
                'name' => 'Tarifa Residencial Gas (R2-1)',
                'type' => 'gas',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}