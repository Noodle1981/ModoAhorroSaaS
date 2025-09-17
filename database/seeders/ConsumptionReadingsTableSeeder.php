<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConsumptionReadingsTableSeeder extends Seeder
{
    public function run(): void
    {
        // Genera 4 lecturas horarias para el suministro 1
        $now = Carbon::now()->startOfHour();
        DB::table('consumption_readings')->insert([
            [
                'supply_id' => 1,
                'reading_timestamp' => $now->copy()->subHours(3),
                'consumed_kwh' => 0.55,
                'injected_kwh' => 0,
                'source' => 'smart_meter'
            ],
            [
                'supply_id' => 1,
                'reading_timestamp' => $now->copy()->subHours(2),
                'consumed_kwh' => 0.48,
                'injected_kwh' => 0,
                'source' => 'smart_meter'
            ],
            [
                'supply_id' => 1,
                'reading_timestamp' => $now->copy()->subHours(1),
                'consumed_kwh' => 0.62,
                'injected_kwh' => 0,
                'source' => 'smart_meter'
            ],
        ]);
    }
}