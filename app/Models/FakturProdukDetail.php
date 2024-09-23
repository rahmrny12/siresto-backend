<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FakturProdukDetail extends Model
{
    use HasFactory;

    protected $table = 'faktur_detail';
    protected $primaryKey = 'id_faktur_detail';
    protected $guarded = ['id_faktur_detail'];
    protected $with = ['produk'];

    public function faktur()
    {
        return $this->belongsTo(FakturProduk::class, 'id_faktur', 'id_faktur');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id');
    }
}
