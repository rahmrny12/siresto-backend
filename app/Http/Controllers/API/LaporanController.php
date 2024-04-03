<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

use App\Models\Order;
use App\Models\Produk;
use App\Models\StokOpnameDetail;
use App\Models\StokOpname;
use App\Models\FakturProduk;
use App\Models\FakturProdukDetail;

class LaporanController extends Controller
{
    public function laporan_penjualan(Request $request)
    {
        $tanggal_awal = date('Y-m-d H:i:s', strtotime(request('tanggal-awal')));
        $tanggal_akhir = date('Y-m-d H:i:s', strtotime(request('tanggal-akhir')));

        $order = Order::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])
            ->where('id_resto', auth()->user()->id_resto);

        if (request('status-order') != null) {
            $order = $order->where('status_order', request('status-order'));
        }

        if ($order) {
            return ApiFormatter::createApi(200, 'php', $order->get());
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function laporan_pendapatan(Request $request)
    {
        $tanggal_awal = date('Y-m-d H:i:s', strtotime(request('tanggal-awal')));
        $tanggal_akhir = date('Y-m-d H:i:s', strtotime(request('tanggal-akhir')));

        $order = Order::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])
            ->where('id_resto', auth()->user()->id_resto)
            ->where('status_order', 'closed')
            ->get();

        $penjualan_bersih = 0;
        $hpp = 0;

        foreach ($order as $key => $value) {
            $penjualan_bersih += $value->nilai_transaksi;
            foreach ($value->order_detail as $key => $order_detail) {
                $hpp += $order_detail->produk->harga_awal * $order_detail->jumlah_beli;
            }
        }

        $laba_bersih = $penjualan_bersih - $hpp;

        return ApiFormatter::createApi(200, 'Success', ['penjualan_bersih' => $penjualan_bersih, 'hpp' => $hpp, 'laba_bersih' => $laba_bersih]);
    }

    public function stok(Request $request)
    {
        $data_stok = Produk::where('id_resto', auth()->user()->id_resto)
            ->select('*', DB::raw('stok * harga_awal as nilai_transaksi'));

        if ($data_stok = $data_stok->get()) {
            return ApiFormatter::createApi(200, 'Success', $data_stok);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function simpan_stok_opname(Request $request)
    {
        $user = $request->user();
        $id_resto = $user->id_resto;

        DB::beginTransaction();

        try {
            $stok_opname = StokOpname::create([
                'id_resto' => $id_resto,
                'id_pegawai' => $user->id,
            ]);

            $id_stok_opname = $stok_opname->id_stok_opname;

            $stok_opname_detail = [];

            foreach ($request->id_produk as $key => $id_produk) {
                $produk = Produk::find($id_produk);

                if (!$produk)
                {
                    throw new Exception('Product not found');
                }

                $stok_opname_detail[] = [
                    'id_stok_opname' => $id_stok_opname,
                    'id_produk' => $id_produk,
                    'stok_sistem' => $produk->stok,
                    'stok_fisik' => $request->stok_fisik[$key],
                    'selisih_stok' => $produk->stok - $request->stok_fisik[$key],
                ];


            }

            StokOpnameDetail::insert($stok_opname_detail);
            $data = StokOpname::find($id_stok_opname)->get();

            DB::commit();
            return ApiFormatter::createApi(200, 'Success', $data);
        } catch (Exception $e) {
            DB::rollback();
            return ApiFormatter::createApi(400, 'Failed. ' . $e->getMessage() . ". Line : " . $e->getLine());
        }
    }

    public function laporan_stok_opname(Request $request)
    {
        $tanggal_awal = date('Y-m-d H:i:s', strtotime(request('tanggal-awal')));
        $tanggal_akhir = date('Y-m-d H:i:s', strtotime(request('tanggal-akhir')));

        $stok_opname = StokOpname::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])
            ->where('id_resto', auth()->user()->id_resto);

        if ($stok_opname = $stok_opname->get()) {
            return ApiFormatter::createApi(200, 'Success', $stok_opname);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function mutasi_stok(Request $request)
    {
        $tanggal_awal = date('Y-m-d H:i:s', strtotime(request('tanggal-awal')));
        $tanggal_akhir = date('Y-m-d H:i:s', strtotime(request('tanggal-akhir')));

        // $data_stok = DB::table('tb_faktur')
        //     ->select('nama_supplier', 'tb_produk.id_produk', 'tb_produk.nama_produk', DB::raw('SUM(jumlah_stok) as jumlah_stok'), DB::raw('MAX(tb_faktur.created_at) as created_at'))
        //     ->join('tb_faktur_detail', 'tb_faktur.id_faktur', 'tb_faktur_detail.id_faktur')
        //     ->join('tb_produk', 'tb_produk.id_produk', 'tb_faktur_detail.id_produk')
        //     ->join('tb_supplier', 'tb_supplier.id_supplier', 'tb_faktur.id_supplier')
        //     ->join('users', 'users.id', 'tb_faktur.id_pegawai')
        //     ->groupBy('tb_produk.id_produk', 'tb_supplier.id_supplier')
        //     ->whereBetween('tb_faktur.created_at', [$tanggal_awal, $tanggal_akhir])
        //     ->where('tb_faktur.id_toko', auth()->user()->id_toko);

        $data_stok = FakturProdukDetail::join('faktur', 'faktur.id_faktur', 'faktur_detail.id_faktur')
            ->leftJoin('supplier', 'supplier.id_supplier', 'faktur.id_supplier')
            ->leftJoin('users', 'users.id', 'faktur.id_pegawai')
            ->whereBetween('faktur.created_at', [$tanggal_awal, $tanggal_akhir])
            ->where('supplier.id_resto', auth()->user()->id_resto);

        if ($data_stok = $data_stok->get()) {
            return ApiFormatter::createApi(200, 'Success', $data_stok);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }
}
