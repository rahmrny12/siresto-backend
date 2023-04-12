<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;

use App\Models\Order;
use App\Models\Produk;
use App\Models\Resto;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function owner(Request $request)
    {
        $user = auth()->user();

        $penjualan_hari_ini = Order::whereDate('created_at', date('Y-m-d'))->where('id_resto', $user->id_resto)->count(); // penjualan hari ini
        $jumlah_produk = Produk::where('id_resto', $user->id_resto)->count(); // jumlah produk
        $pendapatan_hari_ini = Order::whereDate('created_at', date('Y-m-d'))->where('id_resto', $user->id_resto)->sum('nilai_transaksi'); // pendapatan hari ini
        $total_staff = 5; // total staff

        $data_penjualan_per_bulan = []; //penjualan per bulan;
        for ($i=1; $i < 13; $i++) { 
            $bulan = sprintf("%02d", $i);
            $bulan_text = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $penjualan_per_bulan = Order::query()
                                ->select(\DB::raw("sum(nilai_transaksi) as total, extract(month from created_at)"))
                                ->whereMonth('created_at', $bulan)
                                ->where('id_resto', $user->id_resto)
                                ->whereYear('created_at', date('Y'))
                                ->groupByRaw("extract(month from created_at)")
                                ->first();

            $data_penjualan_per_bulan[] = [
                'label' => $bulan_text[$i - 1],
                'value' => empty($penjualan_per_bulan['total']) ? 0 : $penjualan_per_bulan['total']
            ];
        }

        $id_resto = $user->id_resto;
        $produk_populer = \DB::table('order_detail')
                            ->selectRaw('produk.nama_produk, produk.gambar, COUNT ( order_detail.* ) AS jumlah_terjual')
                            ->leftJoin('produk', 'order_detail.id_produk', '=', 'produk.id')
                            ->where('produk.id_resto', $id_resto)
                            ->groupBy('produk.id')
                            ->orderByDesc('jumlah_terjual')
                            ->limit(5)
                            ->get();

        $data = [
            'penjualan_hari_ini' => $penjualan_hari_ini,
            'jumlah_produk' => $jumlah_produk,
            'pendapatan_hari_ini' => $pendapatan_hari_ini,
            'total_staff' => $total_staff,
            'penjualan_per_bulan' => $data_penjualan_per_bulan,
            'produk_populer' => $produk_populer
        ];

        return ApiFormatter::createApi(200, '', $data);
    }

    public function superadmin(Request $request)
    {
        $jumlah_resto = Resto::count();
        $id_resto = 0;
        if($request->resto == null || $request->resto == '') {
            $resto = Resto::where('status_resto', '1')->first();
            $id_resto = $resto->id;
        } else {
            $nama_resto = rawurldecode($request->resto);
            $resto = Resto::where('nama_resto', $nama_resto)->first();
            $id_resto = $resto->id;
        }

        $jumlah_sku = Produk::where('id_resto', $id_resto)->count(); // jumlah sku
        $jumlah_transaksi = Order::where('id_resto', $id_resto)->whereDate('created_at', date('Y-m-d'))->count(); // jumlah transaksi

        $data_registrasi_resto_per_bulan = []; //registrasi resto per bulan;
        for ($i=1; $i < 13; $i++) {
            $bulan = sprintf("%02d", $i);
            $bulan_text = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $registrasi_resto_per_bulan = Resto::query()
                                ->select(\DB::raw("count(id) as total, extract(month from created_at)"))
                                ->whereMonth('created_at', $bulan)
                                ->whereYear('created_at', date('Y'))
                                ->groupByRaw("extract(month from created_at)")
                                ->first();

            $data_registrasi_resto_per_bulan[] = [
                'label' => $bulan_text[$i - 1],
                'value' => empty($registrasi_resto_per_bulan['total']) ? 0 : $registrasi_resto_per_bulan['total']
            ];
        }

        $data = [
            'jumlah_resto' => $jumlah_resto,
            'jumlah_sku' => $jumlah_sku,
            'jumlah_transaksi' => $jumlah_transaksi,
            'registrasi_resto_per_bulan' => $data_registrasi_resto_per_bulan,
            'select_resto' => ['label' => $resto->nama_resto, 'value' => $resto->id],
        ];

        return ApiFormatter::createApi(200, '', $data);
    }
}
