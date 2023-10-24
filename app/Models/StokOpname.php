<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokOpname extends Model
{
    use HasFactory;
    protected $table = 'stok_opname';
    protected $primaryKey = 'id_stok_opname';
    protected $guarded = ['id_stok_opname'];
    protected $with = ['stok_opname_detail', 'pegawai'];

    public function resto()
    {
        return $this->belongsTo(Resto::class, 'id_resto');
    }

    public function pegawai()
    {
        return $this->belongsTo(User::class, 'id_pegawai');
    }

    public function stok_opname_detail()
    {
        return $this->hasMany(StokOpnameDetail::class, 'id_stok_opname', 'id_stok_opname');
    }
}
