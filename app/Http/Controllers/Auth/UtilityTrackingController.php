<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UtilityTrackingController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('login');
        }

        // 1. FILTER SETTINGS: report data is loaded only after both dates are selected.
        $selected_branch = $request->input('branch_id', 'all');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        foreach (['date_from', 'date_to'] as $dateField) {
            if ($request->filled($dateField) && str_contains(${$dateField}, '/')) {
                ${$dateField} = Carbon::createFromFormat('m/d/Y', ${$dateField}, 'Asia/Manila')->toDateString();
            }
        }
        $report_generated = filled($date_from) && filled($date_to);
        $search = $request->input('search', '');

        // 2. BASE QUERY PARA SA TABLE LOGS (May alias na 'e')
        $query = DB::table('expenses as e')
            ->join('branches as b', 'e.branch_id', '=', 'b.id')
            ->select('e.*', 'b.branch_name');

        if ($selected_branch !== 'all') {
            $query->where('e.branch_id', intval($selected_branch));
        }

        // Gamitin ang date_logged na may alias na e.
        if ($report_generated) {
            $query->whereDate('e.date_logged', '>=', $date_from)
                  ->whereDate('e.date_logged', '<=', $date_to);
        } else {
            $query->whereRaw('1 = 0');
        }

        // Search keyword filter kung meron man
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('e.expense_type', 'LIKE', "%{$search}%")
                  ->orWhere('e.remarks', 'LIKE', "%{$search}%")
                  ->orWhere('b.branch_name', 'LIKE', "%{$search}%");
            });
        }

        $logs = $query->orderBy('e.date_logged', 'desc')->get();

        // 3. INDEPENDENT TOTALS PARA SA STAT CARDS (Walang alias para diretsong gumana)
        $totalsQuery = DB::table('expenses');
        if ($report_generated) {
            $totalsQuery->whereDate('date_logged', '>=', $date_from)
                        ->whereDate('date_logged', '<=', $date_to);
        } else {
            $totalsQuery->whereRaw('1 = 0');
        }

        if ($selected_branch !== 'all') {
            $totalsQuery->where('branch_id', intval($selected_branch));
        }

        $allExpenses = $totalsQuery->get();

        $electricity_total = $allExpenses->filter(fn($i) => stripos($i->expense_type, 'elect') !== false || stripos($i->expense_type, 'meralco') !== false)->sum('amount');
        $water_total       = $allExpenses->filter(fn($i) => stripos($i->expense_type, 'water') !== false)->sum('amount');
        $rent_total        = $allExpenses->filter(fn($i) => stripos($i->expense_type, 'rent') !== false)->sum('amount');
        $supplies_total    = $allExpenses->filter(fn($i) => stripos($i->expense_type, 'suppl') !== false)->sum('amount');

        // Branches Dropdown
        $branches_dropdown = DB::table('branches')->orderBy('branch_name', 'asc')->get();
        $selected_branch_name = $selected_branch !== 'all'
            ? optional($branches_dropdown->firstWhere('id', $selected_branch))->branch_name
            : null;

        return view('admin.utility_tracking', compact(
            'selected_branch', 
            'selected_branch_name',
            'report_generated',
            'date_from', 
            'date_to', 
            'search', 
            'logs', 
            'electricity_total', 
            'water_total', 
            'rent_total',
            'supplies_total',
            'branches_dropdown'
        ));
    }
}