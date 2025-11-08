<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolarLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'entity_id', 'type', 'contact_name', 'contact_email', 'contact_phone', 'status', 'notes',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }
}
