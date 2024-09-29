<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bahan extends Model
{
    use HasFactory;

    protected $table = 'bahan';
    protected $guarded = ['id'];

    public function produk()
    {
        return $this->belongsToMany(Produk::class, 'produk_bahan', 'id_bahan', 'id_produk');
    }
}
