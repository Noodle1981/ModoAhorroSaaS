<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolarProductionReading extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = [
        'solar_installation_id',
        'reading_timestamp',
        'produced_kwh',
    ];
    
    protected $casts = [
        'reading_timestamp' => 'datetime',
    ];

    public function solarInstallation()
    {
        return $this->belongsTo(SolarInstallation::class);
    }
}
