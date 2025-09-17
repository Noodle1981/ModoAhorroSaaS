<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = [
        'utility_company_id',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function utilityCompany()
    {
        return $this->belongsTo(UtilityCompany::class);
    }

    public function ratePrices()
    {
        return $this->hasMany(RatePrice::class);
    }
}