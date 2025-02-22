<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Compoships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'latitude',
        'longitude'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'forecast'
    ];

    public function forecast() : Attribute {
        return new Attribute(
            fn ($value) => (new \App\Services\Weather($this))->get()
        );
    }

    public function forecastHistory() {
        return $this->hasMany(Forecast::class, ['latitude', 'longitude'], ['latitude', 'longitude']);
    }

    public function scopePaginate($query, $perPage = 10, $page = null) {
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $paginator->appends(request()->query());

        return $paginator;
    }

}
