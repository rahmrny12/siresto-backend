<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

use App\Models\Order;

class LaporanController extends Controller
{
    public function laporan_penjualan(Request $request)
    {
        $tanggal_awal = date('Y-m-d H:i:s', strtotime(request('tanggal-awal')));
        $tanggal_akhir = date('Y-m-d H:i:s', strtotime(request('tanggal-akhir')));

        $order = Order::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])
            ->where('id_resto', auth()->user()->id_resto)
            ->get();

        if ($order) {
            return ApiFormatter::createApi(200, 'php', $order);
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
}
