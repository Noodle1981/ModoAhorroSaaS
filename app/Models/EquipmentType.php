<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'default_power_watts',
        'default_avg_daily_use_hours',
    ];

    public function equipmentCategory()
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }
}