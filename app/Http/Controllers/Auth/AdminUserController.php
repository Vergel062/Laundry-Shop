<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        // Auto-Delete Policy: Permanenteng burahin ang mga archived accounts pagkalipas ng 30 araw
        DB::table('users')
            ->where('is_archived', 1)
            ->whereNotNull('archive_date')
            ->where('archive_date', '<', now()->subDays(30))
            ->delete();

        $is_edit = false;
        $edit_data = null;

        if ($request->has('edit_id')) {
            $is_edit = true;
            $edit_data = DB::table('users')->where('id', $request->edit_id)->first();
        }

        // Kunin ang active users kasama ang branch name
        $selectedFilter = $request->input('filter', 'all');
        $userSearch = trim((string) $request->input('user_search', ''));

        $usersQuery = DB::table('users as u')
            ->leftJoin('branches as b', 'u.branch_id', '=', 'b.id')
            ->select('u.*', 'b.branch_name')
            ->where('u.is_archived', 0)
            ->orderBy('u.id', 'desc');

        if (str_starts_with($selectedFilter, 'role:')) {
            $role = substr($selectedFilter, 5);
            if (in_array($role, ['admin', 'staff', 'manager', 'customer'], true)) {
                $usersQuery->where('u.role', $role);
            } else {
                $selectedFilter = 'all';
            }
        } elseif (str_starts_with($selectedFilter, 'branch:')) {
            $branchId = substr($selectedFilter, 7);
            if (ctype_digit($branchId)) {
                $usersQuery->where('u.branch_id', (int) $branchId);
            } else {
                $selectedFilter = 'all';
            }
        } elseif ($selectedFilter !== 'all') {
            $selectedFilter = 'all';
        }

        if ($userSearch !== '') {
            $usersQuery->where(function ($query) use ($userSearch) {
                $query->where('u.fullname', 'LIKE', '%' . $userSearch . '%')
                    ->orWhere('u.email', 'LIKE', '%' . $userSearch . '%')
                    ->orWhere('u.phone', 'LIKE', '%' . $userSearch . '%');
            });
        }

        $users = $usersQuery->get();

        $branches = DB::table('branches')->orderBy('branch_name')->get();

        return view('admin.manage_users', compact('users', 'branches', 'is_edit', 'edit_data', 'selectedFilter', 'userSearch'));
    }

    public function history(Request $request, $id)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $user = DB::table('users as u')
            ->leftJoin('branches as b', 'u.branch_id', '=', 'b.id')
            ->select('u.id', 'u.fullname', 'u.email', 'u.phone', 'u.role', 'b.branch_name')
            ->where('u.id', $id)
            ->where('u.is_archived', 0)
            ->first();

        if (!$user) {
            abort(404, 'User not found.');
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $hasDateFilter = $request->boolean('filter') && $startDate && $endDate;

        $historyQuery = DB::table('transactions as t')
            ->leftJoin('branches as b', 't.branch_id', '=', 'b.id')
            ->select(
                't.id',
                't.ref_number',
                't.service_type',
                't.weight_kg',
                't.total_amount',
                't.order_status',
                't.payment_status',
                't.payment_method',
                't.created_at',
                'b.branch_name'
            )
            ->where('t.user_id', $user->id);

        if ($hasDateFilter) {
            $historyQuery->whereBetween('t.created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ]);
        } else {
            $historyQuery->whereRaw('1 = 0');
        }

        $history = $historyQuery
            ->orderByDesc('t.created_at')
            ->get();

        $historyTotal = $history->sum('total_amount');

        return view('admin.user_history', compact(
            'user',
            'history',
            'historyTotal',
            'startDate',
            'endDate',
            'hasDateFilter'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:customer,staff,manager,admin',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        DB::table('users')->insert([
            'fullname' => $request->fullname,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'branch_id' => in_array($request->role, ['staff', 'customer', 'manager']) ? $request->branch_id : null,
        ]);

        return redirect()->route('admin.manage.users')->with('msg', 'created');
    }

    public function update(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email,' . $request->user_id,
            'role' => 'required|in:customer,staff,manager,admin',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        DB::table('users')
            ->where('id', $request->user_id)
            ->update([
                'fullname' => $request->fullname,
                'phone' => $request->phone,
                'email' => $request->email,
                'role' => $request->role,
                'branch_id' => in_array($request->role, ['staff', 'customer', 'manager']) ? $request->branch_id : null,
            ]);

        return redirect()->route('admin.manage.users')->with('msg', 'updated');
    }

    public function archive($id)
    {
        if (auth()->id() == $id) {
            return redirect()->route('admin.manage.users')->with('msg', 'self_delete');
        }

        DB::table('users')
            ->where('id', $id)
            ->update([
                'is_archived' => 1,
                'archive_date' => now(),
            ]);

        return redirect()->route('admin.manage.users')->with('msg', 'archived');
    }
}