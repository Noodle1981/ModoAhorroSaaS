<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyWeatherLogsTableSeeder extends Seeder
{
    public function run(): void
    {
        // Datos climáticos de ejemplo para la localidad con ID 1 (Palermo, CABA)
        DB::table('daily_weather_logs')->insert([
            [
                'locality_id' => 1,
                'date' => Carbon::yesterday(),
                'avg_temp_celsius' => 18.5,
                'min_temp_celsius' => 14.2,
                'max_temp_celsius' => 22.8,
            ],
            [
                'locality_id' => 1,
                'date' => Carbon::now()->subDays(2),
                'avg_temp_celsius' => 16.1,
                'min_temp_celsius' => 12.0,
                'max_temp_celsius' => 20.2,
            ]
        ]);
    }
}