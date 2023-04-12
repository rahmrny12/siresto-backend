<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'order_detail';
    protected $guarded = ['id'];
    protected $with = 'produk';

    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order');
    }

    public function produk()
    {
        return $this->hasOne(Produk::class, 'id', 'id_produk');
    }
}
