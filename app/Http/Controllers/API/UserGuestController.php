<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Models\UserGuest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class UserGuestController extends Controller
{
    public function index()
    {
        $perPage = request('per_page', 15);

        $data = UserGuest::paginate($perPage);

        return ApiFormatter::createApi(200, $data);
    }

    public function login_menu(Request $request)
    {
        $data = UserGuest::where('email', $request->email)->first();

        if (!empty($data) && Hash::check($request->password, $data->password)) {
            return ApiFormatter::createApi(200, 'success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Email atau password salah');
        }
    }

    public function update(Request $request)
    {
        $userGuest = UserGuest::where('email', $request->email)->first();

        $data = $userGuest->update([
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
            'alamat_1' => $request->alamat,
        ]);

        if ($data) {
            return ApiFormatter::createApi(200, 'success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function update_alamat(Request $request)
    {
        $userGuest = UserGuest::where('email', $request->email)->first();

        $data = $userGuest->update([
            'alamat_1' => $request->alamat,
        ]);

        if ($data) {
            return ApiFormatter::createApi(200, 'success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function forgot_password(Request $request)
    {
        $userGuest = UserGuest::where('no_hp', $request->phone_number)->first();

        if (!$userGuest) {
            return  ApiFormatter::createApi(404, 'Not Found');
        } else {
            $encrypt = Crypt::encrypt($userGuest->id);
            $url = $request->link . "?id=" . urlencode($encrypt) . '&email=' . $userGuest->email;
            $message = "Berikut adalah link pemulihan akun anda : \n" . $url;
            $response = Http::post('https://whatsapp.kyoo.id/messages', [
                'phone_number' => $request->phone_number,
                'message' => $message,
            ]);

            $data = $userGuest;

            return ApiFormatter::createApi(200, '', $data);
        }
    }

    public function reset_password(Request $request)
    {
        $userGuest = UserGuest::where('email', $request->email)->first();

        $userGuest->update([
            'password' => bcrypt($request->password)
        ]);

        $data = $userGuest;

        if (!$userGuest) {
            return  ApiFormatter::createApi(404, 'Not Found');
        } else {
            return ApiFormatter::createApi(200, '', $data);
        }
    }
}
