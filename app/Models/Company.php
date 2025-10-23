<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'tax_id',
        'address',
        'phone',
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
     * Una Compañía tiene una Suscripción.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
}
