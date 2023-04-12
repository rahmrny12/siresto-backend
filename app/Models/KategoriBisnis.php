<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBisnis extends Model
{
    use HasFactory;
    
    protected $table = 'kategori_bisnis';
    protected $guarded = ['id'];

    public function resto()
    {
        return $this->hasMany(Resto::class);
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    // get formatted datetime string for email_verified_at

    public function getCreatedAttribute()
    {
        if ($this->email_verified_at) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at, 'UTC');
            return $date->setTimezone($this->timezone->name)->isoFormat('LLLL');
        } else {
            return null;
        }
    }

    public function getUpdatedAttribute()
    {
        if ($this->email_verified_at) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $this->updated_at, 'UTC');
            return $date->setTimezone($this->timezone->name)->isoFormat('LLLL');
        } else {
            return null;
        }
    }

    public function getDeletedAttribute()
    {
        if ($this->email_verified_at) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $this->deleted_at, 'UTC');
            return $date->setTimezone($this->timezone->name)->isoFormat('LLLL');
        } else {
            return null;
        }
    }
}
