<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    use HasFactory;

    protected $table = 'meja';
    protected $guarded = ['id'];
    protected $with = ['resto', 'order'];

    public function resto()
    {
        return $this->belongsTo(Resto::class, 'id_resto');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order');   
    }
}
