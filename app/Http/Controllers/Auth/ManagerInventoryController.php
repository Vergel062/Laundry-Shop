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
        $branch_name = \App\Support\BranchName::format(
            $branch->branch_name ?? $branch->name ?? null,
            'Assigned Branch'
        );

        // Kunin ang inventory ng branch
        $inventory = DB::table('inventory')
            ->where('branch_id', $my_branch_id)
            ->orderBy('item_name', 'ASC')
            ->get();

        $deductionRequests = DB::table('inventory_deduction_requests as r')
            ->join('inventory as i', 'r.inventory_id', '=', 'i.id')
            ->join('users as u', 'r.staff_id', '=', 'u.id')
            ->where('r.branch_id', $my_branch_id)
            ->where('r.status', 'pending')
            ->select(
                'r.id',
                'r.quantity',
                'r.created_at',
                'i.item_name',
                'i.stock_level',
                'i.unit',
                'u.fullname as staff_name'
            )
            ->orderBy('r.created_at')
            ->get();

        return view('manager.inventory', compact('inventory', 'branch_name', 'deductionRequests'));
    }

    public function history(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $branchId = (int) $user->branch_id;
        $branch = DB::table('branches')->where('id', $branchId)->first();
        abort_unless($branch, 404, 'Assigned branch could not be found.');

        $selected_branch = (string) $branchId;
        $selected_item = $request->input('item', 'all');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $hasDateFilter = $request->boolean('filter') && $start_date && $end_date;

        $historyQuery = DB::table('inventory_history as h')
            ->join('branches as b', 'h.branch_id', '=', 'b.id')
            ->where('h.branch_id', $branchId)
            ->select('h.*', 'b.branch_name');

        $itemFilters = [
            'detergent' => ['detergent', ['%detergent%', '%powder%']],
            'downy' => ['softener', ['%downy%', '%softener%']],
            'spray' => ['spray', ['%spray%']],
            'lpg' => ['lpg', ['%lpg%', '%gas%']],
        ];

        if (isset($itemFilters[$selected_item])) {
            [$systemKey, $namePatterns] = $itemFilters[$selected_item];
            $historyQuery->whereIn('h.inventory_id', DB::table('inventory')
                ->select('id')
                ->where('branch_id', $branchId)
                ->where(function ($query) use ($systemKey, $namePatterns) {
                    $query->where('system_key', $systemKey)
                        ->orWhere(function ($nameQuery) use ($namePatterns) {
                            foreach ($namePatterns as $pattern) {
                                $nameQuery->orWhereRaw('LOWER(item_name) LIKE ?', [$pattern]);
                            }
                        });
                }));
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
        $branches_dropdown = collect([$branch]);

        return view('manager.inventory_history', compact(
            'history',
            'branches_dropdown',
            'selected_branch',
            'selected_item',
            'start_date',
            'end_date',
            'hasDateFilter',
            'total_in',
            'total_out'
        ));
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
            InventoryHistoryService::adjustBySystemKey($my_branch_id, 'detergent', (int) $request->detergent_stock, 'Manager inventory restock', $user->id);
        }

        if ($request->filled('downy_stock')) {
            InventoryHistoryService::adjustBySystemKey($my_branch_id, 'softener', (int) $request->downy_stock, 'Manager inventory restock', $user->id);
        }

        // 2. Manual Adjustment (Papatungan ang stock level)
        if ($request->filled('spray_stock')) {
            InventoryHistoryService::setBySystemKey($my_branch_id, 'spray', (int) $request->spray_stock, 'Manager inventory adjustment', $user->id);
        }

        if ($request->filled('lpg_stock')) {
            InventoryHistoryService::setBySystemKey($my_branch_id, 'lpg', (int) $request->lpg_stock, 'Manager inventory adjustment', $user->id);
        }

        return redirect()->route('manager.inventory')->with('status', 'success');
    }

    public function reviewDeduction(Request $request, int $id)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'decision' => 'required|in:approved,rejected',
        ]);

        $result = DB::transaction(function () use ($id, $user, $validated) {
            $deductionRequest = DB::table('inventory_deduction_requests')
                ->where('id', $id)
                ->where('branch_id', $user->branch_id)
                ->lockForUpdate()
                ->first();

            if (!$deductionRequest || $deductionRequest->status !== 'pending') {
                return 'not_pending';
            }

            if ($validated['decision'] === 'rejected') {
                DB::table('inventory_deduction_requests')
                    ->where('id', $deductionRequest->id)
                    ->update([
                        'status' => 'rejected',
                        'reviewed_by' => $user->id,
                        'reviewed_at' => now(),
                        'updated_at' => now(),
                    ]);

                return 'rejected';
            }

            $item = DB::table('inventory')
                ->where('id', $deductionRequest->inventory_id)
                ->where('branch_id', $user->branch_id)
                ->lockForUpdate()
                ->first();

            if (!$item) {
                return 'item_not_found';
            }

            $before = (int) $item->stock_level;
            $quantity = (int) $deductionRequest->quantity;

            if ($before < $quantity) {
                return 'insufficient_stock';
            }

            $after = $before - $quantity;
            DB::table('inventory')
                ->where('id', $item->id)
                ->update([
                    'stock_level' => $after,
                    'last_updated' => now(),
                ]);

            InventoryHistoryService::record(
                $item,
                $before,
                $after,
                'Manager-approved Staff deduction request #' . $deductionRequest->id,
                $user->id
            );

            DB::table('inventory_deduction_requests')
                ->where('id', $deductionRequest->id)
                ->update([
                    'status' => 'approved',
                    'reviewed_by' => $user->id,
                    'reviewed_at' => now(),
                    'updated_at' => now(),
                ]);

            return 'approved';
        });

        $messages = [
            'approved' => ['success', 'Deduction request approved and inventory updated.'],
            'rejected' => ['success', 'Deduction request rejected. Inventory was not changed.'],
            'not_pending' => ['error', 'This request is no longer pending or does not belong to your branch.'],
            'item_not_found' => ['error', 'The requested inventory item could not be found in your branch.'],
            'insufficient_stock' => ['error', 'There is not enough stock to approve this deduction request. Inventory was not changed.'],
        ];

        [$type, $message] = $messages[$result];

        return redirect()->route('manager.inventory')->with($type, $message);
    }
}