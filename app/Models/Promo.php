<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $table = 'promo';
    protected $guarded = ['id'];

    public function resto()
    {
        return $this->belongsTo(Resto::class, 'id_resto');
    }
}
