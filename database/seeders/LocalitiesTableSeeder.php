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

        DB::table('localities')->insert([
            // Localities for San Juan
            ['province_id' => $sanJuanProvinceId, 'name' => 'San Juan Capital', 'postal_code' => 'J5400'],
            ['province_id' => $sanJuanProvinceId, 'name' => 'Rivadavia', 'postal_code' => 'J5401'],
            ['province_id' => $sanJuanProvinceId, 'name' => 'Santa Lucía', 'postal_code' => 'J5402'],
            ['province_id' => $sanJuanProvinceId, 'name' => 'Chimbas', 'postal_code' => 'J5403'],
            ['province_id' => $sanJuanProvinceId, 'name' => 'Rawson', 'postal_code' => 'J5425'],
            ['province_id' => $sanJuanProvinceId, 'name' => 'Pocito', 'postal_code' => 'J5435'],
            ['province_id' => $sanJuanProvinceId, 'name' => 'Caucete', 'postal_code' => 'J5871'],
        ]);
    }
}