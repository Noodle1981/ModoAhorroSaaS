<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketEquipmentCatalogTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('market_equipment_catalog')->insert([
            'equipment_type_id' => 1, // Aire Acondicionado
            'brand' => 'BGH',
            'model_name' => 'Silent Air Inverter 3500W',
            'power_watts' => 850, // Potencia promedio en modo Inverter
            'efficiency_rating' => 'A++',
            'average_price' => 750000.00,
            'purchase_link' => 'https://www.mercadolibre.com.ar/aire-acondicionado',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}