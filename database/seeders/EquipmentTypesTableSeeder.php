<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EquipmentTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Usamos Carbon para manejar las fechas de forma consistente
        $now = Carbon::now();

        // 1. Definimos nuestros datos en un array más estructurado
        $types = [
            // category_id => [ [name, is_portable, power, hours, standby], ... ]
            
            // Climatización (category_id 1)
            1 => [
                ['name' => 'Aire Acondicionado Split', 'is_portable' => false, 'power' => 1500, 'minutes' => 480, 'standby' => 1.5],
                ['name' => 'Ventilador de Techo', 'is_portable' => false, 'power' => 60, 'minutes' => 600, 'standby' => 0.5],
                ['name' => 'Calefactor Eléctrico', 'is_portable' => true, 'power' => 2000, 'minutes' => 300, 'standby' => 0],
            ],
            // Refrigeración (category_id 2)
            2 => [
                ['name' => 'Refrigerador', 'is_portable' => false, 'power' => 150, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Congelador', 'is_portable' => false, 'power' => 200, 'minutes' => 1440, 'standby' => 0],
            ],
            // Lavado (category_id 3)
            3 => [
                ['name' => 'Lavadora de Ropa', 'is_portable' => false, 'power' => 500, 'minutes' => 60, 'standby' => 1],
                ['name' => 'Secadora de Ropa', 'is_portable' => false, 'power' => 3000, 'minutes' => 60, 'standby' => 1],
                ['name' => 'Lavavajillas', 'is_portable' => false, 'power' => 1800, 'minutes' => 90, 'standby' => 1.2],
            ],
            // Cocina (category_id 4)
            4 => [
                ['name' => 'Horno Eléctrico', 'is_portable' => false, 'power' => 2400, 'minutes' => 60, 'standby' => 1.1],
                ['name' => 'Microondas', 'is_portable' => false, 'power' => 1200, 'minutes' => 12, 'standby' => 2.5],
                ['name' => 'Cafetera Eléctrica', 'is_portable' => true, 'power' => 800, 'minutes' => 9, 'standby' => 0.8],
                ['name' => 'Pava Eléctrica', 'is_portable' => true, 'power' => 2200, 'minutes' => 6, 'standby' => 0],
            ],
            // Entretenimiento (category_id 5)
            5 => [
                ['name' => 'Televisor LED', 'is_portable' => false, 'power' => 100, 'minutes' => 240, 'standby' => 3],
                ['name' => 'Sistema de Sonido', 'is_portable' => false, 'power' => 200, 'minutes' => 180, 'standby' => 5],
                ['name' => 'Consola de Videojuegos', 'is_portable' => true, 'power' => 150, 'minutes' => 120, 'standby' => 4],
            ],
            // Iluminación (category_id 6)
            6 => [
                ['name' => 'Luz LED', 'is_portable' => false, 'power' => 10, 'minutes' => 360, 'standby' => 0],
            ],
            // Otros (category_id 8)
            8 => [
                ['name' => 'Computadora de Escritorio', 'is_portable' => false, 'power' => 200, 'minutes' => 480, 'standby' => 3.5],
                ['name' => 'Laptop', 'is_portable' => true, 'power' => 50, 'minutes' => 300, 'standby' => 1],
                ['name' => 'Impresora', 'is_portable' => false, 'power' => 30, 'minutes' => 6, 'standby' => 2],
            ],
            // Seguridad y Hogar Inteligente (category_id 12)
            12 => [
                ['name' => 'Cámara de Seguridad', 'is_portable' => false, 'power' => 5, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Asistente de Voz (Google Home, Alexa)', 'is_portable' => true, 'power' => 10, 'minutes' => 1440, 'standby' => 1.5],
                ['name' => 'Router de Internet', 'is_portable' => false, 'power' => 8, 'minutes' => 1440, 'standby' => 0],
            ],
        ];

        // 2. Preparamos el array final para la inserción masiva
        $dataToInsert = [];
        foreach ($types as $categoryId => $equipmentList) {
            foreach ($equipmentList as $equipment) {
                $dataToInsert[] = [
                    'category_id' => $categoryId,
                    'name' => $equipment['name'],
                    'is_portable' => $equipment['is_portable'],
                    'default_power_watts' => $equipment['power'],
                    'default_avg_daily_use_minutes' => $equipment['minutes'],
                    'standby_power_watts' => $equipment['standby'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        
        // 3. Borramos los datos antiguos e insertamos los nuevos
        DB::table('equipment_types')->delete();
        DB::table('equipment_types')->insert($dataToInsert);
    }
}