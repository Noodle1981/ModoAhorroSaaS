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
        'replaced_by_equipment_id', 'is_backup_for_id',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }
}