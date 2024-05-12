<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

use App\Models\User;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = User::query()->whereNotIn('id_level', [1, 2]);

        if ($s = $request->input('s')) {
            $query->where('name', 'ILIKE', "%$s%");
        }

        $query->where('id_resto', $request->user()->id_resto);

        if ($sort = $request->input('sort')) {
            $query->orderBy('id', $sort);
        }

        $perPage = $request->limit;
        $result = $query->paginate($perPage);
        $data = $result;

        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $gambar = '';
        if ($request->gambar) {
            $gambar = time() . '.' . explode('/', explode(':', substr($request->gambar, 0, strpos($request->gambar, ';')))[1])[1];
            \Image::make($request->gambar)->save(public_path('images/user/') . $gambar);
            $gambar = 'images/user/' . $gambar;
        }

        $id_resto = $request->user()->id_resto;
        $staff = User::create([
            'name' => $request->nama_lengkap,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'gambar' => $gambar,
            'username' => $request->username,
            'no_telepon' => $request->no_telepon,
            'alamat_lengkap' => $request->alamat_lengkap,
            'id_resto' => $id_resto,
            'id_level' => 3,
            'id_lisence' => 0,
        ]);

        $data = User::where('id', $staff->id)->first();

        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $gambar = null;
        $user = User::findOrFail($id);
        if ($request->gambar) {
            @unlink('images/user/' . $user->gambar);
            $gambar = time() . '.' . explode('/', explode(':', substr($request->gambar, 0, strpos($request->gambar, ';')))[1])[1];
            \Image::make($request->gambar)->save(public_path('images/user/') . $gambar);
            $gambar = 'images/user/' . $gambar;
        } else {
            $user = User::where('id', $id)->first();
            $gambar = $user->gambar;
        }

        $id_resto = $request->user()->id_resto;
        $staff = User::where('id', $id)->update([
            'name' => $request->nama_lengkap,
            'email' => $request->email,
            'gambar' => $gambar,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'no_telepon' => $request->no_telepon,
            'alamat_lengkap' => $request->alamat_lengkap,
            'id_resto' => $id_resto,
            'id_level' => 3,
            'id_lisence' => 0,
        ]);

        $data = User::where('id', $id)->first();

        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function show($id)
    {
        $data = User::where('id', '=', $id)->first();
        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function reset_password(Request $request)
    {
        $user = $request->user;
        $id_staff = $request->id_staff;

        $staff = User::where('id', $id_staff)->update([
            'password' => bcrypt($request->password_baru),
        ]);

        $data = User::where('id', $id_staff)->first();

        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function ubah_profile(Request $request, $id)
    {
        $staff = User::where('id', '=', $id)->first();

        $gambar = null;
        if ($request->gambar) {
            $gambar = time() . '.' . explode('/', explode(':', substr($request->gambar, 0, strpos($request->gambar, ';')))[1])[1];

            \Image::make($request->gambar)->save(public_path('images/user/') . $gambar);
        } else {
            $gambar = $staff->gambar;
        }

        $staff = User::where('id', $id)->update([
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'no_telepon' => $request->no_telepon,
            'alamat' => $request->alamat,
            'kabupaten' => $request->kabupaten,
            'kecamatan' => $request->kecamatan,
            'kelurahan' => $request->kelurahan,
            'kode_pos' => $request->kode_pos,
            'gambar' => $gambar
        ]);

        $data = User::where('id', '=', $id)->first();

        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        $staff = User::findOrFail($id);
        @unlink('images/user/' . $staff->gambar);
        $data = $staff->delete();

        if ($data) {
            return ApiFormatter::createApi(200, 'Success Destroy Data');
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }
}
