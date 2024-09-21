<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Produk;
use App\Models\Resto;
use App\Models\Meja;
use App\Models\FakturProduk;
use App\Models\FakturProdukDetail;
use App\Models\Supplier;
use App\Models\Bahan;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Produk::query();
        $id_resto = request()->user()->id_resto;

        $query->where('id_resto', $id_resto);
        if ($s = request()->input('s')) {
            $query->where(function ($query) {
                $s = request()->input('s');
                $query->where('nama_produk', 'LIKE', "%$s%")
                    ->orWhere('nomor_sku', 'LIKE', "%$s%");
            });
        }

        if ($id_kategori = request()->input('id_kategori')) {
            if ($id_kategori != 0) {
                $query->where('id_kategori_produk', '=', $id_kategori);
            }
        }

        if ($sort = request()->input('sort')) {
            $query->orderBy('id', $sort);
        }

        $perPage = request()->limit;

        $result = $query->paginate($perPage);
        $data['data'] = $result;
        $data['total_produk'] = $query->count();

        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function produk_home()
    {
        $query = Produk::query();

        if ($slug = request('resto')) {
            $resto = Resto::where('slug', $slug)->first();
            $query->where('id_resto', $resto->id);
        }

        if ($s = request()->input('s')) {
            $query->where('nama_produk', 'ILIKE', "%$s%")
                ->orWhere('nomor_sku', 'ILIKE', "%$s%");
        }

        if ($id_kategori = request()->input('id_kategori')) {
            if ($id_kategori != 0) {
                $query->where('id_kategori_produk', '=', $id_kategori);
            }
        }

        if ($sort = request()->input('sort')) {
            $query->orderBy('id', $sort);
        }

        $perPage = request()->limit;

        $data = $query->paginate($perPage);

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
        if ($request->gambar_produk) {
            $gambar = time() . '.' . explode('/', explode(':', substr($request->gambar_produk, 0, strpos($request->gambar_produk, ';')))[1])[1];

            \Image::make($request->gambar_produk)->save(public_path('images/produk/') . $gambar);
        } else {
            $gambar = $request->gambar_produk_lama;
        }

        $user = $request->user();
        $id_resto = $user->id_resto;

        try {
            $produk = Produk::create([
                'nama_produk' => $request->nama_produk,
                'id_kategori_produk' => $request->id_kategori_produk,
                'nomor_sku' => $request->nomor_sku,
                'gambar' => 'images/produk/' . $gambar,
                'harga_awal' => $request->harga_awal,
                'harga_jual' => $request->harga_jual,
                'diskon' => $request->status_diskon == 0 ? 0 : $request->diskon,
                'status_diskon' => $request->status_diskon,
                'id_resto' => $id_resto,
                'status_produk' => 1,
                'uuid' => Str::uuid(),
            ]);

            $data = Produk::where('id', '=', $produk->id)->get();

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
     * @param  \App\Models\Produk  $produk
     * @return \Illuminate\Http\Response
     */
    public function show(Produk $produk)
    {
        $data = Produk::findOrFail($id);

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
     * @param  \App\Models\Produk  $produk
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $gambar = '';
        $produk = Produk::findOrFail($id);

        if ($request->gambar_produk) {
            @unlink('images/produk/' . $produk->gambar); // hapus gambar

            $gambar = time() . '.' . explode('/', explode(':', substr($request->gambar_produk, 0, strpos($request->gambar_produk, ';')))[1])[1];
            \Image::make($request->gambar_produk)->save(public_path('images/produk/') . $gambar);
            $gambar = 'images/produk/' . $gambar;
        } else {
            $gambar = $produk->gambar;
        }

        $id_resto = $request->user()->id_resto;

        $produk = Produk::where('id', $id)->update([
            'nama_produk' => $request->nama_produk,
            'id_kategori_produk' => $request->id_kategori_produk,
            'nomor_sku' => $request->nomor_sku,
            'gambar' => $gambar,
            'harga_awal' => $request->harga_awal,
            'harga_jual' => $request->harga_jual,
            'diskon' => $request->status_diskon == 0 ? 0 : $request->diskon,
            'status_diskon' => $request->status_diskon,
            'id_resto' => $id_resto,
        ]);

        $data = Produk::where('id', '=', $id)->get();
        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Produk  $produk
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        @unlink('images/produk/' . $produk->gambar); // hapus gambar
        $data = $produk->delete();

        if ($data) {
            return ApiFormatter::createApi(200, 'Success Destroy Data');
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function ubah_status(Request $request, Produk $produk)
    {
        $status_produk = $request->input('status_produk');
        $action_change_status = Produk::where('id', $produk->id)->update(['status_produk' => $status_produk]);
        $data = Produk::findOrFail($produk->id);

        if ($produk) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function stok_masuk(Request $request)
    {
        $user = auth()->user();
        $id_resto = $user->id_resto;

        DB::beginTransaction();

        try {
            // Buat Faktur Produk
            $faktur = FakturProduk::create([
                'id_resto' => $id_resto,
                'id_pegawai' => $user->id,
                'id_supplier' => $request->id_supplier,
                'tanggal' => Carbon::now()->toDateString(),
                'waktu' => Carbon::now()->toTimeString(),
            ]);

            $id_faktur = $faktur->id_faktur;

            $faktur_detail = [];

            // Loop untuk bahan yang masuk
            foreach ($request->bahan as $key => $data) {
                $id_bahan = $data['id_bahan'];
                $jumlah_stok_bahan = $data['jumlah_stok']; // Jumlah stok bahan yang masuk

                // Cek produk-produk yang menggunakan bahan ini di tabel produk_bahan
                $produk_bahan_list = DB::table('produk_bahan')
                    ->where('id_bahan', $id_bahan)
                    ->get();

                foreach ($produk_bahan_list as $produk_bahan) {
                    $produk = Produk::find($produk_bahan->id_produk);

                    if (!$produk) {
                        throw new Exception('Product not found for id_bahan: ' . $id_bahan);
                    }

                    // Update stok produk berdasarkan bahan yang ditambahkan
                    $produk->update([
                        'stok' => $produk->stok + ($jumlah_stok_bahan * $produk_bahan->qty),
                    ]);

                    // Tambahkan ke detail faktur
                    $faktur_detail[] = [
                        'id_faktur' => $id_faktur,
                        'id_produk' => $produk_bahan->id_produk,
                        'jumlah_stok' => $jumlah_stok_bahan * $produk_bahan->qty, // Hitung stok produk yang diperbarui
                        'harga_beli' => $data['harga_beli'],
                        'harga_jual' => $data['harga_jual'],
                        'id_bahan' => $id_bahan,
                    ];
                }
            }

            // Insert faktur detail
            FakturProdukDetail::insert($faktur_detail);

            // Ambil data faktur yang baru dibuat
            $data = FakturProduk::with('details')->find($id_faktur);

            DB::commit();
            return ApiFormatter::createApi(200, 'Success', $data);
        } catch (Exception $e) {
            DB::rollback();
            return ApiFormatter::createApi(400, 'Failed. ' . $e->getMessage() . ". Line : " . $e->getLine());
        }
    }
}
