<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissingMarketAlternative extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_type_id',
        'search_count',
    ];

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }
}
