<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Security check
        if (!in_array($user->role, ['staff', 'manager', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $branch_id = $user->branch_id;

        // Fetch branch name
        $branch = DB::table('branches')->where('id', $branch_id)->first();
        $branch_name = $branch ? $branch->branch_name : 'Unknown Branch';

        // Managers must choose and apply a complete date range before records are loaded.
        $requires_date_filter = $user->role === 'manager';
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        if (!$requires_date_filter) {
            $start_date = $start_date ?: date('Y-m-d');
            $end_date = $end_date ?: date('Y-m-d');
        }
        $search = $request->input('search', '');
        $report_generated = !$requires_date_filter || ($start_date && $end_date);

        $query = DB::table('expenses')->where('branch_id', $branch_id);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('expense_type', 'LIKE', "%{$search}%")
                  ->orWhere('remarks', 'LIKE', "%{$search}%");
            });
        }

        if ($report_generated) {
            $query->whereBetween('date', [$start_date, $end_date]);
            $expenses = $query->orderBy('date', 'DESC')->orderBy('date_logged', 'DESC')->get();
        } else {
            $expenses = collect();
        }

        // Compute Total for the Summary Card
        $range_total = $report_generated
            ? DB::table('expenses')
                ->where('branch_id', $branch_id)
                ->whereBetween('date', [$start_date, $end_date])
                ->sum('amount')
            : 0;

        $view = $user->role === 'manager' ? 'manager.daily_expenses' : 'staff.daily_expenses';

        return view($view, compact('branch_name', 'expenses', 'start_date', 'end_date', 'search', 'range_total', 'report_generated'));
    }
}