<?php


namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UtilityCompaniesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('utility_companies')->insert([
            ['name' => 'Edenor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Edesur', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Metrogas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'EPEC', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aguas y Saneamientos Argentinos (AySA)', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}