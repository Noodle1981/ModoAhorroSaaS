<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'province_id',
        'locality_id',
        'name',
        'address',
        'city',
        'postal_code',
        'country',
        'entity_type',
        'area',
        'occupants',
    ];

    protected $casts = [
        'area' => 'float',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function locality()
    {
        return $this->belongsTo(Locality::class);
    }

    public function supplies()
    {
        return $this->hasMany(Supply::class);
    }

    public function entityEquipments()
    {
        return $this->hasMany(EntityEquipment::class);
    }
}