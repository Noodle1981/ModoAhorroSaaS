<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'calculation_method',
        'description',
    ];

    public function equipmentTypes()
    {
        return $this->hasMany(EquipmentType::class);
    }
}