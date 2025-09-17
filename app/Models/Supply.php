<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'supply_point_identifier',
        'type',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}