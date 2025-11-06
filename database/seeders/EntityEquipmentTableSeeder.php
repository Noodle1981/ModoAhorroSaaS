<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntityEquipmentTableSeeder extends Seeder
{
    public function run(): void
    {
        // Equipo de ejemplo existente
        DB::table('entity_equipment')->updateOrInsert(
            [
                'entity_id' => 1,
                'custom_name' => 'Aire del Living',
            ],
            [
                'equipment_type_id' => 1, // Asume "Aire Acondicionado Split 3000 Frigorías"
                'quantity' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Agregar Aire Acondicionado Portátil (1500W) en habitación "Omar" con 0 minutos
        $portableAcTypeId = DB::table('equipment_types')->where('name', 'Aire Acondicionado Portátil')->value('id');

        if ($portableAcTypeId) {
            DB::table('entity_equipment')->updateOrInsert(
                [
                    'entity_id' => 1, // Casa Principal
                    'custom_name' => 'Aire acondicionado portátil (Omar)',
                ],
                [
                    'equipment_type_id' => $portableAcTypeId,
                    'quantity' => 1,
                    'location' => 'Omar',
                    'power_watts_override' => 1500,
                    'avg_daily_use_minutes_override' => 0,
                    'has_standby_mode' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}