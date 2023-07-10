<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Models\UserGuest;
use App\Models\VerifikasiOTP;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class SendOTPController extends Controller
{
    public function sendOTP(Request $request)
    {
        $result = Http::post('https://dev.api.awandigital.id/api/user-guest-available', [
            'email' => $request->email,
            'username' => $request->username,
        ])->json();

        if (!empty($result)) {
            return ApiFormatter::createApi(400, '');
        }

        $otpData = VerifikasiOTP::generateUniqueCode();

        $data = VerifikasiOTP::create([
            'kode_otp' => $otpData['code'],
            'expires_at' => $otpData['expires_at'],
            'no_whatsapp' => $request->phone_number
        ]);

        if (!empty($data)) {
            $message = "Kode OTP Anda adalah [" . $otpData['code'] . "]. Kode ini hanya berlaku selama 5 menit. Harap segera memasukkan kode yang diberikan untuk melanjutkan proses Registrasi Anda dengan aman. Terima kasih atas kepercayaan Anda pada siresto.awandigital.id, kami mengutamakan keamanan dan perlindungan data Anda.";

            $response = Http::post('https://whatsapp.kyoo.id/messages', [
                'phone_number' => $request->phone_number,
                'message' => $message,
            ]);

            $data = $response->json();

            return ApiFormatter::createApi(200, '', $data);
        } else {
            return ApiFormatter::createApi(400, '');
        }
    }

    public function cekOtp(Request $request)
    {
        $now = Carbon::now();

        $data = VerifikasiOTP::where('kode_otp', $request->kode_otp)->where('expires_at', '>=', $now)->where('digunakan', false)->first();

        $result = '';
        if (!empty($data)) {
            $result = Http::post('https://dev.api.awandigital.id/api/user-guest/store', [
                'nama' => $request->nama,
                'no_hp' => $request->no_whatsapp,
                'username' => $request->username,
                'email' => $request->email,
                'password' => $request->password,
            ])->json();

            if (!empty($result)) {
                UserGuest::create([
                    'nama' => $request->nama,
                    'no_hp' => $request->no_whatsapp,
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ]);
            }
        }

        if (!empty($result)) {
            $data = $data->update(['digunakan' => true]);
            return ApiFormatter::createApi(200, 'Nomor anda berhasil di verifikasi', $result);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }
}
