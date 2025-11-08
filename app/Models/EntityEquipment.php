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
        'power_watts_override', 'avg_daily_use_minutes_override',
        'replaced_by_equipment_id', 'is_backup_for_id', 'location',
        'has_standby_mode', 'activated_at', 'replaced_at', 'replaced_by_id',
        'power_last_changed_at', 'usage_last_changed_at',
        // Frecuencia de uso
        'is_daily_use', 'usage_days_per_week', 'usage_weekdays', 'minutes_per_session',
        'optimization_profile'
    ];

    protected $casts = [
        'activated_at' => 'date',
        'replaced_at' => 'date',
        'power_last_changed_at' => 'datetime',
        'usage_last_changed_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_daily_use' => 'boolean',
        'usage_weekdays' => 'array',
        'optimization_profile' => 'array',
    ];

    /**
     * Accessor para location: decodifica JSON y devuelve solo el nombre
     */
    public function getLocationAttribute($value)
    {
        if (empty($value)) {
            return 'Sin ubicación';
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // Si tiene estructura con 'name'
            if (isset($decoded['name'])) {
                return $decoded['name'];
            }
            // Si tiene estructura con 'rooms'
            if (isset($decoded['rooms']) && is_array($decoded['rooms']) && !empty($decoded['rooms'][0]['name'])) {
                return $decoded['rooms'][0]['name'];
            }
        }

        // Si no es JSON válido, devolver el valor original
        return $value;
    }

    /**
     * Accessor para obtener el valor RAW de location (sin decodificar)
     */
    public function getRawLocationAttribute()
    {
        return $this->attributes['location'] ?? null;
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }

    public function usagePattern()
    {
        return $this->hasOne(EquipmentUsagePattern::class);
    }

    public function replacementRecommendations()
    {
        return $this->hasMany(ReplacementRecommendation::class);
    }

    public function replacedBy()
    {
        return $this->belongsTo(EntityEquipment::class, 'replaced_by_id');
    }

    public function historyRecords()
    {
        return $this->hasMany(EquipmentHistory::class);
    }

    public function snapshots()
    {
        return $this->hasMany(EquipmentUsageSnapshot::class);
    }

    public static function getUniqueLocationsForEntity(Entity $entity): \Illuminate\Support\Collection
    {
        $locations_from_db = $entity->equipments()->whereNotNull('location')->distinct()->pluck('location');

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