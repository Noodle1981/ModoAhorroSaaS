<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'title', 'description', 'applies_to_category_id', 'trigger_rules',
    ];

    protected $casts = [
        'trigger_rules' => 'array',
    ];

    public function equipmentCategory()
    {
        return $this->belongsTo(EquipmentCategory::class, 'applies_to_category_id');
    }
}