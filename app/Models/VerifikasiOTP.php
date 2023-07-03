<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifikasiOTP extends Model
{
    use HasFactory;

    protected $table = 'verifikasi_otp';
    protected $guarded = ['id'];

    public static function generateUniqueCode()
    {
        $code = mt_rand(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        while (VerifikasiOTP::where('kode_otp', $code)->exists()) {
            $code = mt_rand(100000, 999999);
        }

        return ['code' => $code, 'expires_at' => $expiresAt];
    }
}
