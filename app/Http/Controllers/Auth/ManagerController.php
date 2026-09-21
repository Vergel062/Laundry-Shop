<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ManagerController extends Controller
{
    // TANGGAL NA DITO ANG __construct() PARA HINDI MAG-ERROR

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        
        // Security check kung manager
        if ($user->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $my_branch_id = $user->branch_id;
        
        // Kunin ang pangalan ng branch
        $branch = DB::table('branches')->where('id', $my_branch_id)->first();
        $branch_name = $branch ? $branch->branch_name : "Assigned Branch";

        // 1. Transaction counts
        $counts_res = DB::table('transactions')
            ->where('branch_id', $my_branch_id)
            ->selectRaw("
                SUM(CASE WHEN order_status = 'Pending' THEN 1 ELSE 0 END) as pending_total,
                SUM(CASE WHEN order_status = 'Drying' THEN 1 ELSE 0 END) as drying_total,
                SUM(CASE WHEN order_status = 'Ready' THEN 1 ELSE 0 END) as ready_total
            ")
            ->first();

        $counts_res = (array) $counts_res;

        // 2. Total Income (Revenue)
        $total_income = DB::table('transactions')
            ->where('payment_status', 'Paid')
            ->where('branch_id', $my_branch_id)
            ->sum('total_amount');

        // 3. Recent Utility Logs
        $util_logs = DB::table('expenses')
            ->where('branch_id', $my_branch_id)
            ->orderBy('date_logged', 'DESC')
            ->limit(5)
            ->get();

        // 4. Live Branch Transaction Logs
        $tx_query = DB::table('transactions as t')
            ->leftJoin('users as u', 't.user_id', '=', 'u.id')
            ->where('t.branch_id', $my_branch_id)
            ->select('t.*', 'u.fullname as customer_name')
            ->orderByRaw("FIELD(t.payment_status, 'Unpaid', 'Paid')")
            ->orderBy('t.id', 'DESC')
            ->limit(10)
            ->get();

        return view('manager.dashboard', compact(
            'branch_name',
            'counts_res',
            'total_income',
            'util_logs',
            'tx_query'
        ));
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'expense_type' => 'required|string',
            'amount' => 'required|numeric',
            'remarks' => 'nullable|string',
        ]);

        $user = auth()->user();
        $dateColumn = Schema::hasColumn('expenses', 'date_logged')
            ? 'date_logged'
            : (Schema::hasColumn('expenses', 'date') ? 'date' : 'created_at');

        $expense = [
            'branch_id' => $user->branch_id,
            'staff_id' => $user->id,
            'expense_type' => $request->expense_type,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
        ];

        if (Schema::hasColumn('expenses', $dateColumn)) {
            $expense[$dateColumn] = Carbon::now('Asia/Manila');
        }

        DB::table('expenses')->insert($expense);

        return redirect()->route('manager.dashboard')
            ->with('success', 'Expense submitted successfully!');
    }
}