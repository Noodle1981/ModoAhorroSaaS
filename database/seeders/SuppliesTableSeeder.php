<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuppliesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('supplies')->insert([
            'entity_id' => 1, // Asume que existe la entidad con ID 1
            'supply_point_identifier' => '0273000012345678',
            'type' => 'electricity',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}