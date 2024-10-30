<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutletResto extends Model
{
    use HasFactory;

    protected $table = 'outlet_resto'; // Nama tabel
    protected $fillable = ['id_group_outlet', 'id_resto']; // Kolom yang bisa diisi

    public $timestamps = false; // Nonaktifkan timestamps
}
