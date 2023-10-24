<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokOpnameDetail extends Model
{
    use HasFactory;

    protected $table = 'stok_opname_detail';
    protected $primaryKey = 'id_stok_opname_detail';
    protected $guarded = ['id_stok_opname_detail'];
    protected $with = 'produk';

    public function stok_opname()
    {
        return $this->belongsTo(StokOpname::class, 'id_stok_opname', 'id_stok_opname');
    }

    public function produk()
    {
        return $this->hasOne(Produk::class, 'id', 'id_produk');
    }
}
