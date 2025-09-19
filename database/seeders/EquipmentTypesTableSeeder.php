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
            // category_id => [ [name, is_portable, power], [name, is_portable, power], ... ]
            
            // Climatización (category_id 1)
            1 => [
                ['name' => 'Aire Acondicionado Split', 'is_portable' => false, 'power' => 1500],
                ['name' => 'Aire Acondicionado de Ventana', 'is_portable' => false, 'power' => 1800],
                ['name' => 'Ventilador de Techo', 'is_portable' => false, 'power' => 60],
                ['name' => 'Calefactor Eléctrico', 'is_portable' => true, 'power' => 2000],
                ['name' => 'Deshumidificador', 'is_portable' => true, 'power' => 300],
            ],
            // Refrigeración (category_id 2)
            2 => [
                ['name' => 'Refrigerador', 'is_portable' => false, 'power' => 150],
                ['name' => 'Congelador', 'is_portable' => false, 'power' => 200],
                ['name' => 'Mini Bar', 'is_portable' => false, 'power' => 100],
                ['name' => 'Dispensador de Agua', 'is_portable' => false, 'power' => 80],
            ],
            // Lavado (category_id 3)
            3 => [
                ['name' => 'Lavadora de Ropa', 'is_portable' => false, 'power' => 500],
                ['name' => 'Secadora de Ropa', 'is_portable' => false, 'power' => 3000],
                ['name' => 'Lavavajillas', 'is_portable' => false, 'power' => 1800],
                ['name' => 'Lavadora de Alta Eficiencia (carga frontal)', 'is_portable' => false, 'power' => 250],
            ],
            // Cocina (category_id 4)
            4 => [
                ['name' => 'Horno Eléctrico', 'is_portable' => false, 'power' => 2400],
                ['name' => 'Microondas', 'is_portable' => false, 'power' => 1200],
                ['name' => 'Cafetera Eléctrica', 'is_portable' => true, 'power' => 800],
                ['name' => 'Anafe Eléctrico (una hornalla)', 'is_portable' => false, 'power' => 1500],
                ['name' => 'Tostadora', 'is_portable' => true, 'power' => 900],
                ['name' => 'Pava Eléctrica', 'is_portable' => true, 'power' => 2200],
                ['name' => 'Licuadora', 'is_portable' => true, 'power' => 500],
            ],
            // Entretenimiento (category_id 5)
            5 => [
                ['name' => 'Televisor LED', 'is_portable' => false, 'power' => 100],
                ['name' => 'Sistema de Sonido', 'is_portable' => false, 'power' => 200],
                ['name' => 'Consola de Videojuegos', 'is_portable' => true, 'power' => 150],
                ['name' => 'Teatro en Casa (Home Theater)', 'is_portable' => false, 'power' => 300],
            ],
            // Iluminación (category_id 6)
            6 => [
                ['name' => 'Luz Incandescente', 'is_portable' => false, 'power' => 100],
                ['name' => 'Luz LED', 'is_portable' => false, 'power' => 10],
                ['name' => 'Luz Fluorescente', 'is_portable' => false, 'power' => 40],
                ['name' => 'Foco Halógeno', 'is_portable' => false, 'power' => 50],
            ],
            // Agua Caliente Sanitaria (ACS) (category_id 7)
            7 => [
                ['name' => 'Calentador de Agua Eléctrico', 'is_portable' => false, 'power' => 4500],
                ['name' => 'Calentador de Agua a Gas', 'is_portable' => false, 'power' => 0],
                ['name' => 'Bomba de Calor para ACS', 'is_portable' => false, 'power' => 3000],
            ],
            // Otros (category_id 8)
            8 => [
                ['name' => 'Computadora de Escritorio', 'is_portable' => false, 'power' => 200],
                ['name' => 'Laptop', 'is_portable' => true, 'power' => 50],
                ['name' => 'Impresora', 'is_portable' => false, 'power' => 30],
                ['name' => 'Cargador de Teléfono', 'is_portable' => true, 'power' => 50],
                ['name' => 'Aspiradora', 'is_portable' => true, 'power' => 1400],
                ['name' => 'Plancha de Ropa', 'is_portable' => true, 'power' => 1000],
            ],
            // Herramientas (category_id 9)
            9 => [
                ['name' => 'Taladro Eléctrico', 'is_portable' => true, 'power' => 600],
                ['name' => 'Sierra Eléctrica', 'is_portable' => true, 'power' => 1200],
                ['name' => 'Lijadora Eléctrica', 'is_portable' => true, 'power' => 300],
                ['name' => 'Soldadora Inverter', 'is_portable' => true, 'power' => 4000],
            ],
            // Cuidado Personal (category_id 10)
            10 => [
                ['name' => 'Secador de Pelo', 'is_portable' => true, 'power' => 1800],
                ['name' => 'Planchita de Pelo', 'is_portable' => true, 'power' => 300],
                ['name' => 'Afeitadora Eléctrica', 'is_portable' => true, 'power' => 15],
            ],
            // Limpieza de Cocina (category_id 11)
            11 => [
                ['name' => 'Extractor de Aire (Campana)', 'is_portable' => false, 'power' => 200],
                ['name' => 'Triturador de Desechos', 'is_portable' => false, 'power' => 400],
            ],
            // Seguridad y Hogar Inteligente (category_id 12)
            12 => [
                ['name' => 'Cámara de Seguridad', 'is_portable' => false, 'power' => 5],
                ['name' => 'Asistente de Voz (Google Home, Alexa)', 'is_portable' => true, 'power' => 10],
                ['name' => 'Enchufe Inteligente', 'is_portable' => true, 'power' => 2],
                ['name' => 'Router de Internet', 'is_portable' => false, 'power' => 2],
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