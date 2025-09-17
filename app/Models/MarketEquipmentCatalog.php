<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketEquipmentCatalog extends Model
{
    use HasFactory;

    protected $table = 'market_equipment_catalog';

    protected $fillable = [
        'equipment_type_id', 'brand', 'model_name', 'power_watts', 'efficiency_rating', 'average_price', 'purchase_link', 'is_active',
    ];

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }
}