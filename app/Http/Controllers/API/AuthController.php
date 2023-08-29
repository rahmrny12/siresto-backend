<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Helper\ApiFormatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\Models\User;
use App\Models\Resto;
use App\Models\Setting;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required',
            'name' => 'required',
            'password' => 'required',
            'business_name' => 'required',
            'business_category' => 'required',
            'phone' => 'required',
            'province' => 'required',
            'city' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ada Kesalahan',
                'data' => $validator->errors(),
            ]);
        }

        $resto = [
            'nama_pemilik' => request('name'),
            'nama_resto' => request('business_name'),
            'id_kategori_bisnis' => 0,
            'nomor_telepon' => request('phone'),
            'kota' => request('city'),
            'provinsi' => request('province'),
            'email' => request('email'),
            'kategori_bisnis' => request('business_category'),
            'slug' => Str::slug(request('business_name')),
            'status_resto' => 1,
        ];

        $add_resto = Resto::create($resto);
        $id_resto = $add_resto->id;

        $user = [
            'name' => request('name'),
            'email' => request('email'),
            'password' => bcrypt(request('password')),
            'password_asli' => request('password'),
            'id_level' => 2,
            'id_resto' => $id_resto,
            'id_lisence' => 1,
            'no_telepon' => request('phone'),
            'alamat_lengkap' => '',
            'product_id' => request('product_id'),
        ];

        $data = [
            'id_resto' => $id_resto,
            'status_pajak' => 0,
            'pajak' => 0,
            'status_charge_service' => 0,
            'charge_service' => 0,
            'alur_pembayaran_konsumen' => 'bayar_langsung'
        ];

        Setting::create($data);

        $add_user = User::create($user);

        $success['token'] = $add_user->createToken('ewq98gegfNNn77j30u8PwL6sGFhj6aTXRcvrVFNq')->accessToken;
        $success['email'] = $add_user->email;
        $success['name'] = $add_user->name;

        return ApiFormatter::createApi(200, '', [
            'success' => true,
            'message' => 'Sukses Registrasi',
            'data' => $success
        ]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user->level->level != 'Superadmin') {
            $resto = Resto::find($user->id_resto);
            if ($resto->status_resto == 0) {
                return ApiFormatter::createApi(400, 'Email Atau Password Salah', [
                    'success' => false,
                    'data' => null
                ]);
            }
        }

        $fieldType = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $request->validate(['email' => 'required', 'password' => 'required']);

        if (!auth()->attempt([$fieldType => $request->email, 'password' => $request->password])) {
            return ApiFormatter::createApi(400, 'Email Atau Password Salah', [
                'success' => false,
                'data' => null
            ]);
        }

        $auth = auth()->user();

        if ($auth->level->level == 'Staff') {
            $owner = User::where('id_resto', $auth->id_resto)->where('id_level', 2)->first();
            $lisence = $owner->lisence->lisence;
        } else {
            $lisence = $auth->lisence->lisence;
        }

        $success['token'] = auth()->user()->createToken('ewq98gegfNNn77j30u8PwL6sGFhj6aTXRcvrVFNq')->accessToken;
        $success['name'] = $auth->name;
        $success['gambar'] = $auth->gambar;
        $success['username'] = $auth->username;
        $success['level'] = $auth->level->level;
        if ($auth->level->level != 'Superadmin') {
            $success['lisence'] = $lisence;
            $success['created_at'] = $resto->created_at;
            $success['masa_trial'] = $resto->masa_trial;
        }

        return ApiFormatter::createApi(200, 'Login Berhasil', $success);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return ApiFormatter::createApi(200, 'Login Berhasil', auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return ApiFormatter::createApi(200, 'logout Berhasil Dilakukan', auth()->user());
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return ApiFormatter::createApi(200, 'Refresh Token Berhasil', $this->respondWithToken(auth()->refresh()));
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
