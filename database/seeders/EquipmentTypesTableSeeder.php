<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('equipment_types')->insert([
            // Climatización (category_id 1)
            ['category_id' => 1, 'name' => 'Aire Acondicionado Split', 'default_power_watts' => 1500, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 1, 'name' => 'Aire Acondicionado de Ventana', 'default_power_watts' => 1800, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 1, 'name' => 'Ventilador de Techo', 'default_power_watts' => 60, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 1, 'name' => 'Calefactor Eléctrico', 'default_power_watts' => 2000, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 1, 'name' => 'Deshumidificador', 'default_power_watts' => 300, 'created_at' => now(), 'updated_at' => now()],
            
            // Refrigeración (category_id 2)
            ['category_id' => 2, 'name' => 'Refrigerador', 'default_power_watts' => 150, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 2, 'name' => 'Congelador', 'default_power_watts' => 200, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 2, 'name' => 'Mini Bar', 'default_power_watts' => 100, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 2, 'name' => 'Dispensador de Agua', 'default_power_watts' => 80, 'created_at' => now(), 'updated_at' => now()],
            
            // Lavado (category_id 3)
            ['category_id' => 3, 'name' => 'Lavadora de Ropa', 'default_power_watts' => 500, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 3, 'name' => 'Secadora de Ropa', 'default_power_watts' => 3000, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 3, 'name' => 'Lavavajillas', 'default_power_watts' => 1800, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 3, 'name' => 'Lavadora de Alta Eficiencia (carga frontal)', 'default_power_watts' => 250, 'created_at' => now(), 'updated_at' => now()],
            
            // Cocina (category_id 4)
            ['category_id' => 4, 'name' => 'Horno Eléctrico', 'default_power_watts' => 2400, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 4, 'name' => 'Microondas', 'default_power_watts' => 1200, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 4, 'name' => 'Cafetera Eléctrica', 'default_power_watts' => 800, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 4, 'name' => 'Anafe Eléctrico (una hornalla)', 'default_power_watts' => 1500, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 4, 'name' => 'Tostadora', 'default_power_watts' => 900, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 4, 'name' => 'Pava Eléctrica', 'default_power_watts' => 2200, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 4, 'name' => 'Licuadora', 'default_power_watts' => 500, 'created_at' => now(), 'updated_at' => now()],
            
            // Entretenimiento (category_id 5)
            ['category_id' => 5, 'name' => 'Televisor LED', 'default_power_watts' => 100, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 5, 'name' => 'Sistema de Sonido', 'default_power_watts' => 200, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 5, 'name' => 'Consola de Videojuegos', 'default_power_watts' => 150, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 5, 'name' => 'Teatro en Casa (Home Theater)', 'default_power_watts' => 300, 'created_at' => now(), 'updated_at' => now()],
            
            // Iluminación (category_id 6)
            ['category_id' => 6, 'name' => 'Luz Incandescente', 'default_power_watts' => 100, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 6, 'name' => 'Luz LED', 'default_power_watts' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 6, 'name' => 'Luz Fluorescente', 'default_power_watts' => 40, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 6, 'name' => 'Foco Halógeno', 'default_power_watts' => 50, 'created_at' => now(), 'updated_at' => now()],
            
            // Agua Caliente Sanitaria (ACS) (category_id 7)
            ['category_id' => 7, 'name' => 'Calentador de Agua Eléctrico', 'default_power_watts' => 4500, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 7, 'name' => 'Calentador de Agua a Gas', 'default_power_watts' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 7, 'name' => 'Bomba de Calor para ACS', 'default_power_watts' => 3000, 'created_at' => now(), 'updated_at' => now()],
            
            // Otros (category_id 8)
            ['category_id' => 8, 'name' => 'Computadora de Escritorio', 'default_power_watts' => 200, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 8, 'name' => 'Laptop', 'default_power_watts' => 50, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 8, 'name' => 'Impresora', 'default_power_watts' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 8, 'name' => 'Cargador de Teléfono', 'default_power_watts' => 50, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 8, 'name' => 'Aspiradora', 'default_power_watts' => 1400, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 8, 'name' => 'Plancha de Ropa', 'default_power_watts' => 1000, 'created_at' => now(), 'updated_at' => now()],
            
            // Herramientas (category_id 9)
            ['category_id' => 9, 'name' => 'Taladro Eléctrico', 'default_power_watts' => 600, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 9, 'name' => 'Sierra Eléctrica', 'default_power_watts' => 1200, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 9, 'name' => 'Lijadora Eléctrica', 'default_power_watts' => 300, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 9, 'name' => 'Soldadora Inverter', 'default_power_watts' => 4000, 'created_at' => now(), 'updated_at' => now()],
            
            // Cuidado Personal (category_id 10)
            ['category_id' => 10, 'name' => 'Secador de Pelo', 'default_power_watts' => 1800, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 10, 'name' => 'Planchita de Pelo', 'default_power_watts' => 300, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 10, 'name' => 'Afeitadora Eléctrica', 'default_power_watts' => 15, 'created_at' => now(), 'updated_at' => now()],
            
            // Limpieza de Cocina (category_id 11)
            ['category_id' => 11, 'name' => 'Extractor de Aire (Campana)', 'default_power_watts' => 200, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 11, 'name' => 'Triturador de Desechos', 'default_power_watts' => 400, 'created_at' => now(), 'updated_at' => now()],
            
            // Seguridad y Hogar Inteligente (category_id 12)
            ['category_id' => 12, 'name' => 'Cámara de Seguridad', 'default_power_watts' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 12, 'name' => 'Asistente de Voz (Google Home, Alexa)', 'default_power_watts' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 12, 'name' => 'Enchufe Inteligente', 'default_power_watts' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => 12, 'name' => 'Router de Internet', 'default_power_watts' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}