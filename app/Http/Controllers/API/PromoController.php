<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

use App\Models\Promo;
use App\Models\Resto;
use Carbon\Carbon;

class PromoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Promo::query();
        $id_resto = request()->user()->id_resto;

        $query->where('id_resto', $id_resto);
        if ($s = request()->input('s')) {
            $query->where('judul_promo', 'ILIKE', "%$s%");
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

    public function promo_menu()
    {
        $slug = request('resto');
        $resto = Resto::where('slug', $slug)->first();

        $data = Promo::where('id_resto', $resto->id)
            ->whereDate('tanggal_awal_promo', '<=', now())
            ->whereMonth('tanggal_awal_promo', '<=', now())
            ->whereYear('tanggal_awal_promo', '<=', now())
            ->whereDate('tanggal_akhir_promo', '>=', now())
            ->whereMonth('tanggal_akhir_promo', '>=', now())
            ->whereYear('tanggal_akhir_promo', '>=', now())
            ->orderByDesc('id')->get();

        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function store(Request $request)
    {
        $gambar = '';
        if ($request->gambar) {
            $gambar = time() . '.' . explode('/', explode(':', substr($request->gambar, 0, strpos($request->gambar, ';')))[1])[1];

            \Image::make($request->gambar)->save(public_path('images/promo/') . $gambar);
        } else {
            $gambar = $request->gambar_lama;
        }

        $id_resto = request()->user()->id_resto;
        $tanggal_akhir_promo = date('Y-m-d H:i:s', strtotime($request->tanggal_awal_promo . '+' . $request->periode_promo . 'days'));

        try {
            $promo = Promo::create([
                'judul_promo' => $request->judul_promo,
                'gambar' => 'images/promo/' . $gambar,
                'tanggal_awal_promo' => $request->tanggal_awal_promo,
                'tanggal_akhir_promo' => $tanggal_akhir_promo,
                'periode_promo' => $request->periode_promo,
                'deskripsi_promo' => $request->deskripsi_promo,
                'status_promo' => $request->status_promo,
                'promo' => $request->promo,
                'id_resto' => $id_resto,
            ]);

            $data = Promo::where('id', $promo->id)->first();

            if ($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(500, $error->errorInfo[2]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $gambar = '';
            $promo = Promo::findOrFail($id);

            if ($request->gambar != null) {
                @unlink('images/promo/' . $promo->gambar);
                $gambar = time() . '.' . explode('/', explode(':', substr($request->gambar, 0, strpos($request->gambar, ';')))[1])[1];

                \Image::make($request->gambar)->save(public_path('images/promo/') . $gambar);

                $gambar = 'images/promo/' . $gambar;
            } else {
                $gambar = $promo->gambar;
            }

            $id_resto = request()->user()->id_resto;
            $tanggal_akhir_promo = date('Y-m-d H:i:s', strtotime($request->tanggal_awal_promo . '+' . $request->periode_promo . 'days'));

            $promo = Promo::where('id', $id)->update([
                'judul_promo' => $request->judul_promo,
                'gambar' => $gambar,
                'tanggal_awal_promo' => $request->tanggal_awal_promo,
                'tanggal_akhir_promo' => $tanggal_akhir_promo,
                'periode_promo' => $request->periode_promo,
                'deskripsi_promo' => $request->deskripsi_promo,
                'status_promo' => $request->status_promo,
                'promo' => $request->nominal_promo,
                'id_resto' => $id_resto,
            ]);

            $data = Promo::where('id', $id)->first();

            if ($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(500, $error);
        }
    }

    public function destroy($id)
    {
        $promo = Promo::findOrFail($id);
        @unlink('images/promo/' . $promo->gambar);
        $data = $promo->delete();

        if ($data) {
            return ApiFormatter::createApi(200, 'Success Destroy Data');
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }
}
