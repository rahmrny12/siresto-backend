<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;

use App\Models\Resto;
use App\Models\Setting;
use App\Models\Meja;
use App\Models\User;

class RestoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Resto::query();

        if ($s = request()->input('s')) {
            $query->where('nama_resto', 'ILIKE', "%$s%")
                ->orWhere('nama_pemilik', 'ILIKE', "%$s%");
        }

        if ($sort = request()->input('sort')) {
            $query->orderBy('id', $sort);
        }

        $perPage = request()->limit;
        $result = $query
            ->with(['users' => function ($query) {
                $query->where('id_level', 2);
            }])
            ->paginate($perPage);
        $data = $result;

        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function resto_row(Request $request)
    {
        $id_resto = request()->user()->id_resto;
        $data = Resto::where('id', $id_resto)->first();
        return ApiFormatter::createApi(200, 'Success', $data);
    }

    public function resto_all(Request $request)
    {
        try {
            $s = $request->s;
            $data = Resto::select('id as value', 'nama_resto as label')->where('nama_resto', 'ILIKE', "%$s%")->get();

            if ($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(500, 'Failed');
        }
    }

    public function setting_resto(Request $request)
    {
        $slug = $request->resto;
        $resto = Resto::where('slug', $slug)->first();
        $data = Setting::where('id_resto', $resto->id)->first();

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
        try {
            $resto = Resto::create([
                'nama_pemilik' => $request->nama_pemilik,
                'nama_resto' => $request->nama_resto,
                'id_kategori_bisnis' => $request->id_kategori_bisnis,
                'nomor_telepon' => $request->nomor_telepon,
                'kota' => $request->kota,
                'provinsi' => $request->provinsi,
                'status_resto' => 1,
                'slug' => Str::slug($request->nama_resto),
            ]);

            $data = Resto::where('id', '=', $resto->id)->get();

            if ($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(500, 'Failed');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $resto = Resto::where('id', $id)->update([
                'nama_pemilik' => $request->nama_pemilik,
                'nama_resto' => $request->nama_resto,
                'id_kategori_bisnis' => $request->id_kategori_bisnis,
                'nomor_telepon' => $request->nomor_telepon,
                'kota' => $request->kota,
                'provinsi' => $request->provinsi,
                'slug' => Str::slug($request->nama_resto),
                'jumlah_meja' => $request->jumlah_meja,
            ]);

            $data = Resto::where('id', '=', $id)->first();

            if ($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(500, 'Failed', $error);
        }
    }

    public function ubah_lisensi(Request $request, $id)
    {
        try {
            $resto = Resto::find($id);
            $user = User::where('id_resto', $id)->where('id_level', 2)->first();
            if (isset($request->masa_trial)) {
                $resto->update([
                    'masa_trial' => $resto->masa_trial + $request->masa_trial
                ]);
            }

            $user->update([
                'id_lisence' => $request->id_lisence
            ]);

            $data = Resto::where('id', '=', $id)->first();

            if ($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(500, 'Failed', $error);
        }
    }

    public function ubah_status(Request $request, Resto $resto)
    {
        $update = Resto::where('id', $resto->id)->update(['status_resto' => $request->status_resto]);
        $data = Resto::findOrFail($resto->id);

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
        $resto = Resto::findOrFail($id);
        $data = $resto->delete();

        if ($data) {
            return ApiFormatter::createApi(200, 'Success Destroy Data');
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }
}
