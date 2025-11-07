<?php

namespace Database\Seeders;

use App\Models\EquipmentType;
use App\Models\MarketEquipmentCatalog;
use Illuminate\Database\Seeder;

class MarketEquipmentCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener tipos de equipos (asumiendo que ya existen)
        $heladera = EquipmentType::where('name', 'LIKE', '%Heladera%')->orWhere('name', 'LIKE', '%Refrigerador%')->first();
        $ac = EquipmentType::where('name', 'LIKE', '%Aire%')->orWhere('name', 'LIKE', '%AC%')->first();
        $lavarropas = EquipmentType::where('name', 'LIKE', '%Lavarropas%')->orWhere('name', 'LIKE', '%Lavadora%')->first();
        $tv = EquipmentType::where('name', 'LIKE', '%TV%')->orWhere('name', 'LIKE', '%Televisor%')->first();

        // HELADERAS - Alta eficiencia
        if ($heladera) {
            $refrigeradores = [
                [
                    'equipment_type_id' => $heladera->id,
                    'brand' => 'Samsung',
                    'model_name' => 'RT38K5932SL/ZS Frost Free Inverter',
                    'power_watts' => 120,
                    'annual_consumption_kwh' => 350.00,
                    'energy_label' => 'A++',
                    'estimated_price_ars' => 850000.00,
                    'is_recommended' => true,
                    'is_active' => true,
                    'features' => 'Tecnología Inverter, 382L, Frost Free, bajo consumo',
                    'purchase_link' => 'https://www.samsung.com/ar/',
                ],
                [
                    'equipment_type_id' => $heladera->id,
                    'brand' => 'Whirlpool',
                    'model_name' => 'WRM54K1 Inverter 462L',
                    'power_watts' => 140,
                    'annual_consumption_kwh' => 420.00,
                    'energy_label' => 'A+',
                    'estimated_price_ars' => 750000.00,
                    'is_recommended' => true,
                    'is_active' => true,
                    'features' => 'Inverter, No Frost, iluminación LED',
                    'purchase_link' => 'https://www.whirlpool.com.ar/',
                ],
                [
                    'equipment_type_id' => $heladera->id,
                    'brand' => 'LG',
                    'model_name' => 'GT40WGP Side by Side Inverter',
                    'power_watts' => 180,
                    'annual_consumption_kwh' => 520.00,
                    'energy_label' => 'A',
                    'estimated_price_ars' => 1200000.00,
                    'is_recommended' => true,
                    'is_active' => true,
                    'features' => 'Side by Side, Inverter, dispensador agua/hielo',
                    'purchase_link' => 'https://www.lg.com/ar/',
                ],
            ];

            foreach ($refrigeradores as $item) {
                MarketEquipmentCatalog::updateOrInsert(
                    ['brand' => $item['brand'], 'model_name' => $item['model_name']],
                    $item
                );
            }
        }

        // AIRES ACONDICIONADOS - Alta eficiencia
        if ($ac) {
            $aires = [
                [
                    'equipment_type_id' => $ac->id,
                    'brand' => 'Daikin',
                    'model_name' => 'FTXS35K Inverter 3200W',
                    'power_watts' => 900,
                    'annual_consumption_kwh' => 650.00,
                    'energy_label' => 'A+++',
                    'estimated_price_ars' => 650000.00,
                    'is_recommended' => true,
                    'is_active' => true,
                    'features' => 'Inverter, bajo ruido, filtro purificador',
                    'purchase_link' => 'https://www.daikin.com.ar/',
                ],
                [
                    'equipment_type_id' => $ac->id,
                    'brand' => 'Samsung',
                    'model_name' => 'AR12TXHQASINEU Wind-Free',
                    'power_watts' => 1050,
                    'annual_consumption_kwh' => 750.00,
                    'energy_label' => 'A++',
                    'estimated_price_ars' => 580000.00,
                    'is_recommended' => true,
                    'is_active' => true,
                    'features' => 'Wind-Free, Inverter, 3500W, Wi-Fi',
                    'purchase_link' => 'https://www.samsung.com/ar/',
                ],
                [
                    'equipment_type_id' => $ac->id,
                    'brand' => 'LG',
                    'model_name' => 'Dual Inverter S4-W12JA3AA',
                    'power_watts' => 980,
                    'annual_consumption_kwh' => 700.00,
                    'energy_label' => 'A++',
                    'estimated_price_ars' => 520000.00,
                    'is_recommended' => true,
                    'is_active' => true,
                    'features' => 'Dual Inverter, 3000W, ahorra hasta 70%',
                    'purchase_link' => 'https://www.lg.com/ar/',
                ],
            ];

            foreach ($aires as $item) {
                MarketEquipmentCatalog::updateOrInsert(
                    ['brand' => $item['brand'], 'model_name' => $item['model_name']],
                    $item
                );
            }
        }

        // LAVARROPAS - Alta eficiencia
        if ($lavarropas) {
            $lavadoras = [
                [
                    'equipment_type_id' => $lavarropas->id,
                    'brand' => 'Samsung',
                    'model_name' => 'WW90TA046AE EcoBubble 9kg',
                    'power_watts' => 2000,
                    'annual_consumption_kwh' => 220.00,
                    'energy_label' => 'A+++',
                    'estimated_price_ars' => 450000.00,
                    'is_recommended' => true,
                    'is_active' => true,
                    'features' => 'EcoBubble, Inverter, 9kg, bajo consumo agua',
                    'purchase_link' => 'https://www.samsung.com/ar/',
                ],
                [
                    'equipment_type_id' => $lavarropas->id,
                    'brand' => 'Drean',
                    'model_name' => 'Next 8.14 Eco Inverter',
                    'power_watts' => 1800,
                    'annual_consumption_kwh' => 195.00,
                    'energy_label' => 'A++',
                    'estimated_price_ars' => 380000.00,
                    'is_recommended' => true,
                    'is_active' => true,
                    'features' => 'Inverter, 8kg, carga frontal, eficiente',
                    'purchase_link' => 'https://www.drean.com.ar/',
                ],
            ];

            foreach ($lavadoras as $item) {
                MarketEquipmentCatalog::updateOrInsert(
                    ['brand' => $item['brand'], 'model_name' => $item['model_name']],
                    $item
                );
            }
        }

        // TVs - Alta eficiencia
        if ($tv) {
            $televisores = [
                [
                    'equipment_type_id' => $tv->id,
                    'brand' => 'Samsung',
                    'model_name' => 'QN55Q60C QLED 4K 55"',
                    'power_watts' => 100,
                    'annual_consumption_kwh' => 146.00,
                    'energy_label' => 'A+',
                    'estimated_price_ars' => 650000.00,
                    'is_recommended' => true,
                    'is_active' => true,
                    'features' => 'QLED, 4K, HDR10+, modo eco',
                    'purchase_link' => 'https://www.samsung.com/ar/',
                ],
                [
                    'equipment_type_id' => $tv->id,
                    'brand' => 'LG',
                    'model_name' => 'OLED55C3PSA 55" 4K',
                    'power_watts' => 120,
                    'annual_consumption_kwh' => 175.00,
                    'energy_label' => 'A',
                    'estimated_price_ars' => 1200000.00,
                    'is_recommended' => true,
                    'is_active' => true,
                    'features' => 'OLED, 4K, a9 Gen6 AI, bajo consumo',
                    'purchase_link' => 'https://www.lg.com/ar/',
                ],
            ];

            foreach ($televisores as $item) {
                MarketEquipmentCatalog::updateOrInsert(
                    ['brand' => $item['brand'], 'model_name' => $item['model_name']],
                    $item
                );
            }
        }

        $this->command->info('✅ Catálogo de equipos eficientes creado/actualizado exitosamente');
    }
}
