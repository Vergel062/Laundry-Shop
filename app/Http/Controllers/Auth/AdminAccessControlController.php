<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAccessControlController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Unauthorized action.');

        $staff = DB::table('users as u')
            ->leftJoin('branches as b', 'u.branch_id', '=', 'b.id')
            ->where('u.role', 'staff')
            ->where(function ($query) {
                $query->whereNull('u.is_archived')
                    ->orWhere('u.is_archived', false);
            })
            ->orderBy('u.fullname')
            ->get(['u.id', 'u.fullname', 'u.email', 'u.can_view_reports', 'b.branch_name']);

        return view('admin.access_control', compact('staff'));
    }

    public function update(Request $request, int $id)
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Unauthorized action.');

        $validated = $request->validate([
            'can_view_reports' => ['required', 'boolean'],
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
            ->update(['can_view_reports' => (bool) $validated['can_view_reports']]);

        return redirect()->route('admin.access-control')
            ->with('success', 'Staff report access updated.');
    }
}
