<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forecast extends Model
{
    use HasFactory, Compoships;

    protected $guarded = [];

    protected $casts = [
        'forecast' => 'array'
    ];

    public function user() {
        return $this->hasMany(User::class, ['latitude', 'longitude'], ['latitude', 'longitude']);
    }
}
