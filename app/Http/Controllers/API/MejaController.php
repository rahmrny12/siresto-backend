<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;

use App\Models\Meja;

class MejaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Meja::query();
        $id_resto = request()->user()->id_resto;

        $query->where('id_resto', $id_resto);
        if ($s = request()->input('s')) {
            $query->where('no_meja', 'ILIKE', "%$s%");
        }

        if ($sort = request()->input('sort')) {
            $query->orderBy('id', $sort);
        }

        $perPage = request()->limit;
        $result = $query->paginate($perPage);
        $data = $result;
        
        if($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function no_meja(Request $request)
    {
        $data = Meja::where('uuid', $request->_u)->first();
        
        if($data) {
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
        $id_resto = request()->user()->id_resto;

        $meja = Meja::create([
            'no_meja' => $request->no_meja,
            'meja_tersedia' => 1,
            'id_resto' => $id_resto,
            'uuid' => Str::uuid(),
        ]);

        $data = Meja::where('id', '=', $meja->id)->get();

        if($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Meja  $meja
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Meja $meja)
    {
        try{
            $id_resto = request()->user()->id_resto;

            $action = Meja::where('id', $meja->id)->update([
                'no_meja' => $request->no_meja,
                'id_resto' => $id_resto
            ]);

            $data = Meja::where('id', '=', $meja->id)->get();

            if($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch(Exception $error) {
            return ApiFormatter::createApi(500, 'Failed');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Meja  $meja
     * @return \Illuminate\Http\Response
     */
    public function destroy(Meja $meja)
    {   
        $meja = Meja::findOrFail($meja->id);
        $data = $meja->delete();

        if($data) {
            return ApiFormatter::createApi(200, 'Success Destroy Data');
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function meja_all(Request $request)
    {
        try{
            $s = $request->s;
            $data = Meja::select('no_meja as value', 'no_meja as label')->where('no_meja', 'ILIKE', "%$s%")->get();

            if($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch(Exception $error) {
            return ApiFormatter::createApi(500, 'Failed');
        }
    }
}
