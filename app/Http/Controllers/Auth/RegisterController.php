<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    // Ipakita ang registration form at ipasa ang mga branches
    public function showRegistrationForm()
    {
        $branches = DB::table('branches')->orderBy('branch_name', 'ASC')->get();
        return view('auth.register', compact('branches'));
    }

    // Proseso ng pag-rehistro
    public function register(Request $request)
    {
        // 1. Validation
        $request->validate([
            'fullname' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|max:255',
            'password' => 'required|string|min:8|same:confirm_password',
            'terms' => 'accepted',
        ], [
            'terms.accepted' => 'You must agree to the Terms of Service and Privacy Policy before creating an account.',
        ]);

        // 2. Referral Logic
        $referrerId = null;
        $refCodeInput = strtoupper(trim($request->input('referral_from')));

        if (!empty($refCodeInput)) {
            $referrer = DB::table('users')->where('referral_code', $refCodeInput)->first();
            if ($referrer) {
                $referrerId = $referrer->id;
            }
        }

        // 3. Auto-generate referral code para sa bagong user
        $myNewRef = strtoupper(substr($request->fullname, 0, 3)) . rand(100, 999);

        // 4. Insert User (Tinanggal ang created_at at updated_at para iwas error)
        $userId = DB::table('users')->insertGetId([
            'fullname' => $request->fullname,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'referral_code' => $myNewRef,
            'referred_by' => $referrerId,
            'branch_id' => $request->branch_id,
        ]);

        // 5. Bigayan ng Points (Ginamit ang mas maikling source strings para sakto sa DB length)
        if ($referrerId !== null) {
            // 50 points sa nag-refer
            DB::table('loyalty_points')->insert([
                'user_id' => $referrerId,
                'points_earned' => 50,
                'source' => 'Ref Bonus',
                'created_at' => now(),
            ]);

            // 20 points sa bagong sali
            DB::table('loyalty_points')->insert([
                'user_id' => $userId,
                'points_earned' => 20,
                'source' => 'Welcome Ref',
                'created_at' => now(),
            ]);
        } else {
            // New member bonus kung walang referral
            DB::table('loyalty_points')->insert([
                'user_id' => $userId,
                'points_earned' => 10,
                'source' => 'New Member',
                'created_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', '✨ Registration successful! Account created and points awarded.');
    }
}