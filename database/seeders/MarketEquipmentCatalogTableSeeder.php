<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// ¡No uses DB::table! Mejor importa el modelo directamente.
use App\Models\MarketEquipmentCatalog;

class MarketEquipmentCatalogTableSeeder extends Seeder
{
    public function run(): void
    {
        // --- TU EQUIPO EXISTENTE, AHORA EN FORMATO SEGURO ---
        MarketEquipmentCatalog::updateOrCreate(
            [
                // Columnas para BUSCAR y evitar duplicados
                'equipment_type_id' => 1, // Aire Acondicionado
                'model_name' => 'Silent Air Inverter 3500W',
            ],
            [
                // Columnas para rellenar o ACTUALIZAR
                'brand' => 'BGH',
                'power_watts' => 850,
                'efficiency_rating' => 'A++',
                'average_price' => 750000.00,
                'purchase_link' => 'https://www.mercadolibre.com.ar/aire-acondicionado',
                'is_active' => true,
            ]
        );

        MarketEquipmentCatalog::updateOrCreate(
            [
                'equipment_type_id' => 1, // Aire Acondicionado
                'model_name' => 'EcoCool Inverter 3200W',
            ],
            [
                'brand' => 'Samsung',
                'power_watts' => 1200,
                'efficiency_rating' => 'A+',
                'average_price' => 650000.00,
                'is_active' => true,
            ]
        );

        MarketEquipmentCatalog::updateOrCreate(
            [
                'equipment_type_id' => 4, // Refrigerador
                'model_name' => 'No-Frost Master 400L',
            ],
            [
                'brand' => 'Whirlpool',
                'power_watts' => 120,
                'efficiency_rating' => 'A++',
                'average_price' => 500000.00,
                'is_active' => true,
            ]
        );

        MarketEquipmentCatalog::updateOrCreate(
            [
                'equipment_type_id' => 16, // Luz LED
                'model_name' => 'Bright Bulb 7W',
            ],
            [
                'brand' => 'Philips',
                'power_watts' => 7,
                'efficiency_rating' => 'A++',
                'average_price' => 1500.00,
                'is_active' => true,
            ]
        );

        // --- PLANTILLA PARA AÑADIR NUEVOS EQUIPOS ---
        // Imagina que el log te pide una "Pava Eléctrica" (ID de tipo: 25)
        /*
        MarketEquipmentCatalog::updateOrCreate(
            [
                'equipment_type_id' => 25,
                'model_name' => 'PE-K17DW',
            ],
            [
                'brand' => 'Atma',
                'power_watts' => 1850,
                'average_price' => 25000,
                'is_active' => true,
            ]
        );
        */
    }
}
