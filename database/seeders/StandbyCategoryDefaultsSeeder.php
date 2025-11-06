<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use Illuminate\Database\Seeder;

class StandbyCategoryDefaultsSeeder extends Seeder
{
    /**
     * Setea supports_standby=true para categorías que típicamente tienen standby
     */
    public function run(): void
    {
        // Nombres posibles (normalizados) que podrían existir en tu catálogo
        $targets = [
            'Entretenimiento',
            'Electrodomésticos',
            'Electrodomesticos',
            'Seguridad',
            'Hogar inteligente',
            'Hogar Inteligente',
            'Smart Home',
            'IoT / Smart Home',
        ];

        EquipmentCategory::whereIn('name', $targets)->update(['supports_standby' => true]);
    }
}
