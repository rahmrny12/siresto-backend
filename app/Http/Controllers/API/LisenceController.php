<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Models\Lisence;
use Illuminate\Http\Request;

class LisenceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $s = $request->s;
            $data = Lisence::select('id as value', 'lisence as label')->where('lisence', 'ILIKE', "%$s%")->get();

            if ($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(500, 'Failed');
        }
    }
}
