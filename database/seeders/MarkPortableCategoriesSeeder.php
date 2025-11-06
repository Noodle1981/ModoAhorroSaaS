<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquipmentCategory;

class MarkPortableCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Categorías que son portátiles (nombres exactos o parciales)
        $portableCategories = [
            'Celular',
            'Smartphone',
            'Teléfono',
            'Tablet',
            'Notebook',
            'Laptop',
            'Computadora Portátil',
            'Portátil',
            'Cargador',
            'Wearable',
            'Smartwatch',
            'E-reader',
            'Kindle',
            'Cámara',
            'Consola Portátil',
            'Nintendo Switch',
            'Steam Deck',
        ];

        foreach ($portableCategories as $categoryName) {
            // Buscar por coincidencia parcial (case insensitive)
            EquipmentCategory::where('name', 'LIKE', "%{$categoryName}%")
                ->update(['is_portable' => true]);
        }

        // También actualizar por descripción si contiene palabras clave
        EquipmentCategory::where('description', 'LIKE', '%portátil%')
            ->orWhere('description', 'LIKE', '%móvil%')
            ->orWhere('description', 'LIKE', '%recargable%')
            ->update(['is_portable' => true]);

        $this->command->info('✅ Categorías portátiles marcadas correctamente.');
    }
}
