<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryHistoryService;

class BranchInventoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Proteksyon: Admin lang ang pwedeng makakita nito
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('login');
        }

        // Get selected branch for filtering
        $selected_branch = $request->input('branch_id', 'all');

        // Query for inventory data
        $query = DB::table('inventory as i')
            ->join('branches as b', 'i.branch_id', '=', 'b.id');

        if ($selected_branch !== 'all') {
            $query->where('i.branch_id', intval($selected_branch));
        }

        // Priority sorting: Low stock items first, then by Branch and Item Name
        $inventory = $query->select('i.*', 'b.branch_name')
            ->orderByRaw("(
                i.stock_level <= CASE
                    WHEN LOWER(i.item_name) LIKE '%detergent%'
                      OR LOWER(i.item_name) LIKE '%downy%'
                      OR LOWER(i.item_name) LIKE '%softener%' THEN 15
                    WHEN LOWER(i.item_name) LIKE '%spray%' THEN 5
                    WHEN LOWER(i.item_name) LIKE '%lpg%'
                      OR LOWER(i.item_name) LIKE '%gas%' THEN 3
                    ELSE i.min_threshold
                END
            ) DESC")
            ->orderBy('b.branch_name', 'ASC')
            ->orderBy('i.item_name', 'ASC')
            ->get();

        $branches_dropdown = DB::table('branches')
            ->select('id', 'branch_name')
            ->orderBy('branch_name', 'ASC')
            ->get();

        return view('admin.branch_inventory', compact('inventory', 'branches_dropdown', 'selected_branch'));
    }

    public function history(Request $request)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'admin') {
            return redirect()->route('login');
        }

        $selected_branch = $request->input('branch_id', 'all');
        $selected_item = $request->input('item', 'all');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $historyQuery = DB::table('inventory_history as h')
            ->join('branches as b', 'h.branch_id', '=', 'b.id')
            ->select('h.*', 'b.branch_name');

        $hasDateFilter = $request->boolean('filter') && $start_date && $end_date;

        if ($selected_branch !== 'all') {
            $historyQuery->where('h.branch_id', intval($selected_branch));
        }

        $itemPatterns = [
            'detergent' => '%Detergent%',
            'downy' => '%Downy%',
            'spray' => '%Spray%',
            'lpg' => '%LPG%',
        ];
        if (isset($itemPatterns[$selected_item])) {
            $historyQuery->where('h.item_name', 'like', $itemPatterns[$selected_item]);
        }

        if ($hasDateFilter) {
            $historyQuery->whereBetween('h.created_at', [
                $start_date . ' 00:00:00',
                $end_date . ' 23:59:59',
            ]);
        } else {
            $historyQuery->whereRaw('1 = 0');
        }

        $history = $historyQuery->orderByDesc('h.created_at')->get();
        $total_in = $history->where('movement_type', 'IN')->sum('quantity');
        $total_out = $history->where('movement_type', 'OUT')->sum('quantity');
        $branches_dropdown = DB::table('branches')
            ->select('id', 'branch_name')
            ->orderBy('branch_name', 'ASC')
            ->get();

        return view('admin.inventory_history', compact('history', 'branches_dropdown', 'selected_branch', 'selected_item', 'start_date', 'end_date', 'hasDateFilter', 'total_in', 'total_out'));
    }

    public function updateStock(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('login');
        }

        $request->validate([
            'item_id' => 'required|integer',
            'new_stock' => 'required|integer|min:0',
            'branch_id' => 'nullable',
        ]);

        $item = DB::table('inventory')->where('id', $request->input('item_id'))->first();
        if (!$item) {
            return back()->withErrors(['item_id' => 'Inventory item not found.']);
        }

        $newStock = (int) $request->input('new_stock');
        DB::table('inventory')->where('id', $item->id)->update([
            'stock_level' => $newStock,
            'last_updated' => now(),
        ]);
        InventoryHistoryService::record($item, (int) $item->stock_level, $newStock, 'Admin inventory update', $user->id);

        $selectedBranch = $request->input('branch_id', 'all');

        return redirect()->route('admin.inventory', [
            'branch_id' => $selectedBranch,
            'status' => 'updated',
        ]);
    }
}