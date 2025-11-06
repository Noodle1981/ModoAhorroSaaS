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
        'current_heater_type',
        'solar_heater_interest',
        'solar_heater_notes',
        'roof_area_m2',
        'roof_usable_area_m2',
        'roof_obstacles_percent',
        'has_shading',
        'shading_hours_daily',
        'shading_source',
        'roof_orientation',
        'roof_slope_degrees',
        'has_solar_panels',
        'installed_solar_kwp',
        'solar_panel_interest',
        'solar_panel_notes',
        'ground_area_m2',
        'ground_location',
        'ground_has_trees',
        'ground_shade_percent',
        'ground_notes',
    ];

    protected $casts = [
        'details' => 'array',
        'solar_heater_interest' => 'boolean',
        'has_shading' => 'boolean',
        'has_solar_panels' => 'boolean',
        'solar_panel_interest' => 'boolean',
        'ground_has_trees' => 'boolean',
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