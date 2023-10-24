<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FakturProduk extends Model
{
    use HasFactory;

    protected $table = 'faktur';
    protected $primaryKey = 'id_faktur';
    protected $guarded = ['id_faktur'];
    protected $with = ['faktur_detail', 'pegawai', 'supplier'];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'id_toko');
    }

    public function faktur_detail()
    {
        return $this->hasMany(FakturProdukDetail::class, 'id_faktur', 'id_faktur');
    }

    public function pegawai()
    {
        return $this->belongsTo(User::class, 'id_pegawai', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }
}
