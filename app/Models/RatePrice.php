<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate_id',
        'price_energy_p1', 'price_energy_p2', 'price_energy_p3',
        'price_power_p1', 'price_power_p2',
        'valid_from', 'valid_to',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function rate()
    {
        return $this->belongsTo(Rate::class);
    }
}