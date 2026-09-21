<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryHistoryService;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        // 1. Kunin ang branch_id mula sa session
        $my_branch = session('branch_id');

        // 2. Kunin ang branch name mula sa database (Para lumabas ang 'Carmona Branch', atbp.)
        $branchName = DB::table('branches')->where('id', $my_branch)->value('branch_name') ?? 'Unknown Branch';

        // Kunin ang petsa ngayon sa Pilipinas
        $currentDate = Carbon::now('Asia/Manila')->toDateString();

        // AUTO-FIX: Kung na-approve na online payment o na-mark as paid pero walang date_paid, 
        // lalagyan natin ito ng date ngayon para pumasok sa Today's Sales.
        DB::table('transactions')
            ->where('branch_id', $my_branch)
            ->where('payment_status', 'Paid')
            ->whereNull('date_paid')
            ->update(['date_paid' => $currentDate]);

        // Mga Counts para sa Dashboard Stats
        $pending_count = DB::table('transactions')
            ->where('order_status', 'Pending')
            ->where('branch_id', $my_branch)
            ->count();

        $drying_count = DB::table('transactions')
            ->where('order_status', 'Drying')
            ->where('branch_id', $my_branch)
            ->count();

        $ready_count = DB::table('transactions')
            ->where('order_status', 'Ready')
            ->where('branch_id', $my_branch)
            ->count();

        $today_sales = DB::table('transactions')
            ->where('payment_status', 'Paid')
            ->whereDate('date_paid', $currentDate)
            ->where('branch_id', $my_branch)
            ->sum('total_amount') ?? 0;

        // Inventory Warnings
        $detergent = DB::table('inventory')
            ->where('item_name', 'like', '%Detergent%')
            ->where('branch_id', $my_branch)
            ->first();

        $spray = DB::table('inventory')
            ->where('item_name', 'like', '%Spray%')
            ->where('branch_id', $my_branch)
            ->first();

        $lpg = DB::table('inventory')
            ->where('item_name', 'like', '%LPG%')
            ->where('branch_id', $my_branch)
            ->first();

        $downy = DB::table('inventory')
            ->where('item_name', 'like', '%Downy%')
            ->where('branch_id', $my_branch)
            ->first();

        // Side Inventory Status (Limit 4)
        $side_inv = DB::table('inventory')
            ->where('branch_id', $my_branch)
            ->limit(4)
            ->get();

        // Customers para sa New Order Modal
        $customers = DB::table('users')
            ->where('role', 'customer')
            ->where('branch_id', $my_branch)
            ->orderBy('fullname', 'asc')
            ->get();

        // Filter logic para sa Transactions Table (Ngayong araw at sorted)
        $filter = $request->input('filter', 'All');
        
        $query = DB::table('transactions as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->select('t.*', 'u.fullname')
            ->where('t.branch_id', $my_branch)
            ->whereDate('t.created_at', $currentDate);

        if ($filter == 'Pending') {
            $query->where('t.order_status', 'Pending');
        } elseif ($filter == 'Ready') {
            $query->where('t.order_status', 'Ready');
        }

        $orders = $query->orderByRaw("FIELD(t.order_status, 'Claimed', 'Cancelled') ASC")
            ->orderByRaw("(t.payment_status = 'Unpaid') DESC")
            ->orderBy('t.created_at', 'desc')
            ->limit(10)
            ->get();

        // 3. Ipasa ang branchName sa view gamit ang compact
        return view('staff.dashboard', compact(
            'pending_count',
            'drying_count',
            'ready_count',
            'today_sales',
            'detergent',
            'spray',
            'lpg',
            'downy',
            'side_inv',
            'customers',
            'orders',
            'filter',
            'branchName'
        ));
    }

    public function storeOrder(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required',
                'weight' => 'required|numeric',
                'service' => 'required|in:Wash Only,Dry Only,Wash-Dry,Wash-Dry-Fold,Comforter (Wash Only),Comforter (Dry Only),Comforter (Wash-Dry),Comforter (Wash-Dry-Fold)',
                'det_qty' => 'nullable|integer|min:0',
                'soft_qty' => 'nullable|integer|min:0',
                'amount' => 'required|numeric',
                'request_id' => 'nullable|integer|exists:transactions,id',
            ]);

            $staff_id = session('id') ?? session('user_id') ?? 1;
            $my_branch = session('branch_id') ?? 1;
            $refNumber = 'LC-' . strtoupper(substr(uniqid(), -6));
            
            $phTime = Carbon::now('Asia/Manila');
            $basePrices = [
                'Wash Only' => 70,
                'Dry Only' => 60,
                'Wash-Dry' => 130,
                'Wash-Dry-Fold' => 170,
                'Comforter (Wash Only)' => 140,
                'Comforter (Dry Only)' => 130,
                'Comforter (Wash-Dry)' => 200,
                'Comforter (Wash-Dry-Fold)' => 240,
            ];
            $weight = (float) $request->weight;
            $amount = $basePrices[$request->service] + (max(0, $weight - 8) * 10);

            DB::transaction(function () use ($request, $staff_id, $my_branch, $refNumber, $phTime, $amount, $weight) {
                $requestQuery = DB::table('transactions')
                    ->where('id', $request->input('request_id'))
                    ->where('branch_id', $my_branch)
                    ->where('user_id', $request->user_id)
                    ->where('order_status', 'Pending')
                    ->where('payment_status', 'Service Request');

                if ($request->filled('request_id') && $requestQuery->exists()) {
                    $requestQuery->update([
                        'staff_id' => $staff_id,
                        'ref_number' => $refNumber,
                        'weight_kg' => $weight,
                        'service_type' => $request->service,
                        'total_amount' => $amount,
                        'payment_status' => 'Unpaid',
                    ]);
                } else {
                    DB::table('transactions')->insert([
                        'user_id' => $request->user_id,
                        'staff_id' => $staff_id,
                        'branch_id' => $my_branch,
                        'ref_number' => $refNumber,
                        'weight_kg' => $weight,
                        'service_type' => $request->service,
                        'total_amount' => $amount,
                        'order_status' => 'Pending',
                        'payment_status' => 'Unpaid',
                        'created_at' => $phTime,
                    ]);
                }

                $detQty = $request->input('det_qty', 0);
                if ($detQty > 0) {
                    InventoryHistoryService::adjustByItemName($my_branch, '%Detergent%', -$detQty, 'Staff service order usage', $staff_id);
                }

                $softQty = $request->input('soft_qty', 0);
                if ($softQty > 0) {
                    InventoryHistoryService::adjustByItemName($my_branch, '%Downy%', -$softQty, 'Staff service order usage', $staff_id);
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Order saved and inventory updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'expense_type' => 'required|string',
            'amount' => 'required|numeric',
            'remarks' => 'nullable|string',
        ]);

        $staff_id = session('id') ?? session('user_id') ?? 1;
        $my_branch = session('branch_id') ?? 1;
        $phTime = Carbon::now('Asia/Manila');

        // Dynamic detection kung anong date column ang meron sa expenses table
        $dateCol = 'created_at';
        if (Schema::hasColumn('expenses', 'date_logged')) {
            $dateCol = 'date_logged';
        } elseif (Schema::hasColumn('expenses', 'date')) {
            $dateCol = 'date';
        }

        // I-set lang ang date column kung nag-eexist ito sa table para iwas error
        $dataToInsert = [
            'branch_id' => $my_branch,
            'staff_id' => $staff_id,
            'expense_type' => $request->expense_type,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
        ];

        if (Schema::hasColumn('expenses', $dateCol)) {
            $dataToInsert[$dateCol] = $phTime;
        }

        DB::table('expenses')->insert($dataToInsert);

        return redirect()->route('staff.dashboard')->with('success', 'Expense submitted successfully!');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect('/login')->with('success', 'Successfully logged out.');
    }
}