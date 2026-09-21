<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect('/login');
        }

        $userData = DB::table('users')->select('id', 'fullname', 'email', 'phone', 'profile_pic', 'referral_code')->where('id', $userId)->first();
        $firstName = $userData ? explode(' ', trim($userData->fullname))[0] : 'User';

        $activeOrder = DB::table('transactions')
            ->where('user_id', $userId)
            ->whereNotIn('order_status', ['Claimed', 'Cancelled'])
            ->where('payment_status', '!=', 'Service Request')
            ->orderBy('created_at', 'desc')
            ->first();

        $progressPercent = 0;
        if ($activeOrder) {
            switch ($activeOrder->order_status) {
                case 'Pending': $progressPercent = 25; break;
                case 'Washing': $progressPercent = 50; break;
                case 'Drying':  $progressPercent = 75; break;
                case 'Ready':   $progressPercent = 100; break;
                default: $progressPercent = 0; break;
            }
        }

        $transactions = DB::table('transactions')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $currentMonth = date('m');
        $kiloData = DB::table('transactions')
            ->where('user_id', $userId)
            ->whereMonth('created_at', $currentMonth)
            ->where('order_status', 'Claimed')
            ->sum('weight_kg');
        
        $monthlyKilos = $kiloData ?? 0;

        $pointsData = DB::table('loyalty_points')
            ->where('user_id', $userId)
            ->select(DB::raw('SUM(points_earned - points_redeemed) as balance'))
            ->first();
            
        $currentPoints = $pointsData->balance ?? 0;

        $historyTransactions = DB::table('transactions')
            ->where('user_id', $userId)
            ->whereIn('order_status', ['Claimed', 'Cancelled'])
            ->orderBy('created_at', 'desc')
            ->get();

        $readyOrders = DB::table('transactions')
            ->where('user_id', $userId)
            ->where('order_status', 'Ready')
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingServiceRequests = DB::table('transactions')
            ->where('user_id', $userId)
            ->where('order_status', 'Pending')
            ->where('payment_status', 'Service Request')
            ->orderByDesc('created_at')
            ->get();

        return view('user.dashboard', compact(
            'userData',
            'firstName',
            'activeOrder',
            'progressPercent',
            'transactions',
            'monthlyKilos',
            'currentPoints',
            'historyTransactions',
            'readyOrders',
            'pendingServiceRequests'
        ));
    }

    public function requestService(Request $request)
    {
        $userId = Auth::id();
        $user = DB::table('users')->where('id', $userId)->first();

        if (!$user || $user->role !== 'customer' || !$user->branch_id) {
            return back()->with('error', 'Your account is not assigned to a branch.');
        }

        DB::table('transactions')->insert([
            'user_id' => $userId,
            'staff_id' => $userId,
            'branch_id' => $user->branch_id,
            'ref_number' => 'LC-' . strtoupper(substr(uniqid(), -6)),
            'weight_kg' => 0,
            'service_type' => 'Others',
            'total_amount' => 0,
            'order_status' => 'Pending',
            'payment_status' => 'Service Request',
        ]);

        return redirect()->route('user.dashboard')
            ->with('success', 'Service request sent to your branch.');
    }

    public function cancelServiceRequest($id)
    {
        $updated = DB::table('transactions')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->where('order_status', 'Pending')
            ->where('payment_status', 'Service Request')
            ->update(['order_status' => 'Cancelled']);

        return redirect()->route('user.dashboard')->with(
            $updated ? 'success' : 'error',
            $updated ? 'Service request cancelled.' : 'Only pending requests can be cancelled.'
        );
    }

    public function submitPayment(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect('/login');
        }

        $request->validate([
            'transaction_id'   => 'required|exists:transactions,id',
            'payment_method'   => 'required',
            'proof_of_payment' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'transaction_id.required'   => 'Transaction ID is required.',
            'payment_method.required'   => 'Please select a payment method.',
            'proof_of_payment.required' => 'Please upload proof of payment.',
            'proof_of_payment.image'    => 'File must be an image.',
        ]);

        $imagePath = null;
        if ($request->hasFile('proof_of_payment')) {
            $file = $request->file('proof_of_payment');
            $filename = 'PAY_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/payments'), $filename);
            $imagePath = $filename;
        }

        DB::table('transactions')
            ->where('id', $request->transaction_id)
            ->where('user_id', $userId)
            ->update([
                'payment_method'    => $request->payment_method,
                'payment_reference' => $request->payment_reference ?? null,
                'proof_of_payment'  => $imagePath,
                'payment_status'   => 'Pending',
            ]);

        return back()->with('success', 'Payment submitted successfully!');
    }

    /**
     * Update Profile Info (Fullname, Phone, Email, Profile Picture)
     */
    public function updateProfile(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect('/login');
        }

        // Strict Validation with Custom Messages
        $request->validate([
            'fullname'    => 'required|string|max:255',
            'phone'       => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'email'       => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'fullname.required' => 'Full name cannot be empty.',
            'phone.required'    => 'Phone number is required.',
            'phone.unique'      => 'This phone number is already in use by another account.',
            'email.required'    => 'Email address is required.',
            'email.email'       => 'Please enter a valid email address.',
            'email.unique'      => 'This email address is already in use by another account.',
            'profile_pic.image' => 'The uploaded file must be an image.',
            'profile_pic.max'   => 'Profile picture size must not exceed 2MB.',
        ]);

        $currentUser = DB::table('users')->where('id', $userId)->first();

        // Suriin kung may totoong nabagong data bago mag-update
        $hasTextChanges = ($request->fullname !== $currentUser->fullname) ||
                         ($request->phone !== ($currentUser->phone ?? '')) ||
                         ($request->email !== $currentUser->email);

        $hasFileChange = $request->hasFile('profile_pic');

        if (!$hasTextChanges && !$hasFileChange) {
            return back()->withErrors([
                'fullname' => 'No changes were made to your profile info.',
                'phone'    => 'No changes were made to your profile info.',
                'email'    => 'No changes were made to your profile info.',
            ])->withInput();
        }

        $imagePath = $currentUser->profile_pic;
        $oldImagePath = $currentUser->profile_pic;

        if ($hasFileChange) {
            $imagePath = $request->file('profile_pic')->store('profile_pics', 'public');
        }

        DB::table('users')->where('id', $userId)->update([
            'fullname'    => $request->fullname,
            'phone'       => $request->phone,
            'email'       => $request->email,
            'profile_pic' => $imagePath,
        ]);

        if ($hasFileChange && $oldImagePath && $oldImagePath !== $imagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return back()->with('success', 'Edit profile information successfully.');
    }

    /**
     * Update User Password
     */
    public function updatePassword(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect('/login');
        }

        // Strict Validation for Password Change
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Please enter your current password.',
            'password.required'         => 'Please enter a new password.',
            'password.min'              => 'New password must be at least 6 characters.',
            'password.confirmed'        => 'New password confirmation does not match.',
        ]);

        $user = DB::table('users')->where('id', $userId)->first();

        // Check Current Password Accuracy
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password you entered is incorrect.']);
        }

        // Check kung pareho ang bagong password sa lumang password
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'New password cannot be the same as your current password.']);
        }

        // Update Hashed Password
        DB::table('users')
            ->where('id', $userId)
            ->update([
                'password' => Hash::make($request->password),
            ]);

        return back()->with('success', 'Your password has been updated successfully!');
    }
}