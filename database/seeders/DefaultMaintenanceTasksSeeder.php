<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquipmentType;
use App\Models\MaintenanceTask;

class DefaultMaintenanceTasksSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar tipos comunes (ajusta los slugs/nombres a tu catálogo)
        $ac = EquipmentType::where('name','like','%Aire%')->orWhere('slug','ac')->first();
        $fridge = EquipmentType::where('name','like','%Heladera%')->orWhere('slug','fridge')->first();
        $washer = EquipmentType::where('name','like','%Lavarrop%')->orWhere('slug','washer')->first();

        if ($ac) {
            MaintenanceTask::updateOrCreate([
                'equipment_type_id' => $ac->id,
                'name' => 'Limpieza de filtro',
            ],[
                'description' => 'Remover y limpiar filtro para mejorar eficiencia y calidad del aire.',
                'recommended_frequency_days' => 30,
                'recommended_season' => 'all',
                'maintenance_type' => 'filter_clean',
            ]);

            MaintenanceTask::updateOrCreate([
                'equipment_type_id' => $ac->id,
                'name' => 'Limpieza profunda',
            ],[
                'description' => 'Limpieza profunda de serpentinas y bandeja de drenaje.',
                'recommended_frequency_days' => 180, // 6 meses
                'recommended_season' => 'all',
                'maintenance_type' => 'deep_clean',
                'variable_interval_json' => [
                    'usage_hours_per_day' => [
                        '>6' => 180,
                        '<=6' => 365
                    ]
                ]
            ]);
        }

        if ($fridge) {
            MaintenanceTask::updateOrCreate([
                'equipment_type_id' => $fridge->id,
                'name' => 'Deshielo manual',
            ],[
                'description' => 'Evitar acumulación de hielo mayor a 3-5mm para mantener eficiencia.',
                'recommended_frequency_days' => 90,
                'recommended_season' => 'all',
                'maintenance_type' => 'defrost',
                'variable_interval_json' => [
                    'ice_thickness_mm' => [
                        '>=5' => 30,
                        '>=3' => 60,
                        '<3' => 120
                    ]
                ]
            ]);
            
            MaintenanceTask::updateOrCreate([
                'equipment_type_id' => $fridge->id,
                'name' => 'Limpieza burlete',
            ],[
                'description' => 'Limpiar sellos de puertas para asegurar cierre hermético.',
                'recommended_frequency_days' => 60,
                'recommended_season' => 'all',
                'maintenance_type' => 'gasket_clean',
            ]);
        }

        if ($washer) {
            MaintenanceTask::updateOrCreate([
                'equipment_type_id' => $washer->id,
                'name' => 'Limpieza de tambor',
            ],[
                'description' => 'Ciclo de limpieza para remover residuos y mejorar eficiencia.',
                'recommended_frequency_days' => 90,
                'recommended_season' => 'all',
                'maintenance_type' => 'drum_clean',
            ]);
        }
    }
}
