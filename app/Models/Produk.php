<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $guarded = ['id'];
    protected $with = ['kategori_produk'];

    public function kategori_produk()
    {
        return $this->belongsTo(KategoriProduk::class, 'id_kategori_produk', 'id');
    }

    public function resto()
    {
        return $this->belongsTo(Resto::class, 'id_resto');
    }

    public function order_detail()
    {
        return $this->belongsTo(Produk::class, 'id');
    }

    public function bahan()
    {
        return $this->belongsToMany(Bahan::class, 'produk_bahan', 'id_produk', 'id_bahan')
                        ->withPivot('qty');
    }
}
