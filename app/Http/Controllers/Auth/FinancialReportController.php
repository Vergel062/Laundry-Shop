<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('login');
        }

        // 1. FILTER SETTINGS: generate only after both dates are selected.
        $selected_branch = $request->input('branch_id', 'all');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $report_generated = filled($start_date) && filled($end_date);
        $start_datetime = $report_generated
            ? Carbon::parse($start_date, 'Asia/Manila')->startOfDay()->format('Y-m-d H:i:s')
            : null;
        $end_datetime = $report_generated
            ? Carbon::parse($end_date, 'Asia/Manila')->endOfDay()->format('Y-m-d H:i:s')
            : null;

        // 2. AUTO-DETECT COLUMNS
        $exp_date_col = \Schema::hasColumn('expenses', 'date') ? 'date' : 'created_at';

        $user_col = 'username';
        if (\Schema::hasColumn('users', 'name')) {
            $user_col = 'name';
        } elseif (\Schema::hasColumn('users', 'fullname')) {
            $user_col = 'fullname';
        }

        // 3. QUERIES - Independent Totals (Sales & Expenses)
        $total_sales = 0;
        $total_expenses = 0;
        $list_q = collect();

        if ($report_generated) {
            $salesQuery = DB::table('transactions as t')
                ->where('payment_status', 'Paid')
                ->whereBetween('t.created_at', [$start_datetime, $end_datetime]);
            if ($selected_branch !== 'all') {
                $salesQuery->where('t.branch_id', intval($selected_branch));
            }
            $total_sales = $salesQuery->sum('total_amount') ?? 0;

            $expensesQuery = DB::table('expenses')
                ->whereDate($exp_date_col, '>=', $start_date)
                ->whereDate($exp_date_col, '<=', $end_date);
            if ($selected_branch !== 'all') {
                $expensesQuery->where('branch_id', intval($selected_branch));
            }
            $total_expenses = $expensesQuery->sum('amount') ?? 0;

            $listQuery = DB::table('transactions as t')
                ->join('branches as b', 't.branch_id', '=', 'b.id')
                ->leftJoin('users as u', 't.user_id', '=', 'u.id')
                ->select('t.*', 'b.branch_name', "u.$user_col as customer_name")
                ->where('t.payment_status', 'Paid')
                ->whereBetween('t.created_at', [$start_datetime, $end_datetime]);
            if ($selected_branch !== 'all') {
                $listQuery->where('t.branch_id', intval($selected_branch));
            }
            $list_q = $listQuery->orderBy('t.created_at', 'DESC')->get();
        }

        // Branches Dropdown
        $branches_dropdown = DB::table('branches')->orderBy('branch_name', 'ASC')->get();
        $selected_branch_name = $selected_branch !== 'all'
            ? optional($branches_dropdown->firstWhere('id', $selected_branch))->branch_name
            : null;

        return view('admin.financial_reports', compact(
            'selected_branch',
            'selected_branch_name',
            'report_generated',
            'start_date',
            'end_date',
            'total_sales',
            'total_expenses',
            'list_q',
            'branches_dropdown'
        ));
    }
}