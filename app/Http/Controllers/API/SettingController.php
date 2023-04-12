<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Resto;
use App\Models\Setting;
use App\Models\KategoriBisnis;

class SettingController extends Controller
{

    public function index(Request $request)
    {
        $uid = $request->user()->id_resto;

        $setting = Setting::where('id_resto', $uid)->first();
        return ApiFormatter::createApi(200, 'Success', $setting);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $id_resto = $user->id_resto;
        $data = [
            'id_resto' => $id_resto,
            'status_pajak' => $request->status_pajak ?? 0,
            'pajak' => $request->pajak ?? 0,
            'status_charge_service' => $request->status_charge_service ?? 0,
            'charge_service' => $request->charge_service ?? 0,
            'alur_pembayaran_konsumen' => $request->alur_pembayaran_konsumen,
        ];

        $setting = Setting::updateOrCreate(['id_resto' => $id_resto], $data);

        if($setting) {
            return ApiFormatter::createApi(200, 'Success', $setting);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function profile_user(Request $request)
    {
        $user = auth()->user();

        if($user) {
            return ApiFormatter::createApi(200, 'Success', $user);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function profile(Request $request)
    {
        try{
            $image_name = $request->logo_lama;
            if($request->logo) {
                $image_name = time().'.' . explode('/', explode(':', substr($request->logo, 0, strpos($request->logo, ';')))[1])[1];
                @unlink('images/logo/'. $request->logo_lama); // hapus gambar

                \Image::make($request->logo)->save(public_path('images/logo/') . $image_name);
            } else {

            }
            
            $user = auth()->user();
            $kategori_bisnis = KategoriBisnis::findOrFail($request->id_kategori_bisnis);
            $upsertResto = Resto::updateOrCreate(
                ['id' => $user->id_resto],
                [
                    'nama_lengkap' => $request->nama_pemilik,
                    'nama_resto' => $request->nama_resto,
                    'kategori_bisnis' => $kategori_bisnis->kategori_bisnis,
                    'id_kategori_bisnis' => $request->id_kategori_bisnis,
                    'nomor_telepon' => $request->nomor_telepon_aktif,
                    'email' => $request->email,
                    'kota' => $request->kota,
                    'provinsi' => $request->provinsi,
                    'alamat_lengkap' => $request->alamat_lengkap,
                    'logo' => 'images/logo/' . $image_name,
                    'jam_buka' => $request->jam_buka,
                    'jam_tutup' => $request->jam_tutup,
                ]
            );

            $data = Resto::find($upsertResto->id);

            if($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch(Exception $error) {
            return ApiFormatter::createApi(500, $error->message);
        }
    }

    public function get_profile(Request $request)
    {
        $user = $request->user();
        $data = Resto::where('id', $user->id_resto)->first();

        return ApiFormatter::createApi(200, 'Success', $data);
    }

    public function reset_password(Request $request)
    {
        $user = $request->user();
        $id_user = $user->id;

        $user = User::where('id', $id_user)->update([
            'password' => Hash::make($request->password_baru),
            'password_asli' => $request->password_baru,
        ]);

        $data = User::where('id', $id_user)->first();

        if($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function ubah_profile(Request $request)
    {
        $gambar = '';
        $user = $request->user();
        if($request->gambar) {
            @unlink('images/user/'. $user->gambar);
            $gambar = time().'.' . explode('/', explode(':', substr($request->gambar, 0, strpos($request->gambar, ';')))[1])[1];
            \Image::make($request->gambar)->save(public_path('images/user/').$gambar);
            $gambar = 'images/user/' . $gambar;
        } else {
            $gambar = $user->gambar;
        }

        $id_resto = $user->id_resto;
        $data_user = User::where('id', $user->id)->update([
            'name' => $request->nama_lengkap,
            'email' => $request->email,
            'gambar' => $gambar,
            'username' => $request->username,
            'no_telepon' => $request->no_telepon,
            'alamat_lengkap' => $request->alamat_lengkap,
            'id_resto' => $id_resto,
        ]);

        $user = User::findOrFail($user->id);

        if($data_user) {
            return ApiFormatter::createApi(200, 'Success', $user);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }
}
