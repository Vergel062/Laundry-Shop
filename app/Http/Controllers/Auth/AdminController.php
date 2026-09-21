<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $selected_branch = $request->input('branch_id', 'all');

        // 1. Query para sa Cards (Sales, Expenses, Total KG)
        $salesQuery = DB::table('transactions')->where('payment_status', 'Paid');
        $expensesQuery = DB::table('expenses');
        $kgQuery = DB::table('transactions');

        if ($selected_branch !== 'all') {
            $branch_id = intval($selected_branch);
            $salesQuery->where('branch_id', $branch_id);
            $expensesQuery->where('branch_id', $branch_id);
            $kgQuery->where('branch_id', $branch_id);
        }

        $total_sales = $salesQuery->sum('total_amount') ?? 0;
        $total_expenses = $expensesQuery->sum('amount') ?? 0;
        $total_kg = $kgQuery->sum('weight_kg') ?? 0;

        // 2. Kunin ang lahat ng branches para sa dropdown
        $branches = DB::table('branches')->orderBy('branch_name', 'asc')->get();

        // 3. Query para sa Revenue Trends (per week)
        $revenue_data = [0, 0, 0, 0];
        $weeklySales = DB::table('transactions')
            ->select(
                DB::raw('FLOOR((DAYOFMONTH(created_at) - 1) / 7) + 1 AS week_num'),
                DB::raw('SUM(total_amount) as weekly_sales')
            )
            ->where('payment_status', 'Paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);

        if ($selected_branch !== 'all') {
            $weeklySales->where('branch_id', intval($selected_branch));
        }

        $weeklySales = $weeklySales->groupBy('week_num')->get();

        foreach ($weeklySales as $row) {
            $index = (int)$row->week_num - 1;
            if ($index >= 0 && $index < 4) {
                $revenue_data[$index] = (float)$row->weekly_sales;
            }
        }
        $json_revenue = json_encode($revenue_data);

        // 4. Query para sa Top Branches
        $ranking = DB::table('branches as b')
            ->leftJoin('transactions as t', function($join) {
                $join->on('b.id', '=', 't.branch_id')
                     ->where('t.payment_status', '=', 'Paid');
            })
            ->select('b.branch_name', DB::raw('COUNT(t.id) as orders'), DB::raw('SUM(t.total_amount) as sales'))
            ->groupBy('b.id', 'b.branch_name')
            ->orderBy('sales', 'desc')
            ->limit(3)
            ->get();

        // 5. Query para sa Recent Customer Payments
        $historyQuery = DB::table('transactions as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->join('branches as b', 't.branch_id', '=', 'b.id')
            ->select('t.*', 'u.fullname', 'b.branch_name');

        if ($selected_branch !== 'all') {
            $historyQuery->where('t.branch_id', intval($selected_branch));
        }
        $history_q = $historyQuery->orderBy('t.created_at', 'desc')->limit(5)->get();

        // 6. Query para sa Recent Utility Logs
        $logsQuery = DB::table('expenses as e')
            ->join('branches as b', 'e.branch_id', '=', 'b.id')
            ->select('e.*', 'b.branch_name');

        if ($selected_branch !== 'all') {
            $logsQuery->where('e.branch_id', intval($selected_branch));
        }
        
        $logs_q = $logsQuery->orderBy('e.date_logged', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact(
            'selected_branch',
            'total_sales',
            'total_expenses',
            'total_kg',
            'branches',
            'json_revenue',
            'ranking',
            'history_q',
            'logs_q'
        ));
    }
}