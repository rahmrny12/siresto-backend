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
use App\Models\User;
use App\Models\GroupOutlet;
use App\Models\OutletResto;

class LaporanController extends Controller
{
    public function laporan_penjualan(Request $request)
    {
        $tanggal_awal = date('Y-m-d H:i:s', strtotime(request('tanggal-awal')));
        $tanggal_akhir = date('Y-m-d H:i:s', strtotime(request('tanggal-akhir')));
        $staff = request('staff-name');

        $order = Order::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])
            ->where('id_resto', auth()->user()->id_resto);

        if($staff) {
            $staff = User::where('name', $staff)->first();
            $order = $order->where('id_staff', $staff->id);
        }

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

        $penjualan_kotor = 0;
        $total_diskon = 0;
        $penjualan_bersih = 0;
        $laba_kotor = 0;
        $hpp = 0;
        $pajak = 0;
        $service_charge = 0;

        foreach ($order as $key => $value) {
            $laba_kotor += $value->nilai_laba;
            $total_diskon += $value->diskon;
            $penjualan_bersih += $value->nilai_transaksi;
            $penjualan_kotor += $value->nilai_transaksi + $value->diskon;

            $pajak += $value->pajak;
            $service_charge += $value->service_charge;

            foreach ($value->order_detail as $key => $order_detail) {
                $hpp += optional($order_detail->produk)->harga_awal * $order_detail->jumlah_beli;
            }

        }

        $laba_bersih = $penjualan_bersih - $hpp;

        return ApiFormatter::createApi(200, 'Success', ['penjualan_bersih' => $penjualan_bersih, 'hpp' => $hpp, 'laba_bersih' => $laba_bersih, 'penjualan_kotor' => $penjualan_kotor, 'total_diskon' => $total_diskon, 'laba_kotor' => $laba_kotor, 'pajak' => $pajak, 'service_charge' => $service_charge]);
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

    public function laporan_penjualan_group(Request $request)
    {
        $request->validate([
            'tanggal-awal' => 'required|date_format:d-m-Y',
            'tanggal-akhir' => 'required|date_format:d-m-Y',
        ]);

        $tanggal_awal = date('Y-m-d 00:00:00', strtotime($request->input('tanggal-awal')));
        $tanggal_akhir = date('Y-m-d 23:59:59', strtotime($request->input('tanggal-akhir')));

        $status_order = $request->input('status-order');
        $id_resto = $request->input('id-resto');

        $user = auth()->user();

        $id_group_outlet = GroupOutlet::where('id_multi_outlet', $user->id)
            ->pluck('id')
            ->first();

        if (!$id_group_outlet) {
            return ApiFormatter::createApi(404, 'Group outlet tidak ditemukan.');
        }

        $id_resto_list = OutletResto::where('id_group_outlet', $id_group_outlet)
            ->pluck('id_resto');

        $order = Order::whereIn('id_resto', $id_resto_list)
            ->whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])
            ->with('resto:id,nama_resto');

        if (!is_null($status_order)) {
            $order->where('status_order', $status_order);
        }

        if (!is_null($id_resto)) {
            $order->where('id_resto', $id_resto);
        }

        $orders = $order->orderBy('created_at', 'desc')->get();

        if ($orders->isEmpty()) {
            return ApiFormatter::createApi(404, 'Tidak ada data penjualan untuk periode yang dipilih.');
        }

        $orders->transform(function ($order) {
            $order->nama_resto = $order->resto ? $order->resto->nama_resto : 'Nama Restoran Tidak Ditemukan';
            return $order;
        });

        return ApiFormatter::createApi(200, 'Data ditemukan', $orders);
    }

    public function get_resto_group(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return ApiFormatter::createApi(401, 'Unauthorized: User tidak ditemukan.');
        }

        $id_group_outlet = GroupOutlet::where('id_multi_outlet', $user->id)
            ->pluck('id')
            ->first();

        if (!$id_group_outlet) {
            return ApiFormatter::createApi(404, 'Group outlet tidak ditemukan.');
        }

        $restoran = OutletResto::where('id_group_outlet', $id_group_outlet)
            ->with('resto:id,nama_resto')
            ->get();

        $restoran->transform(function ($outlet) {
            $outlet->nama_resto = $outlet->resto ? $outlet->resto->nama_resto : 'Nama Restoran Tidak Ditemukan';
            unset($outlet->resto);
            return $outlet;
        });

        if ($restoran->isEmpty()) {
            return ApiFormatter::createApi(404, 'Data restoran tidak ditemukan.');
        }

        return ApiFormatter::createApi(200, 'Data restoran ditemukan.', $restoran);
    }

    public function laporan_stok_group(Request $request)
    {
        $id_group_outlet = GroupOutlet::where('id_multi_outlet', auth()->user()->id)
            ->pluck('id')
            ->first();

        if (!$id_group_outlet) {
            return ApiFormatter::createApi(404, 'Group outlet tidak ditemukan.');
        }

        $id_resto_list = OutletResto::where('id_group_outlet', $id_group_outlet)
            ->pluck('id_resto');

        if ($id_resto_list->isEmpty()) {
            return ApiFormatter::createApi(404, 'Tidak ada restoran yang terkait dengan grup outlet ini.');
        }

        $id_resto = $request->query('id_resto');
        $query = Produk::with('kategori_produk')
            ->whereIn('id_resto', $id_resto_list);

        if ($id_resto) {
            $query->where('id_resto', $id_resto);
        }

        $data_stok = $query->select(
            'id_resto',
            'nomor_sku',
            'nama_produk',
            'stok',
            'harga_awal',
            'harga_jual',
            'id_kategori_produk',
            DB::raw('stok * harga_awal as nilai_transaksi')
        )
        ->orderBy('created_at', 'desc')
        ->get();

        if ($data_stok->isEmpty()) {
            return ApiFormatter::createApi(404, 'Tidak ada data stok ditemukan.');
        }

        $formatted_data = $data_stok->map(function ($item) {
            return [
                'id_resto' => $item->id_resto,
                "nomor_sku" => $item->nomor_sku,
                'nama_produk' => $item->nama_produk,
                'stok' => $item->stok,
                'harga_awal' => $item->harga_awal,
                'harga_jual' => $item->harga_jual,
                'kategori_produk' => $item->kategori_produk,
                'nilai_transaksi' => $item->nilai_transaksi,
            ];
        });

        return ApiFormatter::createApi(200, 'Laporan Stok Group berhasil', $formatted_data);
    }

    public function mutasi_stok_group(Request $request)
    {
        $tanggal_awal = date('Y-m-d H:i:s', strtotime($request->input('tanggal-awal')));
        $tanggal_akhir = date('Y-m-d H:i:s', strtotime($request->input('tanggal-akhir')));

        // Ambil group outlet pengguna
        $id_group_outlet = GroupOutlet::where('id_multi_outlet', auth()->user()->id)
            ->pluck('id')
            ->first();

        if (!$id_group_outlet) {
            return ApiFormatter::createApi(404, 'Group outlet tidak ditemukan.');
        }

        // Ambil daftar resto terkait grup outlet
        $id_resto_list = OutletResto::where('id_group_outlet', $id_group_outlet)
            ->pluck('id_resto');

        if ($id_resto_list->isEmpty()) {
            return ApiFormatter::createApi(404, 'Tidak ada restoran terkait dengan grup outlet ini.');
        }

        // Filter berdasarkan id-resto jika dikirimkan
        $query = FakturProdukDetail::join('faktur', 'faktur.id_faktur', '=', 'faktur_detail.id_faktur')
            ->leftJoin('supplier', 'supplier.id_supplier', '=', 'faktur.id_supplier')
            ->leftJoin('users', 'users.id', '=', 'faktur.id_pegawai')
            ->leftJoin('produk', 'produk.id', '=', 'faktur_detail.id_produk')
            ->leftJoin('kategori_produk', 'kategori_produk.id', '=', 'produk.id_kategori_produk')
            ->whereIn('supplier.id_resto', $id_resto_list)
            ->whereBetween('faktur.created_at', [$tanggal_awal, $tanggal_akhir]);

        // Jika ada id-resto dalam request, tambahkan filter
        if ($request->has('id-resto') && $request->input('id-resto') !== '') {
            $query->where('supplier.id_resto', $request->input('id-resto'));
        }

        $data_stok = $query
            ->orderBy('faktur.created_at', 'desc')
            ->select(
                'faktur_detail.id_faktur_detail',
                'faktur_detail.id_faktur',
                'faktur_detail.id_produk',
                'faktur_detail.jumlah_stok',
                'faktur_detail.harga_beli',
                'faktur_detail.harga_jual',
                'faktur.created_at as tanggal_faktur',
                'supplier.nama_supplier',
                'supplier.alamat',
                'supplier.no_whatsapp',
                'supplier.id_resto',
                'users.id as id_pegawai',
                'users.name as nama_pegawai',
                'produk.id as product_id',
                'produk.nama_produk',
                'produk.id_kategori_produk',
                'produk.nomor_sku',
                'produk.gambar',
                'produk.harga_awal',
                'produk.harga_jual',
                'produk.diskon',
                'produk.stok',
                'produk.status_diskon',
                'produk.status_produk',
                'kategori_produk.kategori_produk'
            )
            ->get();

        return ApiFormatter::createApi(200, 'Data mutasi stok berhasil ditemukan.', $data_stok);
    }

    public function laporan_pendapatan_group(Request $request)
    {
        $tanggal_awal = date('Y-m-d H:i:s', strtotime($request->input('tanggal-awal')));
        $tanggal_akhir = date('Y-m-d H:i:s', strtotime($request->input('tanggal-akhir')));

        // Ambil group outlet pengguna
        $id_group_outlet = GroupOutlet::where('id_multi_outlet', auth()->user()->id)
            ->pluck('id')
            ->first();

        if (!$id_group_outlet) {
            return ApiFormatter::createApi(404, 'Group outlet tidak ditemukan.');
        }

        // Ambil daftar resto terkait grup outlet
        $id_resto_list = OutletResto::where('id_group_outlet', $id_group_outlet)
            ->pluck('id_resto');

        if ($id_resto_list->isEmpty()) {
            return ApiFormatter::createApi(404, 'Tidak ada restoran terkait dengan grup outlet ini.');
        }

        // Jika parameter id_resto diberikan, filter untuk toko tertentu
        $id_resto = $request->input('id_resto');
        if ($id_resto && !$id_resto_list->contains($id_resto)) {
            return ApiFormatter::createApi(404, 'Restoran tidak ditemukan dalam grup outlet.');
        }

        // Ambil data order dari restoran yang sesuai
        $orderQuery = Order::whereBetween('created_at', [$tanggal_awal, $tanggal_akhir])
            ->whereIn('id_resto', $id_resto_list)
            ->where('status_order', 'closed');

        if ($id_resto) {
            $orderQuery->where('id_resto', $id_resto);
        }

        $order = $orderQuery->get();

        // Variabel inisialisasi
        $penjualan_kotor = 0;
        $total_diskon = 0;
        $penjualan_bersih = 0;
        $laba_kotor = 0;
        $hpp = 0;
        $pajak = 0;
        $service_charge = 0;

        foreach ($order as $value) {
            $laba_kotor += $value->nilai_laba;
            $total_diskon += $value->diskon;
            $penjualan_bersih += $value->nilai_transaksi;
            $penjualan_kotor += $value->nilai_transaksi + $value->diskon;

            $pajak += $value->pajak;
            $service_charge += $value->service_charge;

            foreach ($value->order_detail as $order_detail) {
                $hpp += optional($order_detail->produk)->harga_awal * $order_detail->jumlah_beli;
            }
        }

        $laba_bersih = $penjualan_bersih - $hpp;

        return ApiFormatter::createApi(200, 'Success', [
            'penjualan_bersih' => $penjualan_bersih,
            'hpp' => $hpp,
            'laba_bersih' => $laba_bersih,
            'penjualan_kotor' => $penjualan_kotor,
            'total_diskon' => $total_diskon,
            'laba_kotor' => $laba_kotor,
            'pajak' => $pajak,
            'service_charge' => $service_charge,
        ]);
    }

}
