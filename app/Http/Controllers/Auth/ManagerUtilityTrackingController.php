<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerUtilityTrackingController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. SECURITY: Manager access only
        if (!$user || $user->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $my_branch_id = $user->branch_id;

        // FETCH BRANCH NAME DIRECTLY FROM DATABASE
        // Kung walang branch record, pwedeng kunin mula sa relationship o direktang query:
        $branch = DB::table('branches')->where('id', $my_branch_id)->first();
        $branch_name = $branch->branch_name ?? $branch->name ?? 'Main Branch';

        // 2. FILTER LOGIC: report data is loaded only after both dates are selected.
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');
        foreach (['date_from', 'date_to'] as $dateField) {
            if ($request->filled($dateField) && str_contains(${$dateField}, '/')) {
                ${$dateField} = \Carbon\Carbon::createFromFormat('m/d/Y', ${$dateField}, 'Asia/Manila')->toDateString();
            }
        }
        $report_generated = filled($date_from) && filled($date_to);
        $search = $request->get('search', '');

        // Auto-detect date column for expenses table
        $dateCol = 'created_at';
        if (DB::getSchemaBuilder()->hasColumn('expenses', 'date_logged')) {
            $dateCol = 'date_logged';
        } elseif (DB::getSchemaBuilder()->hasColumn('expenses', 'date')) {
            $dateCol = 'date';
        }

        // Base Query Builder for Expenses filtered by branch
        $query = DB::table('expenses as e')
            ->join('branches as b', 'e.branch_id', '=', 'b.id')
            ->where('e.branch_id', $my_branch_id);

        // ALWAYS apply date range filter kung may pinili sa date picker
        if ($report_generated) {
            $query->whereBetween(DB::raw("DATE(e.{$dateCol})"), [$date_from, $date_to]);
        } else {
            $query->whereRaw('1 = 0');
        }

        // Apply search filter kung may tinype ang user
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('e.expense_type', 'LIKE', "%{$search}%")
                  ->orWhere('e.remarks', 'LIKE', "%{$search}%");
            });
        }

        // Clone query for stats calculation (Kasama na ang Electricity, Water, Rent, at Supplies)
        $statsQuery = clone $query;
        $statsRaw = $statsQuery->select(
            DB::raw("SUM(CASE WHEN LOWER(TRIM(e.expense_type)) LIKE '%elect%' OR LOWER(TRIM(e.expense_type)) LIKE '%meralco%' OR LOWER(TRIM(e.expense_type)) LIKE '%power%' THEN e.amount ELSE 0 END) as electricity"),
            DB::raw("SUM(CASE WHEN LOWER(TRIM(e.expense_type)) LIKE '%water%' OR LOWER(TRIM(e.expense_type)) LIKE '%maynilad%' OR LOWER(TRIM(e.expense_type)) LIKE '%primewater%' THEN e.amount ELSE 0 END) as water"),
            DB::raw("SUM(CASE WHEN LOWER(TRIM(e.expense_type)) LIKE '%rent%' OR LOWER(TRIM(e.expense_type)) LIKE '%renta%' THEN e.amount ELSE 0 END) as rent"),
            DB::raw("SUM(CASE WHEN LOWER(TRIM(e.expense_type)) NOT LIKE '%elect%' AND LOWER(TRIM(e.expense_type)) NOT LIKE '%meralco%' AND LOWER(TRIM(e.expense_type)) NOT LIKE '%power%' AND LOWER(TRIM(e.expense_type)) NOT LIKE '%water%' AND LOWER(TRIM(e.expense_type)) NOT LIKE '%maynilad%' AND LOWER(TRIM(e.expense_type)) NOT LIKE '%primewater%' AND LOWER(TRIM(e.expense_type)) NOT LIKE '%rent%' AND LOWER(TRIM(e.expense_type)) NOT LIKE '%renta%' THEN e.amount ELSE 0 END) as supplies")
        )->first();

        $stats = [
            'electricity' => $statsRaw->electricity ?? 0,
            'water' => $statsRaw->water ?? 0,
            'rent' => $statsRaw->rent ?? 0,
            'supplies' => $statsRaw->supplies ?? 0,
        ];

        // 3. Query for Utility Logs
        $logs = $query->select('e.*', 'b.branch_name', "e.{$dateCol} as date_logged")
            ->orderBy("e.{$dateCol}", 'DESC')
            ->get();

        // Pass branch_name at branchName sa compact para sigurado sa View
        $branchName = $branch_name;

        return view('manager.utility-tracking', compact(
            'logs', 
            'stats', 
            'date_from', 
            'date_to', 
            'search',
            'dateCol',
            'branch_name',
            'branchName'
            ,'report_generated'
        ));
    }
}