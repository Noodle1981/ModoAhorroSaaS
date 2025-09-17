<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecommendationsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('recommendations')->insert([
            'code' => 'WASH_IN_VALLEY_HOURS',
            'title' => 'Lava la ropa en horarios de menor costo',
            'description' => 'Aprovecha los horarios valle (generalmente nocturnos o fines de semana) para usar el lavarropas. El costo de la energía es significativamente menor.',
            'applies_to_category_id' => 3, // Asume que "Lavado" es el ID 3
            'trigger_rules' => json_encode(['time_period' => ['punta', 'llano']]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}