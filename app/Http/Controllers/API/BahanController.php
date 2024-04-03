<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Bahan;
use App\Models\Resto;

class BahanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Bahan::query();
        $id_resto = request()->user()->id_resto;

        $query->where('id_resto', $id_resto);
        if ($s = request()->input('s')) {
            $query->where(function ($query) {
                $s = request()->input('s');
                $query->where('nama_bahan', 'LIKE', "%$s%");
            });
        }

        if ($sort = request()->input('sort')) {
            $query->orderBy('id', $sort);
        }

        $perPage = request()->limit;

        $result = $query->paginate($perPage);
        $data['data'] = $result;

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
        $user = $request->user();
        $id_resto = $user->id_resto;

        try {
            $bahan = Bahan::create([
                'nama_bahan' => $request->nama_bahan,
                'id_resto' => $id_resto,
            ]);

            $data = Bahan::where('id', '=', $bahan->id)->get();

            if ($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(500, $error->errorInfo[2]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bahan  $bahan
     * @return \Illuminate\Http\Response
     */
    public function show(Bahan $bahan)
    {
        $data = Bahan::findOrFail($id);

        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bahan  $bahan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $gambar = '';
        $bahan = Bahan::findOrFail($id);

        $id_resto = $request->user()->id_resto;

        $bahan = Bahan::where('id', $id)->update([
            'nama_bahan' => $request->nama_bahan,
            'id_resto' => $id_resto,
        ]);

        $data = Bahan::where('id', '=', $id)->get();
        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bahan  $bahan
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $bahan = Bahan::findOrFail($id);
        $data = $bahan->delete();

        if ($data) {
            return ApiFormatter::createApi(200, 'Success Destroy Data');
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }
}
