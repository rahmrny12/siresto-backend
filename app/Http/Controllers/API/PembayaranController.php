<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\Order;
use App\Models\Meja;
use Exception;

class PembayaranController extends Controller
{
    public function no_transaksi()
    {
        $q = DB::table('order')->select(DB::raw('MAX(RIGHT(no_transaksi, 4)) AS kd_max'))->whereRaw('DATE(created_at) = DATE(NOW())')->get();

        $kd = "";
        if ($q->count() > 0) {
            foreach ($q as $k) {
                $tmp = ((int) $k->kd_max) + 1;
                $kd = sprintf("%04s", $tmp);
            }
        } else {
            $kd = "0001";
        }
        return 'TRN' . date('dmy') . $kd;
    }

    public function store(Request $request)
    {
        $meja = Meja::where('uuid', $request->code)->first();

        $order = Order::create([
            'no_transaksi' => $this->no_transaksi(),
            'nilai_transaksi' => request('nilai_transaksi'),
            'nilai_laba' => request('nilai_laba'),
            'bayar' => request('jumlah_bayar'),
            'kembali' => request('kembalian'),
            'metode_pembayaran' => request('metode_pembayaran'),
            'nama_customer' => request('nama_customer'),
            'code_user' => request('code_user'),
            'diskon' => request('diskon'),
            'id_resto' => $meja->id_resto,
            'id_meja' => $meja->id,
            'status_bayar' => 'not_paid',
            'uuid' => Str::uuid(),
            'pajak' => request('pajak'),
            'service_charge' => request('service_charge'),
            'status_order' => 'open'
        ]);

        $data_order_detail = [];
        foreach (request('produk') as $key => $val) {
            $laba = $val['laba'];
            $diskon = $val['diskon'];
            $harga_jual = $val['harga_jual'];
            $jumlah_beli = $val['jumlah'];

            $data_order_detail[] = array(
                'id_order' => $order->id,
                'id_produk' => $val['id'],
                'diskon' => $diskon,
                'jumlah_beli' => $jumlah_beli,
                'harga_jual' => $harga_jual,
                'laba' => $laba,
                'total_harga_jual' => $harga_jual * $jumlah_beli,
                'total_laba' => $harga_jual * $jumlah_beli,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            );
        }

        DB::table('order_detail')->insert($data_order_detail);
        $data = Order::where('id', $order->id)->first();

        if ($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }
}
