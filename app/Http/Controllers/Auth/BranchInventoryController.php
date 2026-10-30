<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
        $selected_branch = $request->input('branch_id', '');

        // Query for inventory data
        $query = DB::table('inventory as i')
            ->join('branches as b', 'i.branch_id', '=', 'b.id');

        if ($selected_branch === '') {
            $query->whereRaw('1 = 0');
        } elseif ($selected_branch !== 'all') {
            $query->where('i.branch_id', intval($selected_branch));
        }

        // Priority sorting: Low stock items first, then by Branch and Item Name
        $inventory = $query->select('i.*', 'b.branch_name')
            ->orderByRaw('(i.stock_level <= i.min_threshold) DESC')
            ->orderBy('b.branch_name', 'ASC')
            ->orderBy('i.item_name', 'ASC')
            ->get();

        $branches_dropdown = DB::table('branches')
            ->select('id', 'branch_name')
            ->orderBy('branch_name', 'ASC')
            ->get();

        $inventoryItemsQuery = DB::table('inventory as i')
            ->join('branches as b', 'i.branch_id', '=', 'b.id')
            ->select('i.id', 'i.branch_id', 'i.item_name', 'i.stock_level', 'i.min_threshold', 'b.branch_name')
            ->orderBy('b.branch_name')
            ->orderBy('i.item_name');

        if ($selected_branch === '') {
            $inventoryItemsQuery->whereRaw('1 = 0');
        } elseif ($selected_branch !== 'all') {
            $inventoryItemsQuery->where('i.branch_id', intval($selected_branch));
        }

        $inventory_items = $inventoryItemsQuery->get();

        return view('admin.branch_inventory', compact('inventory', 'inventory_items', 'branches_dropdown', 'selected_branch'));
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
            'detergent' => 'detergent',
            'downy' => 'softener',
            'spray' => 'spray',
            'lpg' => 'lpg',
        ];
        if (isset($itemPatterns[$selected_item])) {
            $historyQuery->whereIn('h.inventory_id', DB::table('inventory')
                ->select('id')
                ->where('system_key', $itemPatterns[$selected_item]));
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

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->merge(['item_name' => trim((string) $request->input('item_name'))]);
        $validated = $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
            'item_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('inventory', 'item_name')
                    ->where('branch_id', $request->input('branch_id')),
            ],
            'stock_level' => 'required|integer|min:0',
            'unit' => ['required', Rule::in(['Sachets', 'Gallons', 'Tanks', 'Pieces'])],
            'min_threshold' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated, $user) {
            $inventoryId = DB::table('inventory')->insertGetId([
                'branch_id' => $validated['branch_id'],
                'item_name' => trim($validated['item_name']),
                'stock_level' => $validated['stock_level'],
                'unit' => $validated['unit'],
                'min_threshold' => $validated['min_threshold'],
                'system_key' => null,
                'last_updated' => now(),
            ]);

            if ($validated['stock_level'] > 0) {
                $item = DB::table('inventory')->where('id', $inventoryId)->first();
                InventoryHistoryService::record($item, 0, $validated['stock_level'], 'Admin added inventory item', $user->id);
            }
        });

        return redirect()->route('admin.inventory', [
            'branch_id' => $request->input('return_branch_id', 'all'),
            'status' => 'added',
        ]);
    }

    public function updateItem(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->merge(['item_name' => trim((string) $request->input('item_name'))]);
        $item = DB::table('inventory')->where('id', $request->input('item_id'))->first();
        if (!$item) {
            return back()->withErrors(['item_id' => 'Inventory item not found.']);
        }

        $validated = $request->validate([
            'item_id' => 'required|integer|exists:inventory,id',
            'item_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('inventory', 'item_name')
                    ->where('branch_id', $item->branch_id)
                    ->ignore($item->id),
            ],
            'min_threshold' => 'required|integer|min:0',
        ]);

        DB::table('inventory')
            ->where('id', $item->id)
            ->update([
                'item_name' => $validated['item_name'],
                'min_threshold' => $validated['min_threshold'],
                'last_updated' => now(),
            ]);

        return redirect()->route('admin.inventory', [
            'branch_id' => $request->input('return_branch_id', 'all'),
            'status' => 'item-updated',
        ]);
    }
}