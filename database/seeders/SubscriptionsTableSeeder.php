<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('subscriptions')->insert([
            'company_id' => 1, // Asume que existe la compañía con ID 1
            'plan_id' => 2,    // Le asigna el plan 'Base' (con ID 2)
            'starts_at' => now(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}