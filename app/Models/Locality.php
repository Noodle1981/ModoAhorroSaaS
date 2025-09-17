<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locality extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['province_id', 'name', 'postal_code'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function entities()
    {
        return $this->hasMany(Entity::class);
    }
}
