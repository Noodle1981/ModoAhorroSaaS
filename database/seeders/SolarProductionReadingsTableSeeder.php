<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SolarProductionReadingsTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now()->startOfHour();
        DB::table('solar_production_readings')->insert([
            [
                'solar_installation_id' => 1,
                'reading_timestamp' => $now->copy()->subHours(5), // 5 horas antes (mediodía solar)
                'produced_kwh' => 2.5
            ],
            [
                'solar_installation_id' => 1,
                'reading_timestamp' => $now->copy()->subHours(4),
                'produced_kwh' => 2.1
            ],
            [
                'solar_installation_id' => 1,
                'reading_timestamp' => $now->copy()->subHours(3),
                'produced_kwh' => 1.5
            ],
        ]);
    }
}