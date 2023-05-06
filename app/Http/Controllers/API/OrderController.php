<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Meja;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Order::query();
        $id_resto = request()->user()->id_resto;

        $query->where('id_resto', $id_resto);
        if ($s = request()->input('s')) {
            $query->where('no_transaksi', 'ILIKE', "%$s%");
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

    public function no_transaksi()
    {
        $q = DB::table('order')->select(DB::raw('MAX(RIGHT(no_transaksi, 4)) AS kd_max'))->whereRaw('DATE(created_at) = DATE(NOW())')->get();

        $kd = "";
        if($q->count() > 0){
            foreach($q as $k){
                $tmp = ((int) $k->kd_max) + 1;
                $kd = sprintf("%04s", $tmp);
            }
        }else{
            $kd = "0001";
        }
        return 'TRN'.date('dmy').$kd;
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $id_resto = $user->id_resto;
        $order = Order::create([
            'no_transaksi' => $this->no_transaksi(),
            'nilai_transaksi' => $request->subtotal,
            'bayar' => $request->pembayaran,
            'kembali' => $request->kembalian,
            'nama_pelanggan' => $request->nama_pelanggan,
            'id_resto' => $id_resto,
            'id_meja' => $request->id_meja,
            'diskon' => $request->diskon,
            'metode_pembayaran' => $request->metode_pembayaran,
            'status_order' => 'in_progress',
            'status_bayar' => 'already_paid',
            'uuid' => Str::uuid(),
        ]);

        $id_order = $order->id;

        $order_detail = [];
        $total_semua_laba = 0;
        foreach ($request->produk as $key => $produk) {
            $produk_db = DB::table('produk')->where('id', $produk['id'])->first();

            $harga_jual = $produk_db->harga_jual;
            $harga_awal = $produk_db->harga_awal;
            $diskon = $produk_db->diskon;
            $jumlah_produk = $produk['jumlah_produk'];

            $laba = ($harga_jual - $harga_awal) - $diskon;
            $total_harga_jual = ($harga_jual - $diskon) * $jumlah_produk;
            $total_laba = (($harga_jual - $diskon) - $harga_awal) * $jumlah_produk;
            $total_semua_laba += (int) $total_laba;

            $order_detail[] = [
                'id_order' => $id_order,
                'id_produk' => $produk['id'],
                'jumlah_beli' => $jumlah_produk,
                'harga_jual' => $harga_jual,
                'laba' => $laba,
                'total_harga_jual' => $total_harga_jual,
                'total_laba' => $total_laba,
                'diskon' => $diskon,
                'catatan' => $produk['catatan'],
            ];
        }

        Order::where('id', $id_order)->update(['nilai_laba' => $total_semua_laba]);
        DB::table('order_detail')->insert($order_detail);        
        $data = Order::where('id', '=', $order->id)->get();

        if($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function ubah_status_profile(Request $request, Order $order)
    {
        $action = Order::where('id', $order->id)->update(['status_order' => $request->status]);
        $data = Order::where('id', $order->id)->first();

        if($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function meja(Request $request)
    {
        try{
            $s = $request->s;
            $id_resto = request()->user()->id_resto;
            $data = Meja::select('id as value', 'no_meja as label')->where('no_meja', 'ILIKE', "%$s%")->where('id_resto', $id_resto)->get();

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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Order::findOrFail($id)->with(['order_detail']);

        if($data) {
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
    public function update(Request $request, Order $order)
    {
        $user = $request->user();
        $id_resto = $user->id_resto;

        $action = Order::where('id', $order->id)->update([
            'id_resto' => $id_resto,
            'diskon' => $request->diskon,
            'nilai_transaksi' => $request->subtotal,
            'metode_pembayaran' => $request->metode_pembayaran,
            'bayar' => $request->pembayaran,
            'kembali' => $request->kembalian,
            'status_bayar' => 'already_paid',
        ]);
        
        $data = Order::where('id', $order->id)->first();

        if($data) {
            return ApiFormatter::createApi(200, 'Success', $data);
        } else {
            return ApiFormatter::createApi(400, 'Failed');
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
        $order_detail = OrderDetail::where('id_order', $id)->delete();
        $order = Order::findOrFail($id);
        $data = $order->delete();

        if($data) {
            return ApiFormatter::createApi(200, 'Success Destroy Data');
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }
}
