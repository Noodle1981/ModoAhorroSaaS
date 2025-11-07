<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // San Juan has province_id = 18 based on the provided ProvincesTableSeeder
        $sanJuanProvinceId = 18;

        $localities = [
            // Localities for San Juan
            ['province_id' => $sanJuanProvinceId, 'name' => 'San Juan Capital', 'postal_code' => 'J5400', 'latitude' => -31.5375, 'longitude' => -68.5364],
            ['province_id' => $sanJuanProvinceId, 'name' => 'Rivadavia', 'postal_code' => 'J5401', 'latitude' => -31.5336, 'longitude' => -68.5833],
            ['province_id' => $sanJuanProvinceId, 'name' => 'Santa Lucía', 'postal_code' => 'J5402', 'latitude' => -31.5397, 'longitude' => -68.5069],
            ['province_id' => $sanJuanProvinceId, 'name' => 'Chimbas', 'postal_code' => 'J5403', 'latitude' => -31.4750, 'longitude' => -68.5397],
            ['province_id' => $sanJuanProvinceId, 'name' => 'Rawson', 'postal_code' => 'J5425', 'latitude' => -31.5753, 'longitude' => -68.5653],
            ['province_id' => $sanJuanProvinceId, 'name' => 'Pocito', 'postal_code' => 'J5435', 'latitude' => -31.6833, 'longitude' => -68.5833],
            ['province_id' => $sanJuanProvinceId, 'name' => 'Caucete', 'postal_code' => 'J5871', 'latitude' => -31.6667, 'longitude' => -68.2833],
        ];

        foreach ($localities as $locality) {
            DB::table('localities')->updateOrInsert(
                ['province_id' => $locality['province_id'], 'name' => $locality['name']],
                $locality
            );
        }
    }
}