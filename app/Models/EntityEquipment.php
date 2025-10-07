<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntityEquipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'entity_equipment';

    protected $fillable = [
        'entity_id', 'equipment_type_id', 'quantity', 'custom_name', 'location',
        'power_watts_override', 'avg_daily_use_minutes_override', 'has_standby_mode',
        'replaced_by_equipment_id', 'is_backup_for_id', 'acquisition_cost',
    ];
    
    protected $casts = [
        'has_standby_mode' => 'boolean',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class);
    }

    public function replacement(): BelongsTo
    {
        // Corregido: La relación es con la clave foránea 'replaced_by_equipment_id'
        // pero apunta a la clave primaria 'id' de la misma tabla.
        return $this->belongsTo(EntityEquipment::class, 'replaced_by_equipment_id');
    }

    public function usageSnapshots(): HasMany
    {
        return $this->hasMany(EquipmentUsageSnapshot::class);
    }
    
    // HEMOS ELIMINADO EL MÉTODO getUniqueLocationsForEntity()
}