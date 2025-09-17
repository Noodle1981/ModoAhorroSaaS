<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyWeatherLog extends Model
{
    use HasFactory;

    public $timestamps = false; // Esta tabla no necesita timestamps de Laravel

    protected $fillable = [
        'locality_id',
        'date',
        'avg_temp_celsius',
        'min_temp_celsius',
        'max_temp_celsius',
        'heating_degree_days',
        'cooling_degree_days',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function locality()
    {
        return $this->belongsTo(Locality::class);
    }
}