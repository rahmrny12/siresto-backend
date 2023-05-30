<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

use App\Models\KategoriProduk;
use App\Models\Resto;

class KategoriProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = KategoriProduk::query();
        $id_resto = auth()->user()->id_resto;

        $query->where('id_resto', $id_resto);
        if ($s = request()->input('s')) {
            $query->where('kategori_produk', 'ILIKE', '%' . $s . '%');
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

    // api untuk kategori produk di halaman menu frontend
    public function kategori_produk_menu()
    {
        $slug = request('resto');
        $resto = Resto::where('slug', $slug)->first();
        $data = KategoriProduk::where('id_resto', $resto->id)->orderByDesc('id')->get();

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
            $user = $request->user();
            $id_resto = $user->id_resto;

            $kategori_produk = KategoriProduk::create([
                'kategori_produk' => $request->kategori_produk,
                'id_resto' => $id_resto,
            ]);

            $data = KategoriProduk::where('id', '=', $kategori_produk->id)->get();

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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = KategoriProduk::findOrFail($id);

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $id_resto = $request->user()->id_resto;
            $kategori_produk = KategoriProduk::where('id', $id)->update([
                'kategori_produk' => $request->kategori_produk,
                'id_resto' => $id_resto,
            ]);

            $data = KategoriProduk::where('id', '=', $id)->get();

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
        $kategori_produk = KategoriProduk::findOrFail($id);
        $cek_jumlah = $kategori_produk->produk->count();

        if ($cek_jumlah < 0) {
            $kategori_produk->delete();
            return ApiFormatter::createApi(200, 'Success Destroy Data');
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function kategori_produk_all(Request $request)
    {
        $id_resto = auth()->user()->id_resto;
        $s = $request->s;
        $data = KategoriProduk::select('id as value', 'kategori_produk as label')->where('kategori_produk', 'ILIKE', "%$s%")->where('id_resto', $id_resto)->get();

        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }
}
