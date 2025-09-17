<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompaniesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('companies')->insert([
            'name' => 'Mi Primera Empresa Cliente',
            'tax_id' => '30-12345678-9',
            'address' => 'Av. Corrientes 1234',
            'phone' => '11-5555-4444',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}