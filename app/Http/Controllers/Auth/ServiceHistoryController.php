<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ServiceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Proteksyon: Admin lang ang pwedeng makakita nito
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('login');
        }

        if ($request->routeIs('admin.transaction.report')) {
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);
        }

        // 1. FILTERS
        $selected_branch = $request->input('branch_id', 'all');
        $is_report = $request->routeIs('admin.transaction.report');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        if (!$is_report) {
            $start_date = $start_date ?: date('Y-m-01');
            $end_date = $end_date ?: date('Y-m-d');
        }
        $search = $request->input('search', '');
        $report_generated = !$is_report || ($start_date && $end_date);

        // 2. DETECT COLUMN para sa users table kung fullname o username
        $user_col = Schema::hasColumn('users', 'fullname') ? 'fullname' : 'username';

        // Base Query para sa Transactions
        $query = DB::table('transactions as t')
            ->join('branches as b', 't.branch_id', '=', 'b.id')
            ->leftJoin('users as u', 't.user_id', '=', 'u.id');

        // Apply Branch Filter
        if ($selected_branch !== 'all') {
            $query->where('t.branch_id', $selected_branch);
        }

        // Apply Search Filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search, $user_col) {
                $q->where("u.{$user_col}", 'LIKE', "%{$search}%")
                  ->orWhere('t.ref_number', 'LIKE', "%{$search}%");
            });
        }

        // Apply Date Range Filter only after the report dates are submitted.
        if ($report_generated) {
            $query->whereBetween('t.created_at', ["{$start_date} 00:00:00", "{$end_date} 23:59:59"]);
        }

        // 3. STATS (Bilang ng In vs Out at Collected Sales)
        // Gumawa muna tayo ng clone ng query para sa statistics para hindi maapektuhan ang main list execution
        $statsQuery = clone $query;
        $rawStats = $report_generated ? $statsQuery->select(
            DB::raw('COUNT(t.id) as total_trans'),
            DB::raw("SUM(CASE WHEN t.payment_status = 'Paid' THEN t.total_amount ELSE 0 END) as collected"),
            DB::raw("SUM(CASE WHEN t.order_status = 'Claimed' THEN 1 ELSE 0 END) as total_out"),
            DB::raw("SUM(CASE WHEN t.order_status != 'Claimed' THEN 1 ELSE 0 END) as total_in")
        )->first() : null;

        $stats = [
            'total_trans' => $rawStats->total_trans ?? 0,
            'collected' => $rawStats->collected ?? 0,
            'total_out' => $rawStats->total_out ?? 0,
            'total_in' => $rawStats->total_in ?? 0,
        ];

        // 4. MAIN LIST
        $list = $report_generated ? $query->select(
            't.*',
            'b.branch_name',
            "u.{$user_col} as customer_name"
        )
        ->orderBy('t.created_at', 'DESC')
        ->get() : collect();

        // Branches Dropdown data
        $branches_dropdown = DB::table('branches')->select('id', 'branch_name')->orderBy('branch_name', 'ASC')->get();
        $selected_branch_name = $selected_branch === 'all'
            ? 'ALL BRANCHES'
            : optional($branches_dropdown->firstWhere('id', (int) $selected_branch))->branch_name;
        $selected_branch_name = $selected_branch_name ?: 'ALL BRANCHES';

        $view = $request->routeIs('admin.transaction.report')
            ? 'admin.transaction_report'
            : 'admin.service_history';

        return view($view, compact(
            'selected_branch',
            'start_date',
            'end_date',
            'search',
            'stats',
            'list',
            'branches_dropdown',
            'selected_branch_name',
            'report_generated'
        ));
    }
}