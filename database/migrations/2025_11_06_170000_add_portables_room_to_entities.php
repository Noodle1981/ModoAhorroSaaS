<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Entity;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar "Portátiles" como ubicación a todas las entities existentes
        Entity::chunk(100, function ($entities) {
            foreach ($entities as $entity) {
                $details = $entity->details ?? [];
                $rooms = $details['rooms'] ?? [];
                
                // Verificar si ya existe "Portátiles"
                $hasPortables = collect($rooms)->contains(function ($room) {
                    return isset($room['name']) && $room['name'] === 'Portátiles';
                });
                
                if (!$hasPortables) {
                    // Agregar "Portátiles" como primera ubicación
                    array_unshift($rooms, [
                        'name' => 'Portátiles',
                        'description' => 'Equipos móviles sin ubicación fija (celulares, tablets, notebooks, etc.)'
                    ]);
                    
                    $details['rooms'] = $rooms;
                    $entity->details = $details;
                    $entity->save();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover "Portátiles" de todas las entities
        Entity::chunk(100, function ($entities) {
            foreach ($entities as $entity) {
                $details = $entity->details ?? [];
                $rooms = $details['rooms'] ?? [];
                
                // Filtrar para remover "Portátiles"
                $rooms = array_values(array_filter($rooms, function ($room) {
                    return !isset($room['name']) || $room['name'] !== 'Portátiles';
                }));
                
                $details['rooms'] = $rooms;
                $entity->details = $details;
                $entity->save();
            }
        });
    }
};
