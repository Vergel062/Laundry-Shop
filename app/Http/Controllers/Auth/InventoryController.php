<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryHistoryService;

class InventoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Security check kung staff o angkop ang role
        if (!in_array($user->role, ['staff', 'manager', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $user_role = strtolower($user->role ?? 'staff');
        $branch_id = $user->branch_id;

        // Kunin ang branch name
        $branch = DB::table('branches')->where('id', $branch_id)->first();
        $branch_name = $branch ? $branch->branch_name : 'Unknown Branch';

        // Kunin ang inventory data para sa branch na ito
        $inventory = DB::table('inventory')
            ->where('branch_id', $branch_id)
            ->orderBy('item_name', 'ASC')
            ->get();

        return view('staff.inventory', compact('user_role', 'branch_name', 'inventory'));
    }

    public function deductManual(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'staff') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'inventory_id' => 'required|integer|exists:inventory,id',
        ]);

        $item = DB::table('inventory')
            ->where('id', $request->inventory_id)
            ->where('branch_id', $user->branch_id)
            ->first();

        if (!$item) {
            return redirect()->route('staff.inventory')->with('error', 'Inventory item not found.');
        }

        $itemName = strtolower($item->item_name);
        $isManualItem = str_contains($itemName, 'spray')
            || str_contains($itemName, 'lpg')
            || str_contains($itemName, 'gas');

        if (!$isManualItem) {
            return redirect()->route('staff.inventory')->with('error', 'Only Fabric Spray and LPG can be manually deducted.');
        }

        if ((int) $item->stock_level < 1) {
            return redirect()->route('staff.inventory')->with('error', $item->item_name . ' is already out of stock.');
        }

        InventoryHistoryService::adjustByItemName($user->branch_id, $item->item_name, -1, 'Staff manual deduction', $user->id);

        return redirect()->route('staff.inventory')->with('success', $item->item_name . ' deducted by 1.');
    }
}