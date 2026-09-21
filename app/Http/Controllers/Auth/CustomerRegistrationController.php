<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Security: Only a logged-in Manager can register branch accounts.
        if (!$user || $user->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        // 2. Fetch branch info of the logged-in user
        $branch_id = $user->branch_id ?? 0;
        $branch = DB::table('branches')->where('id', $branch_id)->first();
        $branch_name = $branch ? $branch->branch_name : 'Unknown Branch';

        $selectedRole = $request->input('user_type', 'all');
        if (!in_array($selectedRole, ['all', 'staff', 'customer'], true)) {
            $selectedRole = 'all';
        }
        $userSearch = trim((string) $request->input('user_search', ''));

        // Fetch active staff and customers from this branch only.
        $usersQuery = DB::table('users as u')
            ->leftJoin('loyalty_points as lp', 'u.id', '=', 'lp.user_id')
            ->select(
                'u.id',
                'u.fullname', 
                'u.phone', 
                'u.email',
                'u.role',
                'u.referral_code', 
                DB::raw('COALESCE(SUM(lp.points_earned - lp.points_redeemed), 0) as total_points')
            )
            ->whereIn('u.role', ['staff', 'customer'])
            ->where('u.branch_id', $branch_id)
            ->groupBy('u.id', 'u.fullname', 'u.phone', 'u.email', 'u.role', 'u.referral_code')
            ->orderBy('u.fullname', 'ASC')
            ;

        if ($selectedRole !== 'all') {
            $usersQuery->where('u.role', $selectedRole);
        }

        if ($userSearch !== '') {
            $usersQuery->where(function ($query) use ($userSearch) {
                $query->where('u.fullname', 'LIKE', '%' . $userSearch . '%')
                    ->orWhere('u.email', 'LIKE', '%' . $userSearch . '%')
                    ->orWhere('u.phone', 'LIKE', '%' . $userSearch . '%');
            });
        }

        $users_result = $usersQuery->get();

        return view('manager.register-customer', compact(
            'branch_name',
            'users_result',
            'selectedRole',
            'userSearch'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $branch_id = $user->branch_id ?? 0;

        $request->validate([
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'role' => 'required|in:staff,customer',
        ]);

        $fullname = $request->input('fullname');
        $phone = $request->input('phone');
        $email = $request->input('email');
        $hashed_password = Hash::make('user12345');
        $ref_code = "LC-" . strtoupper(substr(md5(uniqid()), 0, 5));

        // Check duplicate phone or email
        $existingCheck = DB::table('users')
            ->where('phone', $phone)
            ->orWhere(function($query) use ($email) {
                if (!empty($email)) {
                    $query->where('email', '!=', '')->where('email', $email);
                }
            })
            ->exists();

        if ($existingCheck) {
            return redirect()->route('manager.register.customer')->with('status_msg', 'error_exists');
        }

        // Insert new user
        $new_user_id = DB::table('users')->insertGetId([
            'fullname' => $fullname,
            'phone' => $phone,
            'email' => $email,
            'password' => $hashed_password,
            'role' => $request->input('role'),
            'branch_id' => $branch_id,
            'referral_code' => $request->input('role') === 'customer' ? $ref_code : null,
        ]);

        if ($new_user_id) {
            if ($request->input('role') === 'customer') {
                DB::table('loyalty_points')->insert([
                    'user_id' => $new_user_id,
                    'points_earned' => 20,
                    'points_redeemed' => 0,
                    'source' => 'Welcome Bonus',
                ]);
            }

            return redirect()->route('manager.register.customer')->with('status_msg', 'success');
        }

        return redirect()->route('manager.register.customer')->with('status_msg', 'error_system');
    }
}