<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $with = ['level', 'lisence'];

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function level()
    {
        return $this->belongsTo(Level::class, 'id_level');
    }

    public function lisence()
    {
        return $this->belongsTo(Lisence::class, 'id_lisence');
    }

    public function user()
    {
        return $this->belongsTo(Resto::class, 'id_resto', 'id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function order()
    {
        return $this->hasMany(Order::class, 'id_staff', 'id');
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
