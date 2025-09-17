<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    public $timestamps = false; // Le decimos a Laravel que no busque created_at/updated_at.
    protected $fillable = ['name'];

    public function localities()
    {
        return $this->hasMany(Locality::class);
    }
}