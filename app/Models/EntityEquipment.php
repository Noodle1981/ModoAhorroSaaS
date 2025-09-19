<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EntityEquipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'entity_equipment'; // Buena práctica especificar el nombre

    protected $fillable = [
        'entity_id', 'equipment_type_id', 'quantity', 'custom_name',
        'power_watts_override', 'avg_daily_use_hours_override',
        'replaced_by_equipment_id', 'is_backup_for_id', 'location'
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public static function getUniqueLocationsForEntity(Entity $entity): \Illuminate\Support\Collection
    {
        $locations_from_db = $entity->entityEquipment()->whereNotNull('location')->distinct()->pluck('location');

        $unique_locations = collect();

        foreach ($locations_from_db as $location_value) {
            $decoded = json_decode($location_value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                if (isset($decoded['rooms']) && is_array($decoded['rooms'])) {
                    foreach ($decoded['rooms'] as $room) {
                        if (!empty($room['name'])) {
                            $unique_locations->push($room['name']);
                        }
                    }
                } elseif (isset($decoded['name'])) {
                    if (!empty($decoded['name'])) {
                        $unique_locations->push($decoded['name']);
                    }
                }
            } else {
                if (!empty($location_value)) {
                    $unique_locations->push($location_value);
                }
            }
        }

        return $unique_locations->unique()->sort()->values();
    }
}