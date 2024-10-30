<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukBahan extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'produk_bahan';

    // Kolom-kolom yang bisa diisi secara massal
    protected $fillable = [
        'id_produk',
        'id_bahan',
        'qty'
    ];

    // Relasi dengan model Produk
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }

    // Relasi dengan model Bahan
    public function bahan()
    {
        return $this->belongsTo(Bahan::class, 'id_bahan');
    }

    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;
}
