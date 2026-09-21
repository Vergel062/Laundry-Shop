<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryHistoryService;

class ManagerInventoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Security check
        if (!$user || $user->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $my_branch_id = $user->branch_id;

        // Kunin ang pangalan ng branch
        $branch = DB::table('branches')->where('id', $my_branch_id)->first();
        $branch_name = $branch ? ($branch->branch_name ?? $branch->name ?? 'Assigned Branch') : 'Assigned Branch';

        // Kunin ang inventory ng branch
        $inventory = DB::table('inventory')
            ->where('branch_id', $my_branch_id)
            ->orderBy('item_name', 'ASC')
            ->get();

        return view('manager.inventory', compact('inventory', 'branch_name'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $my_branch_id = $user->branch_id;

        // 1. Restock (Idadagdag sa kasalukuyang stock)
        if ($request->filled('detergent_stock')) {
            InventoryHistoryService::adjustByItemName($my_branch_id, '%Detergent%', (int) $request->detergent_stock, 'Manager inventory restock', $user->id);
        }

        if ($request->filled('downy_stock')) {
            InventoryHistoryService::adjustByItemName($my_branch_id, '%Downy%', (int) $request->downy_stock, 'Manager inventory restock', $user->id);
        }

        // 2. Manual Adjustment (Papatungan ang stock level)
        if ($request->filled('spray_stock')) {
            InventoryHistoryService::setByItemName($my_branch_id, '%Spray%', (int) $request->spray_stock, 'Manager inventory adjustment', $user->id);
        }

        if ($request->filled('lpg_stock')) {
            InventoryHistoryService::setByItemName($my_branch_id, '%LPG%', (int) $request->lpg_stock, 'Manager inventory adjustment', $user->id);
        }

        return redirect()->route('manager.inventory')->with('status', 'success');
    }
}