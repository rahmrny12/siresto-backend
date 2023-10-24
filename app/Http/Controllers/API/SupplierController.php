<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Helper\ApiFormatter;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Supplier::query();
        $id_toko = auth()->user()->id_toko;

        $query->where('id_toko', $id_toko);
        if ($s = request()->input('s')) {
            $query->where('supplier', 'ILIKE', '%' . $s . '%');
        }

        if ($sort = request()->input('sort')) {
            $query->orderBy('id_supplier', $sort);
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
            $id_toko = $user->id_toko;

            $supplier = Supplier::create([
                'nama_supplier' => $request->nama_supplier,
                'alamat' => $request->alamat,
                'no_whatsapp' => $request->no_whatsapp,
                'id_toko' => $id_toko,
            ]);

            if ($supplier) {
                return ApiFormatter::createApi(200, 'Success', $supplier);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(500, 'Failed. Error : ' . $error);
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
        $data = Supplier::findOrFail($id);

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
            $id_toko = $request->user()->id_toko;
            $supplier = Supplier::where('id_supplier', $id)->update([
                'nama_supplier' => $request->nama_supplier,
                'alamat' => $request->alamat,
                'no_whatsapp' => $request->no_whatsapp,
            ]);

            $data = Supplier::find($id);

            if ($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(500, 'Failed. Error : ' . $error);
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
        $supplier = Supplier::findOrFail($id);
        $cek_jumlah = $supplier->faktur->count();
        if ($cek_jumlah > 0) {
            return ApiFormatter::createApi(409, 'Supplier Already Used');
        }
        $data = $supplier->delete();
        
        if ($data) {
            return ApiFormatter::createApi(200, 'Success Destroy Data');
        } else {
            return ApiFormatter::createApi(400, 'Failed');
        }
    }

    public function supplier_all(Request $request)
    {
        try {
            $s = $request->s;
            $id_toko = auth()->user()->id_toko;
            $data = Supplier::select('id_supplier as value', 'nama_supplier as label')->where('nama_supplier', 'ILIKE', "%$s%")->where('id_toko', $id_toko)->get();

            if ($data) {
                return ApiFormatter::createApi(200, 'Success', $data);
            } else {
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(500, 'Failed. Error : ' . $error);
        }
    }
}
