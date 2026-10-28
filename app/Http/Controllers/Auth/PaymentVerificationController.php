<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Mail\OrderReadyMail;

class PaymentVerificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Security check
        if (!in_array($user->role, ['staff', 'manager', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $branch_id = $user->branch_id;
        
        // Get branch name
        $branch = DB::table('branches')->where('id', $branch_id)->first();
        $branch_name = \App\Support\BranchName::format($branch->branch_name ?? null);

        // Kunin ang mga input mula sa filter form
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $search = $request->input('search');

        // Kunin ang eksaktong petsa ngayon at ang unang araw ng buwan sa Pilipinas
        $today = Carbon::now('Asia/Manila')->toDateString();
        $firstDayOfMonth = Carbon::now('Asia/Manila')->startOfMonth()->toDateString();

        // KUNG WALANG PINILIT NA DATE O SEARCH, GAWIN NING DEFAULT ANG FIRST DAY OF THE MONTH HANGGANG NGAYON
        if (empty($start_date) && empty($end_date) && empty($search)) {
            $start_date = $firstDayOfMonth;
            $end_date = $today;
        }

        // Base Query para sa Payments
        $query = DB::table('transactions as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->where('t.branch_id', $branch_id)
            ->whereNotNull('t.payment_method')
            ->where('t.payment_method', '!=', 'Cash')
            ->select('t.*', 'u.fullname');

        // LOGIC: Isasama ang mga 'Pending Verification' kahit anong petsa, pati ang mga nasa saklaw ng date ngayon o filter
        if (!empty($start_date) && !empty($end_date)) {
            $query->where(function($q) use ($start_date, $end_date) {
                $q->where('t.payment_status', 'Pending Verification')
                  ->orWhereBetween('t.created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            });
        } elseif (!empty($start_date)) {
            $query->where(function($q) use ($start_date) {
                $q->where('t.payment_status', 'Pending Verification')
                  ->orWhere('t.created_at', '>=', $start_date . ' 00:00:00');
            });
        } elseif (!empty($end_date)) {
            $query->where(function($q) use ($end_date) {
                $q->where('t.payment_status', 'Pending Verification')
                  ->orWhere('t.created_at', '<=', $end_date . ' 23:59:59');
            });
        }

        // Filter para sa Search (Reference number o Pangalan ng Customer)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('t.ref_number', 'LIKE', "%{$search}%")
                  ->orWhere('u.fullname', 'LIKE', "%{$search}%")
                  ->orWhere('t.payment_reference', 'LIKE', "%{$search}%");
            });
        }

        // SORTING LOGIC: Uunahin ang 'Pending Verification' sa taas, sunod ang iba
        $payments = $query->orderByRaw("CASE WHEN t.payment_status = 'Pending Verification' THEN 1 ELSE 2 END")
                          ->orderBy('t.created_at', 'DESC')
                          ->get();

        return view('staff.verify_payments', compact(
            'branch_name', 
            'payments', 
            'start_date', 
            'end_date', 
            'search'
        ));
    }

    public function approve(Request $request)
    {
        $validated = $request->validate([
            'approve_id' => 'required|integer|exists:transactions,id',
        ]);

        $user = auth()->user();
        if ($user->role !== 'staff' || !$user->branch_id) {
            abort(403, 'Unauthorized action.');
        }

        $currentDateTime = Carbon::now('Asia/Manila');

        $transaction = DB::transaction(function () use ($validated, $user, $currentDateTime) {
            $transaction = DB::table('transactions as t')
                ->join('users as u', 't.user_id', '=', 'u.id')
                ->select('t.*', 'u.email', 'u.fullname')
                ->where('t.id', $validated['approve_id'])
                ->where('t.branch_id', $user->branch_id)
                ->whereIn('t.payment_status', ['Pending', 'Pending Verification'])
                ->whereNotNull('t.proof_of_payment')
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                return null;
            }

            $updated = DB::table('transactions')
                ->where('id', $transaction->id)
                ->where('branch_id', $user->branch_id)
                ->whereIn('payment_status', ['Pending', 'Pending Verification'])
                ->whereNotNull('proof_of_payment')
                ->update([
                    'payment_status' => 'Paid',
                    'date_paid' => $currentDateTime,
                    'payment_rejection_reason' => null,
                ]);

            return $updated ? $transaction : null;
        });

        if (!$transaction) {
            return back()->with('error', 'Payment is no longer awaiting verification or does not belong to your branch.');
        }

        if (!empty($transaction->email)) {
            $transaction->payment_status = 'Paid';

            try {
                Mail::to($transaction->email)->send(new OrderReadyMail($transaction));
            } catch (\Exception $e) {
                Log::error('Payment approval email notification failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('staff.verify.payments', ['status' => 'verified'])->with('success', 'Payment approved and email notification sent successfully.');
    }

    public function reject(Request $request)
    {
        $validated = $request->validate([
            'reject_id' => 'required|integer|exists:transactions,id',
            'rejection_reason' => 'required|string|max:500',
        ]);

        $user = auth()->user();
        if ($user->role !== 'staff' || !$user->branch_id) {
            abort(403, 'Unauthorized action.');
        }

        $transaction = DB::transaction(function () use ($validated, $user) {
            $transaction = DB::table('transactions as t')
                ->join('users as u', 't.user_id', '=', 'u.id')
                ->select('t.*', 'u.email', 'u.fullname')
                ->where('t.id', $validated['reject_id'])
                ->where('t.branch_id', $user->branch_id)
                ->whereIn('t.payment_status', ['Pending', 'Pending Verification'])
                ->whereNotNull('t.proof_of_payment')
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                return null;
            }

            DB::table('transactions')
                ->where('id', $transaction->id)
                ->update([
                    'payment_status' => 'Unpaid',
                    'payment_rejection_reason' => $validated['rejection_reason'],
                    'date_paid' => null,
                ]);

            return $transaction;
        });

        if (!$transaction) {
            return back()->with('error', 'Payment is no longer awaiting verification or does not belong to your branch.');
        }

        if (!empty($transaction->email)) {
            try {
                Mail::raw(
                    "Your payment for order {$transaction->ref_number} was not approved.\n\nReason: {$validated['rejection_reason']}\n\nPlease review the reason and submit a new payment proof from your account.",
                    fn ($mail) => $mail->to($transaction->email)
                        ->subject('Payment proof needs correction - LaundryCare')
                );
            } catch (\Exception $e) {
                Log::error('Payment rejection email notification failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('staff.verify.payments')
            ->with('success', 'Payment rejected. The customer can submit a new proof of payment.');
    }

    // --- FINAL SUBMIT PAYMENT METHOD ---
    public function submitPayment(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'payment_ref' => 'required|string',
            'method' => 'required|string',
            'proof_of_payment' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = auth()->user();
        $currentDateTime = Carbon::now('Asia/Manila');

        if ($request->hasFile('proof_of_payment')) {
            $targetFile = $request->file('proof_of_payment')->store('payment_proofs', 'public');

            if (!$targetFile) {
                return back()->with('error', 'Upload Error: Could not save the payment proof.');
            }

            DB::table('transactions')
                ->where('id', $request->transaction_id)
                ->where('user_id', $user->id)
                ->update([
                    'payment_method' => $request->method,
                    'payment_reference' => $request->payment_ref,
                    'proof_of_payment' => $targetFile,
                    'payment_status' => 'Pending Verification',
                    'date_paid' => $currentDateTime,
                    'payment_rejection_reason' => null,
                ]);

            return back()->with('success', 'Payment submitted successfully! Please wait for verification.');
        }

        return back()->with('error', 'Upload Error: Could not save the file.');
    }
}