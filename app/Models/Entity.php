<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'locality_id',
        'name',
        'type',
        'address_street',
        'address_postal_code',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function locality()
    {
        return $this->belongsTo(Locality::class);
    }

    public function supplies()
    {
        return $this->hasMany(Supply::class);
    }

    public function equipments()
    {
        return $this->hasMany(EntityEquipment::class);
    }
}