<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lisence extends Model
{
    use HasFactory;

    protected $table = 'lisence';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->hasMany(User::class);   
    }
}
