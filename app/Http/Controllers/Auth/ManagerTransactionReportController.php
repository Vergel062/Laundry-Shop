<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagerTransactionReportController extends Controller
{
    public function index(Request $request)
    {
        $manager = auth()->user();

        if (!$manager || $manager->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $search = $request->input('search', '');
        $report_generated = (bool) ($start_date && $end_date);
        $branch = DB::table('branches')->where('id', $manager->branch_id)->first();
        $branch_name = $branch->branch_name ?? 'Unknown Branch';

        $query = DB::table('transactions as t')
            ->leftJoin('users as u', 't.user_id', '=', 'u.id')
            ->leftJoin('branches as b', 't.branch_id', '=', 'b.id')
            ->where('t.branch_id', $manager->branch_id);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('u.fullname', 'LIKE', "%{$search}%")
                    ->orWhere('t.ref_number', 'LIKE', "%{$search}%");
            });
        }

        if ($report_generated) {
            $query->whereBetween('t.created_at', [
                $start_date . ' 00:00:00',
                $end_date . ' 23:59:59',
            ]);
        }

        $rawStats = $report_generated
            ? (clone $query)->select(
                DB::raw('COUNT(t.id) as total_trans'),
                DB::raw("SUM(CASE WHEN t.payment_status = 'Paid' THEN t.total_amount ELSE 0 END) as collected"),
                DB::raw("SUM(CASE WHEN t.order_status = 'Claimed' THEN 1 ELSE 0 END) as total_out"),
                DB::raw("SUM(CASE WHEN t.order_status != 'Claimed' THEN 1 ELSE 0 END) as total_in")
            )->first()
            : null;

        $stats = [
            'total_trans' => $rawStats->total_trans ?? 0,
            'collected' => $rawStats->collected ?? 0,
            'total_out' => $rawStats->total_out ?? 0,
            'total_in' => $rawStats->total_in ?? 0,
        ];

        $list = $report_generated
            ? $query->select('t.*', 'u.fullname as customer_name', 'b.branch_name')
                ->orderByDesc('t.created_at')
                ->get()
            : collect();

        return view('manager.transaction_report', compact(
            'start_date',
            'end_date',
            'search',
            'stats',
            'list',
            'report_generated',
            'branch_name'
        ));
    }
}
