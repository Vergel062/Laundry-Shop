<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ManagerFinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. SECURITY: Manager access only
        if (!$user || $user->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $my_branch_id = $user->branch_id;

        // --- BAGONG DAGDAG: Kunin ang Branch Name ng Manager ---
        $branch = DB::table('branches')->where('id', $my_branch_id)->first();
        // Tinitingnan dito kung 'branch_name' o 'name' ang column name sa table mo
        $branch_name = $branch ? ($branch->branch_name ?? $branch->name ?? 'Unknown Branch') : 'Unknown Branch';

        // 2. FILTER SETTINGS: generate only after both dates are selected.
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $search = trim((string) $request->get('search', ''));
        $report_generated = filled($start_date) && filled($end_date);
        $start_datetime = $report_generated
            ? Carbon::parse($start_date, 'Asia/Manila')->startOfDay()->format('Y-m-d H:i:s')
            : null;
        $end_datetime = $report_generated
            ? Carbon::parse($end_date, 'Asia/Manila')->endOfDay()->format('Y-m-d H:i:s')
            : null;

        // 3. AUTO-DETECT COLUMNS
        $exp_date_col = Schema::hasColumn('expenses', 'date') ? 'date' : 'created_at';

        // Alamin kung anong column ang available para sa pangalan ng user
        $hasName = Schema::hasColumn('users', 'name');
        $hasFullname = Schema::hasColumn('users', 'fullname');
        $hasUsername = Schema::hasColumn('users', 'username');

        // Dynamic COALESCE builder base sa kung anong columns ang nag-eexist
        $userCols = [];
        if ($hasName) $userCols[] = 'u.name';
        if ($hasFullname) $userCols[] = 'u.fullname';
        if ($hasUsername) $userCols[] = 'u.username';
        
        $customerSelect = count($userCols) > 0 ? 'COALESCE(' . implode(', ', $userCols) . ') as customer_name' : "'' as customer_name";

        // 4. QUERIES
        $total_sales = 0;
        $total_expenses = 0;
        $transactions = collect();

        if ($report_generated) {
            $salesQuery = DB::table('transactions as t')
                ->leftJoin('users as u', 't.user_id', '=', 'u.id')
                ->where('t.branch_id', $my_branch_id)
                ->where('t.payment_status', 'Paid')
                ->whereBetween('t.created_at', [$start_datetime, $end_datetime]);

            $transactionsQuery = DB::table('transactions as t')
                ->join('branches as b', 't.branch_id', '=', 'b.id')
                ->leftJoin('users as u', 't.user_id', '=', 'u.id')
                ->where('t.branch_id', $my_branch_id)
                ->where('t.payment_status', 'Paid')
                ->whereBetween('t.created_at', [$start_datetime, $end_datetime]);

            if ($search !== '') {
                $searchTerm = '%' . $search . '%';
                $applySearch = function ($query) use ($searchTerm, $userCols) {
                    $query->where(function ($searchQuery) use ($searchTerm, $userCols) {
                        $searchQuery->where('t.ref_number', 'like', $searchTerm)
                            ->orWhere('t.service_type', 'like', $searchTerm);

                        foreach ($userCols as $userColumn) {
                            $searchQuery->orWhere($userColumn, 'like', $searchTerm);
                        }
                    });
                };

                $applySearch($salesQuery);
                $applySearch($transactionsQuery);
            }

            $total_sales = $salesQuery->sum('t.total_amount');

            $total_expenses = DB::table('expenses')
                ->where('branch_id', $my_branch_id)
                ->whereDate($exp_date_col, '>=', $start_date)
                ->whereDate($exp_date_col, '<=', $end_date)
                ->sum('amount');

            $transactions = $transactionsQuery
                ->select('t.*', 'b.branch_name', DB::raw($customerSelect))
                ->orderBy('t.created_at', 'DESC')
                ->get();
        }

        // 5. RETURN VIEW WITH BRANCH NAME
        return view('manager.financial-reports', compact(
            'total_sales', 
            'total_expenses', 
            'transactions', 
            'start_date', 
            'end_date',
            'search',
            'report_generated',
            'branch_name' // <--- IPINASA NA DITO ANG VARIABLE
        ));
    }
}