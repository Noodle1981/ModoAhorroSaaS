<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_type_id', 'name', 'description', 'recommended_frequency_days', 'recommended_season',
    ];

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }
}