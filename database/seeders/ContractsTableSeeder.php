<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('contracts')->insert([
            'supply_id' => 1, // Asume que existe el suministro con ID 1
            'utility_company_id' => 1, // Asume Edenor
            'contract_identifier' => 'CONTRATO-001',
            'rate_name' => 'Tarifa 1 - Residencial (T1-R2)',
            'contracted_power_kw_p1' => 5.5,
            'start_date' => '2023-01-01',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}