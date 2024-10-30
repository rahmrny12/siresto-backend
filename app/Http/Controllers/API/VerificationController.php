<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    public function verifyEmail(Request $request)
    {
        $email = $request->query('email');

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->email_verified_at = now();
            $user->save();

            return response()->json(['message' => 'Email Telah Diverifikasi']);
        } else {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }
    }
}
