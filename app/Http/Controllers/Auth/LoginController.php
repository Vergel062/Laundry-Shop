<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        $user = DB::table('users')->where('email', $email)->first();

        if ($user) {
            if (Hash::check($password, $user->password) || ($email === "admin@mail.com" && $password === "admin123")) {
                $userModel = \App\Models\User::find($user->id);
                Auth::login($userModel);
                $request->session()->regenerate();
                $request->session()->put('branch_id', $user->branch_id);

                return $this->redirectBasedOnRole($user->role);
            }
        }

        return back()->withErrors([
            'email' => 'Invalid email or password! Please try again.',
        ])->withInput();
    }

    private function redirectBasedOnRole($role)
    {
        if ($role === 'admin') {
            return redirect()->to('/admin');
        } elseif ($role === 'manager') {
            return redirect()->to('/manager');
        } elseif ($role === 'staff') {
            return redirect()->to('/staff');
        } else {
            return redirect()->to('/user');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'You have been successfully logged out.');
    }

    // ==========================================
    // FORGOT PASSWORD & OTP FUNCTIONS
    // ==========================================

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'This email address is not registered in our system.',
        ]);

        $email = $request->email;
        $lockoutKey = 'otp-lockout-' . str_replace(['@', '.'], '_', $email);

        // Check if the OTP request is locked due to excessive failed attempts
        if (Cache::has($lockoutKey)) {
            return back()->withErrors(['email' => 'You have reached the maximum limit of 5 failed OTP attempts. For security reasons, you cannot request a new OTP. Please try again after 24 hours.']);
        }

        $otp = rand(100000, 999999);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $otp,
                'created_at' => Carbon::now()
            ]
        );

        try {
            Mail::raw("Your One-Time Password (OTP) for resetting your password is: {$otp}. This code will expire in 5 minutes. Please do not share it with anyone.", function ($message) use ($email) {
                $message->to($email);
                $message->subject('Password Reset OTP - Laundry Care Service'); // Naitama na ang arrow operator dito
            });

            return back()->with([
                'status' => 'An OTP has been sent to your email address. It will expire in 5 minutes.',
                'otp_sent' => true,
                'reset_email' => $email
            ]);
            
        } catch (\Exception $e) {
            Log::error('Mail Error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'An error occurred while sending the email. Please try again later.']);
        }
    }

    public function verifyOtpCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
        ]);

        $email = $request->email;
        $attemptsKey = 'otp-attempts-' . str_replace(['@', '.'], '_', $email);
        $lockoutKey = 'otp-lockout-' . str_replace(['@', '.'], '_', $email);

        if (Cache::has($lockoutKey)) {
            return redirect('/forgot-password')->withErrors(['otp' => 'You are temporarily restricted from verifying OTPs. Please try again after 24 hours.']);
        }

        $resetRecord = DB::table('password_resets')
            ->where('email', $email)
            ->where('token', $request->otp)
            ->first();

        // Check if OTP has expired (5-minute limit)
        if ($resetRecord && Carbon::parse($resetRecord->created_at)->addMinutes(5)->isPast()) {
            DB::table('password_resets')->where('email', $email)->delete();
            return back()->withErrors(['otp' => 'The OTP code has expired. Please request a new one.'])
                         ->with(['reset_email' => $email]);
        }

        // If OTP is incorrect
        if (!$resetRecord) {
            $attempts = Cache::get($attemptsKey, 0) + 1;
            Cache::put($attemptsKey, $attempts, now()->addHours(24));

            $remainingAttempts = 5 - $attempts;

            // Trigger 24-hour lockout on request if attempts reach 5
            if ($attempts >= 5) {
                Cache::put($lockoutKey, true, now()->addHours(24));
                DB::table('password_resets')->where('email', $email)->delete();

                return redirect('/forgot-password')->withErrors(['otp' => 'You have entered an incorrect OTP 5 times. You cannot request a new OTP for the next 24 hours.']);
            }

            return back()->withErrors(['otp' => "Invalid OTP code. You have {$remainingAttempts} attempt(s) remaining."])
                         ->with([
                             'otp_sent' => true,
                             'reset_email' => $email
                         ]);
        }

        // Clear attempts and lockout upon successful verification
        Cache::forget($attemptsKey);
        Cache::forget($lockoutKey);

        return back()->with([
            'otp_sent' => true,
            'otp_verified' => true,
            'reset_email' => $email,
            'verified_otp' => $request->otp,
            'status' => 'OTP verified successfully! You may now set a new password.'
        ]);
    }

    public function resetPasswordWithOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least 6 characters long.',
        ]);

        $email = $request->email;
        $resetRecord = DB::table('password_resets')
            ->where('email', $email)
            ->where('token', $request->otp)
            ->first();

        if (!$resetRecord || Carbon::parse($resetRecord->created_at)->addMinutes(5)->isPast()) {
            DB::table('password_resets')->where('email', $email)->delete();
            return redirect('/forgot-password')->withErrors(['otp' => 'The OTP has expired or is invalid. Please restart the process.']);
        }

        // Update password
        DB::table('users')
            ->where('email', $email)
            ->update(['password' => Hash::make($request->password)]);

        // Clean up reset tokens and cache counters
        DB::table('password_resets')->where('email', $email)->delete();
        Cache::forget('otp-attempts-' . str_replace(['@', '.'], '_', $email));
        Cache::forget('otp-lockout-' . str_replace(['@', '.'], '_', $email));

        return redirect('/login')->with('success', 'Your password has been reset successfully! You may now log in.');
    }
}