<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\KategoriBisnis;

class KategoriBisnisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = KategoriBisnis::query();

        if ($s = request()->input('s')) {
            $query->where('kategori_bisnis', 'ILIKE', '%' . $s . '%');
        }

        if ($sort = request()->input('sort')) {
            $query->orderBy('id', $sort);
        }

        $perPage = request()->limit;
        $result = $query->paginate($perPage);
        $data = $result;

        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function kategori_bisnis_register()
    {
        $data = KategoriBisnis::all();

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
            $kategori_bisnis = KategoriBisnis::create([
                'kategori_bisnis' => $request->kategori_bisnis,
            ]);

            $data = KategoriBisnis::where('id', '=', $kategori_bisnis->id)->first();

            if ($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(500, 'Failed');
        }
    }

    public function insert_select(Request $request)
    {
        try {
            $kategori_bisnis = KategoriBisnis::create([
                'kategori_bisnis' => $request->kategori_bisnis,
            ]);

            $data = KategoriBisnis::select('id as value', 'kategori_bisnis as label')->where('id', '=', $kategori_bisnis->id)->get();

            if ($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(500, 'Failed');
        }
    }

    public function kategori_bisnis_all(Request $request)
    {
        try {
            $s = $request->s;
            $data = KategoriBisnis::select('id as value', 'kategori_bisnis as label')->where('kategori_bisnis', 'ILIKE', "%$s%")->get();

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
            $kategori_bisnis = KategoriBisnis::where('id', $id)->update([
                'kategori_bisnis' => $request->kategori_bisnis,
            ]);

            $data = KategoriBisnis::where('id', '=', $id)->first();

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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $kategori_bisnis = KategoriBisnis::findOrFail($id);
        $data = $kategori_bisnis->delete();

        if ($data) {
            return ApiFormatter::createApi(200, 'Success Destroy Data');
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }
}
