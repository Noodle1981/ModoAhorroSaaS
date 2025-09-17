<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentUsagePattern extends Model
{
    use HasFactory;

    protected $table = 'equipment_usage_patterns';

    protected $fillable = [
        'entity_equipment_id',
        'day_of_week',
        'start_time',
        'duration_minutes',
        'season',
    ];

    public function entityEquipment()
    {
        return $this->belongsTo(EntityEquipment::class, 'entity_equipment_id');
    }
}