<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'order_status' => ['nullable', Rule::in([
                'all',
                'Pending',
                'Washing',
                'Drying',
                'Ready',
                'Claimed',
                'Cancelled',
            ])],
            'payment_status' => ['nullable', Rule::in([
                'all',
                'Paid',
                'Unpaid',
                'Pending Verification',
                'Service Request',
            ])],
            'sort_by' => ['nullable', Rule::in([
                'newest',
                'oldest',
                'name_asc',
                'name_desc',
            ])],
        ]);

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $search = $request->input('search', '');
        $order_status = $request->input('order_status', 'all');
        $payment_status = $request->input('payment_status', 'all');
        $sort_by = $request->input('sort_by', 'newest');
        $report_generated = (bool) ($start_date && $end_date);
        $branch = DB::table('branches')->where('id', $manager->branch_id)->first();
        $branch_name = \App\Support\BranchName::format($branch->branch_name ?? null);

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

        if ($order_status !== 'all') {
            $query->where('t.order_status', $order_status);
        }

        if ($payment_status !== 'all') {
            $query->where('t.payment_status', $payment_status);
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

        $listQuery = $report_generated
            ? $query->select('t.*', 'u.fullname as customer_name', 'b.branch_name')
            : null;

        if ($listQuery) {
            match ($sort_by) {
                'oldest' => $listQuery->orderBy('t.created_at')->orderBy('t.id'),
                'name_asc' => $listQuery->orderBy('u.fullname')->orderByDesc('t.created_at')->orderByDesc('t.id'),
                'name_desc' => $listQuery->orderByDesc('u.fullname')->orderByDesc('t.created_at')->orderByDesc('t.id'),
                default => $listQuery->orderByDesc('t.created_at')->orderByDesc('t.id'),
            };
        }
        $list = $listQuery ? $listQuery->get() : collect();

        return view('manager.transaction_report', compact(
            'start_date',
            'end_date',
            'search',
            'order_status',
            'payment_status',
            'sort_by',
            'stats',
            'list',
            'report_generated',
            'branch_name'
        ));
    }
}
