<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAccessControlController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Unauthorized action.');

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);
        $selectedBranchId = $validated['branch_id'] ?? null;

        $branches = DB::table('branches')
            ->whereNull('archive_date')
            ->orderBy('branch_name')
            ->get(['id', 'branch_name']);

        $staffQuery = DB::table('users as u')
            ->leftJoin('branches as b', 'u.branch_id', '=', 'b.id')
            ->where('u.role', 'staff')
            ->where(function ($query) {
                $query->whereNull('u.is_archived')
                    ->orWhere('u.is_archived', false);
            });

        if ($selectedBranchId !== null) {
            $staffQuery->where('u.branch_id', $selectedBranchId);
        }

        $staff = $staffQuery->orderBy('b.branch_name')
            ->orderBy('u.fullname')
            ->get(['u.id', 'u.fullname', 'u.email', 'u.branch_id', 'u.can_view_reports', 'u.can_verify_payments', 'b.branch_name']);

        return view('admin.access_control', compact('staff', 'branches', 'selectedBranchId'));
    }

    public function update(Request $request, int $id)
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Unauthorized action.');

        $validated = $request->validate([
            'can_view_reports' => ['required', 'boolean'],
            'can_verify_payments' => ['required', 'boolean'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $staffMember = DB::table('users')
            ->where('id', $id)
            ->where('role', 'staff')
            ->where(function ($query) {
                $query->whereNull('is_archived')
                    ->orWhere('is_archived', false);
            })
            ->first(['id']);

        if (!$staffMember) {
            abort(404, 'Staff account not found.');
        }

        DB::table('users')->where('id', $staffMember->id)
            ->update([
                'can_view_reports' => (bool) $validated['can_view_reports'],
                'can_verify_payments' => (bool) $validated['can_verify_payments'],
            ]);

        return redirect()->route('admin.access-control', array_filter([
            'branch_id' => $validated['branch_id'] ?? null,
        ], static fn ($value) => $value !== null))
            ->with('success', 'Staff access updated.');
    }
}
