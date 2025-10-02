<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Subscription;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tax_id',
        'address',
        'phone',
        'province_id',
        'locality_id',
    ];

    /**
     * Una Compañía puede tener muchos Usuarios.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Una Compañía puede tener muchas Entidades.
     */
    public function entities()
    {
        return $this->hasMany(Entity::class);
    }

    /**
     * Una Compañía puede tener muchas Suscripciones.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Obtiene la suscripción activa de la compañía.
     */
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active');
    }
}