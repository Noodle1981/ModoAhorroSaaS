<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Supply;
use Carbon\Carbon;

class SmartMeterSeeder extends Seeder
{
    /**
     * Simula la llegada de datos de un medidor inteligente para un suministro.
     */
    public function run(): void
    {
        // Asegúrate de que existe un suministro con ID=1 para asignarle las lecturas.
        // Si no, este seeder fallará.
        $supply = Supply::find(1);
        if (!$supply) {
            $this->command->warn('No se encontró el suministro con ID=1. Saltando SmartMeterSeeder.');
            return;
        }

        $this->command->info("Generando lecturas de medidor inteligente para el suministro #1...");

        // Borramos lecturas antiguas para este suministro para no acumular datos.
        DB::table('consumption_readings')->where('supply_id', 1)->delete();

        $readings = [];
        $now = Carbon::now();

        // Generamos 96 lecturas (4 días de datos horarios)
        //Ejemplos:
        //Para simular 7 días (una semana):
        //24 horas * 7 días = 168
        //Cambia el bucle a: for ($i = 0; $i < 168; $i++) { ... }
        //Para simular 30 días (un mes):
        //24 horas * 30 días = 720
        //Cambia el bucle a: for ($i = 0; $i < 720; $i++) { ... }
        //Para simular 1 día:
        //24 horas * 1 día = 24
        //Cambia el bucle a: for ($i = 0; $i < 24; $i++) { ... }
        
        
        for ($i = 0; $i < 720; $i++) {
            $timestamp = $now->copy()->subHours($i);

            // Simulamos un patrón de consumo: más alto por la tarde/noche.
            $hour = $timestamp->hour;
            if ($hour >= 18 && $hour <= 22) {
                $consumption = rand(500, 1500) / 1000; // Consumo alto: 0.5 a 1.5 kWh
            } elseif ($hour >= 8 && $hour < 18) {
                $consumption = rand(200, 600) / 1000; // Consumo medio: 0.2 a 0.6 kWh
            } else {
                $consumption = rand(50, 150) / 1000; // Consumo base/nocturno: 0.05 a 0.15 kWh
            }

            $readings[] = [
                'supply_id' => 1,
                'reading_timestamp' => $timestamp,
                'consumed_kwh' => $consumption,
                'source' => 'simulated_smart_meter',
            ];
        }

        // Insertamos todos los datos de una vez para mayor eficiencia
        DB::table('consumption_readings')->insert($readings);
    }
}