<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppSettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('app_settings')->insert([
            [
                'key' => 'carbon_factor_kwh',
                'value' => '0.380', // Valor de ejemplo para Argentina, investigar el oficial actual
                'description' => 'Factor de emisión en kgCO2e por kWh para la red eléctrica argentina.'
            ],
            [
                'key' => 'platform_version',
                'value' => '1.0.0',
                'description' => 'Versión actual del SaaS.'
            ]
        ]);
    }
}