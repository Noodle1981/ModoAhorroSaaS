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

    public function invoices()
    {
        // Las facturas están relacionadas a través de supplies -> contracts -> invoices
        $supplyIds = $this->supplies()->pluck('id');
        return Invoice::whereHas('contract', function($query) use ($supplyIds) {
            $query->whereIn('supply_id', $supplyIds);
        });
    }

    public function contracts()
    {
        return $this->hasManyThrough(
            Contract::class,
            Supply::class,
            'entity_id',
            'supply_id',
            'id',
            'id'
        );
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class);
    }
}