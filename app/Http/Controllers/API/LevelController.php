<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

use App\Models\Level;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Level::query();

        if ($s = request()->input('s')) {
            $query->where('level', 'ILIKE', '%' . $s . '%');
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $level = Level::create([
                'level' => $request->level,
            ]);

            $data = Level::where('id', '=', $level->id)->get();

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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try{
            $level = Level::where('id', $id)->update([
                'level' => $request->level,
            ]);

            $data = Level::where('id', '=', $id)->get();

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $level = Level::findOrFail($id);
        $data = $level->delete();

        if($data) {
            return ApiFormatter::createApi(200, 'Success Destroy Data');
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }
}
