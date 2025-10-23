<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute; // Importar Attribute
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Un Usuario pertenece a una Compañía.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * ACCESOR: Obtiene la suscripción del usuario a través de su compañía.
     * Esto nos permite usar la sintaxis `$user->subscription` de forma segura.
     */
    protected function subscription(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->company?->subscription,
        );
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
}
