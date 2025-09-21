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
