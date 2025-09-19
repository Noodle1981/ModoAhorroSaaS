<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UtilityCompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('utility_companies')->insert([
            ['name' => 'Edenor', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Edesur', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'EPEC (Córdoba)', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'EPESF (Santa Fe)', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Energía San Juan', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Metrogas', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gasnor', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Camuzzi Gas', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Aguas y Saneamientos Argentinos (AySA)', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}