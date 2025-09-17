<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumptionReading extends Model
{
    use HasFactory;

    // Esta tabla no usa los timestamps created_at/updated_at de Laravel, 
    // ya que su propia marca de tiempo es el campo principal.
    public $timestamps = false; 

    protected $fillable = [
        'supply_id',
        'reading_timestamp',
        'consumed_kwh',
        'injected_kwh',
        'source',
    ];

    protected $casts = [
        'reading_timestamp' => 'datetime',
    ];

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }
}