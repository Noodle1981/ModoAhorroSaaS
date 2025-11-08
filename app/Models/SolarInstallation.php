<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolarInstallation extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id', 'system_capacity_kwp', 'installation_date', 'inverter_brand', 'inverter_model',
        'panel_brand', 'panel_model', 'number_of_panels', 'orientation', 'tilt_degrees',
        'has_storage', 'storage_capacity_kwh',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'has_storage' => 'boolean',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function solarProductionReadings()
    {
        return $this->hasMany(SolarProductionReading::class);
    }
}