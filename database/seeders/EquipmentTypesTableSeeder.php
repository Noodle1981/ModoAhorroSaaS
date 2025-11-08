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
        // Valores típicos para Argentina
        $types = [
            // category_id => [ [name, is_portable, power_watts, avg_daily_minutes, standby_watts], ... ]
            
            // ========== CLIMATIZACIÓN (category_id 1) ==========
            1 => [
                // Aire Acondicionado
                ['name' => 'Aire Acondicionado Split 2250 frigorías', 'is_portable' => false, 'power' => 1000, 'minutes' => 420, 'standby' => 2],
                ['name' => 'Aire Acondicionado Split 3000 frigorías', 'is_portable' => false, 'power' => 1300, 'minutes' => 420, 'standby' => 2],
                ['name' => 'Aire Acondicionado Split 4500 frigorías', 'is_portable' => false, 'power' => 1800, 'minutes' => 480, 'standby' => 2.5],
                ['name' => 'Aire Acondicionado Split 6000 frigorías', 'is_portable' => false, 'power' => 2400, 'minutes' => 480, 'standby' => 3],
                ['name' => 'Aire Acondicionado Portátil', 'is_portable' => true, 'power' => 1200, 'minutes' => 360, 'standby' => 1.5],
                ['name' => 'Aire Acondicionado Central', 'is_portable' => false, 'power' => 3500, 'minutes' => 480, 'standby' => 5],
                
                // Ventiladores
                ['name' => 'Ventilador de Techo', 'is_portable' => false, 'power' => 65, 'minutes' => 600, 'standby' => 0.5],
                ['name' => 'Ventilador de Pie', 'is_portable' => true, 'power' => 55, 'minutes' => 480, 'standby' => 0],
                ['name' => 'Ventilador de Mesa', 'is_portable' => true, 'power' => 40, 'minutes' => 360, 'standby' => 0],
                ['name' => 'Turbo Ventilador Industrial', 'is_portable' => true, 'power' => 180, 'minutes' => 480, 'standby' => 0],
                
                // Calefacción
                ['name' => 'Estufa Eléctrica 1000W', 'is_portable' => true, 'power' => 1000, 'minutes' => 240, 'standby' => 0],
                ['name' => 'Estufa Eléctrica 2000W', 'is_portable' => true, 'power' => 2000, 'minutes' => 240, 'standby' => 0],
                ['name' => 'Caloventor', 'is_portable' => true, 'power' => 1500, 'minutes' => 180, 'standby' => 0],
                ['name' => 'Panel Calefactor', 'is_portable' => false, 'power' => 1000, 'minutes' => 300, 'standby' => 0.5],
                ['name' => 'Radiador de Aceite', 'is_portable' => true, 'power' => 1500, 'minutes' => 300, 'standby' => 0],
                ['name' => 'Bomba de Calor (Calefacción)', 'is_portable' => false, 'power' => 1200, 'minutes' => 360, 'standby' => 2],
            ],
            
            // ========== REFRIGERACIÓN (category_id 2) ==========
            2 => [
                ['name' => 'Heladera con Freezer (No Frost)', 'is_portable' => false, 'power' => 180, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Heladera con Freezer (Cíclica)', 'is_portable' => false, 'power' => 120, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Heladera sin Freezer', 'is_portable' => false, 'power' => 80, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Freezer Vertical', 'is_portable' => false, 'power' => 220, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Freezer Horizontal', 'is_portable' => false, 'power' => 180, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Frigobar', 'is_portable' => false, 'power' => 65, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Cava de Vinos', 'is_portable' => false, 'power' => 90, 'minutes' => 1440, 'standby' => 0],
            ],
            
            // ========== LAVADO (category_id 3) ==========
            3 => [
                ['name' => 'Lavarropas Automático 6kg', 'is_portable' => false, 'power' => 500, 'minutes' => 60, 'standby' => 1],
                ['name' => 'Lavarropas Automático 8kg', 'is_portable' => false, 'power' => 650, 'minutes' => 60, 'standby' => 1],
                ['name' => 'Lavarropas Carga Frontal', 'is_portable' => false, 'power' => 550, 'minutes' => 90, 'standby' => 1.2],
                ['name' => 'Secarropas por Calor', 'is_portable' => false, 'power' => 2800, 'minutes' => 60, 'standby' => 1],
                ['name' => 'Secarropas por Condensación', 'is_portable' => false, 'power' => 2200, 'minutes' => 90, 'standby' => 1],
                ['name' => 'Lavarropa-Secarropa 2 en 1', 'is_portable' => false, 'power' => 1800, 'minutes' => 120, 'standby' => 1.5],
                ['name' => 'Lavavajillas', 'is_portable' => false, 'power' => 1800, 'minutes' => 90, 'standby' => 1.2],
                ['name' => 'Aspiradora', 'is_portable' => true, 'power' => 1400, 'minutes' => 20, 'standby' => 0],
                ['name' => 'Aspiradora Robot', 'is_portable' => true, 'power' => 30, 'minutes' => 60, 'standby' => 3],
                ['name' => 'Hidrolavadora', 'is_portable' => true, 'power' => 1500, 'minutes' => 15, 'standby' => 0],
            ],
            
            // ========== COCINA (category_id 4) ==========
            4 => [
                // Cocción
                ['name' => 'Horno Eléctrico Grande', 'is_portable' => false, 'power' => 2400, 'minutes' => 60, 'standby' => 1.5],
                ['name' => 'Horno Eléctrico Pequeño', 'is_portable' => true, 'power' => 1500, 'minutes' => 45, 'standby' => 1],
                ['name' => 'Microondas 20L', 'is_portable' => false, 'power' => 700, 'minutes' => 15, 'standby' => 3],
                ['name' => 'Microondas 30L', 'is_portable' => false, 'power' => 1200, 'minutes' => 15, 'standby' => 3.5],
                ['name' => 'Anafe Eléctrico 1 Hornalla', 'is_portable' => true, 'power' => 1000, 'minutes' => 60, 'standby' => 0],
                ['name' => 'Anafe Eléctrico 2 Hornallas', 'is_portable' => true, 'power' => 2000, 'minutes' => 60, 'standby' => 0],
                ['name' => 'Vitrocerámica 4 Hornallas', 'is_portable' => false, 'power' => 7000, 'minutes' => 90, 'standby' => 0],
                ['name' => 'Olla Eléctrica', 'is_portable' => true, 'power' => 1200, 'minutes' => 45, 'standby' => 0],
                ['name' => 'Olla a Presión Eléctrica', 'is_portable' => true, 'power' => 1000, 'minutes' => 30, 'standby' => 0.5],
                ['name' => 'Freidora de Aire (Air Fryer)', 'is_portable' => true, 'power' => 1400, 'minutes' => 25, 'standby' => 0],
                ['name' => 'Sandwichera/Panera', 'is_portable' => true, 'power' => 700, 'minutes' => 10, 'standby' => 0],
                ['name' => 'Parrilla Eléctrica', 'is_portable' => true, 'power' => 2000, 'minutes' => 30, 'standby' => 0],
                ['name' => 'Plancha para Asar', 'is_portable' => true, 'power' => 1800, 'minutes' => 30, 'standby' => 0],
                
                // Bebidas
                ['name' => 'Cafetera de Filtro', 'is_portable' => true, 'power' => 900, 'minutes' => 15, 'standby' => 1.5],
                ['name' => 'Cafetera Express', 'is_portable' => false, 'power' => 1350, 'minutes' => 10, 'standby' => 2],
                ['name' => 'Cafetera Nespresso/Dolce Gusto', 'is_portable' => true, 'power' => 1260, 'minutes' => 5, 'standby' => 1.8],
                ['name' => 'Pava Eléctrica 1.7L', 'is_portable' => true, 'power' => 2200, 'minutes' => 10, 'standby' => 0],
                ['name' => 'Dispensador de Agua Frío/Calor', 'is_portable' => false, 'power' => 500, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Juguera/Licuadora', 'is_portable' => true, 'power' => 600, 'minutes' => 5, 'standby' => 0],
                ['name' => 'Procesadora de Alimentos', 'is_portable' => true, 'power' => 750, 'minutes' => 10, 'standby' => 0],
                ['name' => 'Batidora de Mano', 'is_portable' => true, 'power' => 400, 'minutes' => 5, 'standby' => 0],
                
                // Conservación
                ['name' => 'Tostadora', 'is_portable' => true, 'power' => 800, 'minutes' => 5, 'standby' => 0],
                ['name' => 'Horno Eléctrico para Pan', 'is_portable' => true, 'power' => 1800, 'minutes' => 30, 'standby' => 0],
            ],
            
            // ========== ENTRETENIMIENTO (category_id 5) ==========
            5 => [
                // Televisores
                ['name' => 'TV LED 32"', 'is_portable' => false, 'power' => 50, 'minutes' => 300, 'standby' => 1],
                ['name' => 'TV LED 43"', 'is_portable' => false, 'power' => 80, 'minutes' => 300, 'standby' => 1.5],
                ['name' => 'TV LED 50"', 'is_portable' => false, 'power' => 100, 'minutes' => 300, 'standby' => 2],
                ['name' => 'TV LED 55"', 'is_portable' => false, 'power' => 120, 'minutes' => 300, 'standby' => 2.5],
                ['name' => 'TV LED 65"', 'is_portable' => false, 'power' => 150, 'minutes' => 300, 'standby' => 3],
                ['name' => 'Smart TV 4K 55"', 'is_portable' => false, 'power' => 140, 'minutes' => 300, 'standby' => 3],
                
                // Audio
                ['name' => 'Equipo de Música/Minicomponente', 'is_portable' => false, 'power' => 150, 'minutes' => 180, 'standby' => 5],
                ['name' => 'Home Theatre', 'is_portable' => false, 'power' => 200, 'minutes' => 180, 'standby' => 8],
                ['name' => 'Soundbar', 'is_portable' => false, 'power' => 50, 'minutes' => 180, 'standby' => 3],
                ['name' => 'Parlante Bluetooth', 'is_portable' => true, 'power' => 20, 'minutes' => 120, 'standby' => 0.5],
                
                // Gaming
                ['name' => 'PlayStation 5', 'is_portable' => true, 'power' => 200, 'minutes' => 180, 'standby' => 5],
                ['name' => 'PlayStation 4', 'is_portable' => true, 'power' => 150, 'minutes' => 180, 'standby' => 4],
                ['name' => 'Xbox Series X', 'is_portable' => true, 'power' => 180, 'minutes' => 180, 'standby' => 5],
                ['name' => 'Nintendo Switch', 'is_portable' => true, 'power' => 40, 'minutes' => 120, 'standby' => 1],
                
                // Informática (movido desde categoría 8 - Otros)
                ['name' => 'PC de Escritorio (Oficina)', 'is_portable' => false, 'power' => 150, 'minutes' => 480, 'standby' => 3],
                ['name' => 'PC de Escritorio (Gaming)', 'is_portable' => false, 'power' => 400, 'minutes' => 300, 'standby' => 5],
                ['name' => 'PC All-in-One', 'is_portable' => false, 'power' => 100, 'minutes' => 480, 'standby' => 2],
                ['name' => 'Monitor LED 24"', 'is_portable' => false, 'power' => 25, 'minutes' => 480, 'standby' => 0.5],
                ['name' => 'Monitor LED 27"', 'is_portable' => false, 'power' => 35, 'minutes' => 480, 'standby' => 0.5],
            ],
            
            // ========== ILUMINACIÓN (category_id 6) ==========
            6 => [
                ['name' => 'Lámpara LED 9W', 'is_portable' => false, 'power' => 9, 'minutes' => 300, 'standby' => 0],
                ['name' => 'Lámpara LED 12W', 'is_portable' => false, 'power' => 12, 'minutes' => 300, 'standby' => 0],
                ['name' => 'Lámpara LED 15W', 'is_portable' => false, 'power' => 15, 'minutes' => 300, 'standby' => 0],
                ['name' => 'Tubo LED 18W', 'is_portable' => false, 'power' => 18, 'minutes' => 480, 'standby' => 0],
                ['name' => 'Tira LED 5 metros', 'is_portable' => false, 'power' => 24, 'minutes' => 240, 'standby' => 0.5],
                ['name' => 'Lámpara de Escritorio LED', 'is_portable' => true, 'power' => 10, 'minutes' => 240, 'standby' => 0],
                ['name' => 'Reflector LED 50W', 'is_portable' => false, 'power' => 50, 'minutes' => 600, 'standby' => 0],
                ['name' => 'Reflector LED 100W', 'is_portable' => false, 'power' => 100, 'minutes' => 600, 'standby' => 0],
                ['name' => 'Lámpara Halógena 50W', 'is_portable' => false, 'power' => 50, 'minutes' => 180, 'standby' => 0],
            ],
            
            // ========== OTROS (category_id 8) ==========
            8 => [
                // Informática de red (Router, módem quedan aquí)
                ['name' => 'Notebook 14"', 'is_portable' => true, 'power' => 45, 'minutes' => 360, 'standby' => 1],
                ['name' => 'Notebook 15" (Gaming)', 'is_portable' => true, 'power' => 180, 'minutes' => 240, 'standby' => 2],
                ['name' => 'Impresora Láser', 'is_portable' => false, 'power' => 400, 'minutes' => 15, 'standby' => 5],
                ['name' => 'Impresora de Inyección', 'is_portable' => false, 'power' => 15, 'minutes' => 10, 'standby' => 2],
                ['name' => 'Impresora Multifunción', 'is_portable' => false, 'power' => 30, 'minutes' => 15, 'standby' => 3],
                ['name' => 'Router WiFi', 'is_portable' => false, 'power' => 10, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Switch de Red', 'is_portable' => false, 'power' => 6, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Módem', 'is_portable' => false, 'power' => 8, 'minutes' => 1440, 'standby' => 0],
                
                // Otros dispositivos
                ['name' => 'Cargador de Celular', 'is_portable' => true, 'power' => 10, 'minutes' => 120, 'standby' => 0.3],
                ['name' => 'Cargador de Notebook', 'is_portable' => true, 'power' => 65, 'minutes' => 180, 'standby' => 0.5],
                ['name' => 'Tablet', 'is_portable' => true, 'power' => 10, 'minutes' => 180, 'standby' => 0.2],
                ['name' => 'Planchita de Pelo', 'is_portable' => true, 'power' => 40, 'minutes' => 10, 'standby' => 0],
                ['name' => 'Secador de Pelo', 'is_portable' => true, 'power' => 1800, 'minutes' => 10, 'standby' => 0],
                ['name' => 'Plancha de Ropa', 'is_portable' => true, 'power' => 1200, 'minutes' => 30, 'standby' => 0],
                ['name' => 'Centro de Planchado', 'is_portable' => true, 'power' => 2400, 'minutes' => 45, 'standby' => 0],
                ['name' => 'Máquina de Coser', 'is_portable' => true, 'power' => 100, 'minutes' => 30, 'standby' => 0],
                ['name' => 'Bomba de Agua', 'is_portable' => false, 'power' => 750, 'minutes' => 60, 'standby' => 0],
                ['name' => 'Termotanque Eléctrico', 'is_portable' => false, 'power' => 1500, 'minutes' => 180, 'standby' => 0],
            ],
            
            // ========== SEGURIDAD Y HOGAR INTELIGENTE (category_id 12) ==========
            12 => [
                ['name' => 'Cámara de Seguridad WiFi', 'is_portable' => false, 'power' => 5, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Cámara de Seguridad PTZ', 'is_portable' => false, 'power' => 12, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'DVR 4 Canales', 'is_portable' => false, 'power' => 15, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'DVR 8 Canales', 'is_portable' => false, 'power' => 25, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Alarma de Seguridad', 'is_portable' => false, 'power' => 3, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Portero Eléctrico', 'is_portable' => false, 'power' => 8, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Video Portero', 'is_portable' => false, 'power' => 12, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Google Home/Nest', 'is_portable' => true, 'power' => 15, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Amazon Echo/Alexa', 'is_portable' => true, 'power' => 12, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Hub Domótica', 'is_portable' => false, 'power' => 5, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Enchufe Inteligente', 'is_portable' => true, 'power' => 1, 'minutes' => 1440, 'standby' => 0],
                ['name' => 'Timbre Video Inteligente', 'is_portable' => false, 'power' => 4, 'minutes' => 1440, 'standby' => 0],
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
        
        // 3. Insertamos los nuevos tipos (sin borrar los existentes)
        // Usamos updateOrInsert para evitar duplicados por nombre
        foreach ($dataToInsert as $data) {
            DB::table('equipment_types')->updateOrInsert(
                ['name' => $data['name']], // Condición de búsqueda
                $data // Datos a insertar/actualizar
            );
        }
        
        $this->command->info('✅ Se procesaron ' . count($dataToInsert) . ' tipos de equipos');
    }
}