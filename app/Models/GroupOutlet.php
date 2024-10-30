<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupOutlet extends Model
{
    use HasFactory;

    protected $table = 'group_outlet';

    protected $fillable = [
        'nama_outlet',
    ];

    // Relasi ke OutletResto (many-to-many ke Resto melalui OutletResto)
    public function restos()
    {
        return $this->hasManyThrough(
            Resto::class,
            OutletResto::class,
            'id_group_outlet', // Foreign key di tabel outlet_resto
            'id', // Foreign key di tabel resto
            'id', // Primary key di tabel group_outlet
            'id_resto' // Foreign key di tabel outlet_resto
        );
    }

    public function multiOutletUser()
    {
        return $this->belongsTo(User::class, 'id_multi_outlet');
    }
}
