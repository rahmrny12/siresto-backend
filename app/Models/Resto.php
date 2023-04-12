<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resto extends Model
{
    use HasFactory;

    protected $table = 'resto';
    protected $guarded = ['id'];
    protected $with = ['kategori_bisnis'];

    public function produk()
    {
        return $this->hasMany(Produk::class);
    }

    public function meja()
    {
        return $this->hasMany(Meja::class);   
    }

    public function order()
    {
        return $this->hasMany(Order::class);   
    }

    public function promo()
    {
        return $this->hasMany(Promo::class);   
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function kategori_bisnis()
    {
        return $this->belongsTo(KategoriBisnis::class, 'id_kategori_bisnis');
    }
}
