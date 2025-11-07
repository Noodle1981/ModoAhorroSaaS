<?php

namespace Database\Seeders;

use App\Models\Entity;
use App\Models\EntityEquipment;
use App\Models\EquipmentType;
use Illuminate\Database\Seeder;

class TestEquipmentSeeder extends Seeder
{
    /**
     * Seed de equipos de prueba para testear recomendaciones de reemplazo
     */
    public function run(): void
    {
        $entity = Entity::first();
        
        if (!$entity) {
            $this->command->error('No hay entidades en la base de datos');
            return;
        }

        // Obtener tipos de equipos
        $heladera = EquipmentType::where('name', 'LIKE', '%Heladera%')->first();
        $ac = EquipmentType::where('name', 'LIKE', '%Aire%')->first();
        $lavarropas = EquipmentType::where('name', 'LIKE', '%Lavarropas%')->first();
        $tv = EquipmentType::where('name', 'LIKE', '%TV%')->first();

        $equipments = [];

        // Heladera vieja e ineficiente
        if ($heladera) {
            $equipments[] = [
                'entity_id' => $entity->id,
                'equipment_type_id' => $heladera->id,
                'custom_name' => 'Heladera Vieja Ineficiente',
                'power_watts_override' => 250, // 250W (consume mucho)
                'quantity' => 1,
                'location' => json_encode(['name' => 'Cocina']),
            ];
        }

        // Aire acondicionado antiguo
        if ($ac) {
            $equipments[] = [
                'entity_id' => $entity->id,
                'equipment_type_id' => $ac->id,
                'custom_name' => 'AC Habitación Antiguo',
                'power_watts_override' => 1500, // 1500W (muy ineficiente)
                'quantity' => 1,
                'location' => json_encode(['name' => 'Dormitorio Principal']),
            ];
        }

        // Lavarropas convencional
        if ($lavarropas) {
            $equipments[] = [
                'entity_id' => $entity->id,
                'equipment_type_id' => $lavarropas->id,
                'custom_name' => 'Lavarropas Convencional',
                'power_watts_override' => 2200, // 2200W
                'quantity' => 1,
                'location' => json_encode(['name' => 'Lavadero']),
            ];
        }

        // TV LCD viejo
        if ($tv) {
            $equipments[] = [
                'entity_id' => $entity->id,
                'equipment_type_id' => $tv->id,
                'custom_name' => 'TV LCD 42" Antiguo',
                'power_watts_override' => 180, // 180W (los nuevos consumen 100W)
                'quantity' => 1,
                'location' => json_encode(['name' => 'Living']),
            ];
        }

        foreach ($equipments as $equipment) {
            EntityEquipment::updateOrInsert(
                [
                    'entity_id' => $equipment['entity_id'],
                    'equipment_type_id' => $equipment['equipment_type_id'],
                    'custom_name' => $equipment['custom_name']
                ],
                $equipment
            );
        }

        $this->command->info('✅ Equipos de prueba creados para testing de recomendaciones');
        $this->command->info("   Entidad: {$entity->name}");
        $this->command->info("   Equipos agregados: " . count($equipments));
        $this->command->newLine();
        $this->command->warn('💡 Ahora ejecuta: php artisan equipment:analyze-replacements --entity=' . $entity->id);
    }
}
