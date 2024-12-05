<?php

namespace App\Http\Controllers\API;

use App\Models\GroupOutlet;
use App\Models\OutletResto;
use App\Models\Resto;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupOutletController extends Controller
{
    public function index()
    {
        $groupOutlets = GroupOutlet::with(['multiOutletUser:id,name'])->get();
        return response()->json($groupOutlets);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_outlet' => 'required|string|max:255',
            'id_resto' => 'required|array',
            'id_resto.*' => 'integer|exists:resto,id',
        ]);

        $groupOutlet = GroupOutlet::create([
            'nama_outlet' => $request->nama_outlet,
        ]);

        foreach ($request->id_resto as $restoId) {
            OutletResto::create([
                'id_group_outlet' => $groupOutlet->id,
                'id_resto' => $restoId,
            ]);
        }

        return response()->json([
            'message' => 'group Outlet created successfully',
            'data' => $groupOutlet
        ], 201);
    }

    public function show($id)
    {
        $groupOutlet = GroupOutlet::find($id);

        if (!$groupOutlet) {
            return response()->json(['message' => 'group Outlet not found'], 404);
        }

        $outletRestos = OutletResto::where('id_group_outlet', $id)->get();
        $restoIds = $outletRestos->pluck('id_resto');
        $restos = Resto::whereIn('id', $restoIds)->get();

        return response()->json([
            'group_outlet' => $groupOutlet,
            'outlet_restos' => $outletRestos,
            'restos' => $restos,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $groupOutlet = GroupOutlet::find($id);

        if (!$groupOutlet) {
            return response()->json(['message' => 'Group Outlet not found'], 404);
        }

        $request->validate([
            'nama_outlet' => 'required|string|max:255',
            'id_resto' => 'sometimes|required|array',
            'id_resto.*' => 'integer|exists:resto,id',
        ]);

        $groupOutlet->nama_outlet = $request->input('nama_outlet');
        $groupOutlet->save();

        if ($request->has('id_resto')) {
            OutletResto::where('id_group_outlet', $id)->delete();

            foreach ($request->id_resto as $restoId) {
                OutletResto::create([
                    'id_group_outlet' => $groupOutlet->id,
                    'id_resto' => $restoId,
                ]);
            }
        }

        return response()->json([
            'message' => 'Group Outlet updated successfully',
            'data' => $groupOutlet
        ]);
    }

    public function destroy($id)
    {
        $groupOutlet = GroupOutlet::find($id);

        if (!$groupOutlet) {
            return response()->json(['message' => 'group Outlet not found'], 404);
        }

        OutletResto::where('id_group_outlet', $id)->delete();

        $groupOutlet->delete();

        return response()->json(['message' => 'group Outlet deleted successfully']);
    }

    public function getUsersByResto($restoId)
    {
        $users = User::where('id_resto', $restoId)
                     ->whereIn('id_level', [2, 4])
                     ->get();

        return response()->json($users);
    }

    public function assignMultiOutlet(Request $request, $groupOutletId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $groupOutlet = GroupOutlet::find($groupOutletId);

        if (!$groupOutlet) {
            return response()->json(['message' => 'Group Outlet not found'], 404);
        }

        $groupOutlet->id_multi_outlet = $request->user_id;
        $groupOutlet->save();

        return response()->json(['message' => 'User assigned as multi outlet']);
    }

    public function removeMultiOutlet($groupOutletId)
    {
        $groupOutlet = GroupOutlet::find($groupOutletId);

        if (!$groupOutlet) {
            return response()->json(['message' => 'Group Outlet not found'], 404);
        }

        // Hapus nilai id_multi_outlet
        $groupOutlet->id_multi_outlet = null;
        $groupOutlet->save();

        return response()->json(['message' => 'Multi Outlet removed successfully']);
    }

    public function checkMultiOutlet(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $exists = GroupOutlet::where('id_multi_outlet', $request->id)->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'ID exists in id_multi_outlet' : 'ID does not exist in id_multi_outlet',
        ], 200);
    }

}
