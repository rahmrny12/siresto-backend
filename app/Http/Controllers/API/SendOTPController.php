<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Models\VerifikasiOTP;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Twilio\Rest\Client;

class SendOTPController extends Controller
{
    public function sendOTP(Request $request)
    {
        $sid    = "ACae3146f72302b2b270a66f77e298ccdc";
        $token  = "231f3350e4f4824c4bf415e6cb2fe8b5";
        $twilio = new Client($sid, $token);

        $otpData = VerifikasiOTP::generateUniqueCode();

        // Simpan kode OTP yang dihasilkan ke dalam database
        $data = VerifikasiOTP::create([
            'kode_otp' => $otpData['code'],
            'expires_at' => $otpData['expires_at'],
            'no_whatsapp' => $request->phone_number
        ]);

        $message = "Kode OTP Anda adalah [" . $otpData['code'] . "]. Kode ini hanya berlaku selama 5 menit. Harap segera memasukkan kode yang diberikan untuk melanjutkan proses login Anda dengan aman. Terima kasih atas kepercayaan Anda pada siresto.awandigital.id, kami mengutamakan keamanan dan perlindungan data Anda.";

        $response = Http::post('http://127.0.0.1:4000/messages', [
            'phone_number' => $request->phone_number,
            'message' => $message,
        ]);

        $data = $response->json();

        return ApiFormatter::createApi(200, '', $data);
    }

    public function cekOtp(Request $request)
    {
        $now = Carbon::now();

        $data = VerifikasiOTP::where('kode_otp', $request->kode_otp)->where('expires_at', '>=', $now)->where('digunakan', false)->first();

        if (!empty($data)) {
            $data = $data->update(['digunakan' => true]);
            return ApiFormatter::createApi(200, 'Nomor anda berhasil di verifikasi', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }
}
