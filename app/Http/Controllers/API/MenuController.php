<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\Pelanggan;
use App\Models\Resto;
use App\Models\Meja;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Produk;
use App\Models\Setting;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function cek_pelanggan(Request $request)
    {
        $no_telepon = $request->telepon;
        $query_pelanggan = Pelanggan::where('no_telepon', $no_telepon);
        if ($query_pelanggan->count() > 0) {
            $pelanggan = $query_pelanggan->first();
            return ApiFormatter::createApi(200, 'Success', $pelanggan);
        }

        $pelanggan = null;
        return ApiFormatter::createApi(200, 'Data Kosong', $pelanggan);
    }

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

    public function simpan_order_pelanggan(Request $request)
    {
        $no_transaksi = $request->no_transaksi;
        $resto = Resto::where('slug', $request->branch)->firstOrFail();
        $pelanggan = Pelanggan::create(['no_telepon' => $request->no_telepon, 'nama_pelanggan' => $request->nama_pelanggan, 'id_resto' => $resto->id]);
        $meja = Meja::where('no_meja', $request->meja)->where('id_resto', $resto->id)->first();
        $cek_order = Order::where('no_transaksi', $no_transaksi)->first();

        if ($no_transaksi === 0 || $cek_order->status_order == "closed") {
            $order = Order::create([
                'no_transaksi' => $this->no_transaksi(),
                'nilai_transaksi' => $request->total,
                'nilai_laba' => $request->nilai_laba,
                'source' => $request->source,
                'bayar' => 0,
                'kembali' => 0,
                'nama_pelanggan' => $request->nama_pelanggan,
                'id_pelanggan' => $pelanggan->id,
                'id_resto' => $resto->id,
                'id_meja' => $meja->id,
                'diskon' => $request->diskon,
                'metode_pembayaran' => '',
                'status_order' => 'open',
                'status_bayar' => 'not_paid',
                'pajak' => $request->pajak,
                'service_charge' => $request->service_charge,
                'uuid' => Str::uuid(),
            ]);

            $id_order = $order->id;

            $order_detail = [];
            foreach ($request->produk as $key => $produk) {
                $produk_db = Produk::where('id', $produk['id'])->first();

                $laba = ($produk_db->harga_jual - $produk_db->harga_awal) - $produk_db->diskon;
                $total_harga_jual = ($produk_db->harga_jual - $produk_db->diskon) * $produk['jumlah'];
                $total_laba = (($produk_db->harga_jual - $produk_db->diskon) - $produk_db->harga_awal) * $produk['jumlah'];

                $order_detail[] = [
                    'id_order' => $id_order,
                    'id_produk' => $produk['id'],
                    'jumlah_beli' => $produk['jumlah'],
                    'harga_jual' => $produk_db->harga_jual,
                    'laba' => $laba,
                    'total_harga_jual' => $total_harga_jual,
                    'total_laba' => $total_laba,
                    'diskon' => $produk_db->diskon,
                    'catatan' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }

            DB::table('order_detail')->insert($order_detail);
            $data = Order::where('id', '=', $order->id)->first();

            if ($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } else {
            $order_sebelumnya = Order::where('id_resto', $resto->id)->where('no_transaksi', $no_transaksi)->first();
            $order_update = Order::where('id_resto', $resto->id)
                ->where('no_transaksi', $no_transaksi)
                ->update([
                    'nilai_transaksi' => (int) $order_sebelumnya->nilai_transaksi + (int) $request->total,
                    'nilai_laba' => (int) $order_sebelumnya->nilai_laba + (int) $request->nilai_laba,
                    'diskon' => (int) $order_sebelumnya->diskon +  (int) $request->diskon,
                ]);

            $id_order = $order_sebelumnya->id;

            $order_detail = [];
            foreach ($request->produk as $key => $produk) {
                $produk_db = Produk::where('id', $produk['id'])->first();
                $produk_order_detail = OrderDetail::where('id_order', $id_order)->where('id_produk', $produk['id']);
                $data_produk_order_detail = $produk_order_detail->first();
                if ($produk_order_detail->count() == 0) {

                    $laba = ($produk_db->harga_jual - $produk_db->harga_awal) - $produk_db->diskon;
                    $total_harga_jual = ($produk_db->harga_jual - $produk_db->diskon) * $produk['jumlah'];
                    $total_laba = (($produk_db->harga_jual - $produk_db->diskon) - $produk_db->harga_awal) * $produk['jumlah'];

                    $order_detail[] = [
                        'id_order' => $id_order,
                        'id_produk' => $produk['id'],
                        'jumlah_beli' => $produk['jumlah'],
                        'harga_jual' => $produk_db->harga_jual,
                        'laba' => $laba,
                        'total_harga_jual' => $total_harga_jual,
                        'total_laba' => $total_laba,
                        'diskon' => $produk_db->diskon,
                        'catatan' => '',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                } else {

                    $laba = ($produk_db->harga_jual - $produk_db->harga_awal) - $produk_db->diskon;
                    $total_harga_jual = ($produk_db->harga_jual - $produk_db->diskon) * ($produk['jumlah'] + $data_produk_order_detail->jumlah_beli);
                    $total_laba = (($produk_db->harga_jual - $produk_db->diskon) - $produk_db->harga_awal) * ($produk['jumlah'] + $data_produk_order_detail->jumlah_beli);

                    OrderDetail::where('id_order', $id_order)
                        ->where('id_produk', $produk['id'])
                        ->update([
                            'jumlah_beli' => ($produk['jumlah'] + $data_produk_order_detail->jumlah_beli),
                            'total_harga_jual' => $total_harga_jual,
                            'total_laba' => $total_laba,
                            'catatan' => '',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                }
            }

            DB::table('order_detail')->insert($order_detail);
            $data = Order::where('id', '=', $id_order)->first();

            if ($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        }
    }

    public function cari_order_transaksi(Request $request)
    {
        $resto = Resto::where('slug', $request->branch)->first();
        $noTransaksiArr = explode(",", $request->no_transaksi);
        $order = Order::whereIn('no_transaksi', $noTransaksiArr)->where('id_resto', $resto->id)->orderByDesc('id')->get();
        $setting = Setting::first();

        if ($order) {
            return ApiFormatter::createApi(200, 'Success', ['order' => $order, 'setting' => $setting]);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }
}
