<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SolarInstallationsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('solar_installations')->insert([
            'entity_id' => 1, // La Casa Principal ahora tiene paneles!
            'system_capacity_kwp' => 4.5,
            'installation_date' => '2023-09-15',
            'panel_brand' => 'Canadian Solar',
            'number_of_panels' => 10,
            'orientation' => 'Norte',
            'tilt_degrees' => 30,
            'has_storage' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}