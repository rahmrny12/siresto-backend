<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'order';
    protected $guarded = ['id'];
    protected $with = ['order_detail', 'meja'];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function resto()
    {
        return $this->belongsTo(Resto::class, 'id_resto');
    }

    public function order_detail()
    {
        return $this->hasMany(OrderDetail::class, 'id_order', 'id');
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'id_meja');
    }

    // get formatted datetime string for email_verified_at

    public function getCreatedAttribute()
    {
        if ($this->created_at) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at, 'UTC');
            return $date->setTimezone($this->timezone->name)->isoFormat('LLLL');
        } else {
            return null;
        }
    }

    public function getUpdatedAttribute()
    {
        if ($this->updated_at) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $this->updated_at, 'UTC');
            return $date->setTimezone($this->timezone->name)->isoFormat('LLLL');
        } else {
            return null;
        }
    }

    public function getDeletedAttribute()
    {
        if ($this->deleted_at) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $this->deleted_at, 'UTC');
            return $date->setTimezone($this->timezone->name)->isoFormat('LLLL');
        } else {
            return null;
        }
    }
}
