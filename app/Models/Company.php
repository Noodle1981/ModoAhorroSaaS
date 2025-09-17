<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

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
}