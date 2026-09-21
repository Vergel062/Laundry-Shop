<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Mail\OrderReadyMail;

class ManagerPaymentVerificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Security check - Para lang sa Manager at Admin
        if (!in_array($user->role, ['manager', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $branch_id = $user->branch_id;
        
        // Get branch name
        $branch = DB::table('branches')->where('id', $branch_id)->first();
        $branch_name = $branch ? $branch->branch_name : 'Unknown Branch';

        // Kunin ang mga input mula sa filter form
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $search = $request->input('search');

        // Kunin ang eksaktong petsa ngayon at ang unang araw ng buwan sa Pilipinas
        $today = Carbon::now('Asia/Manila')->toDateString();
        $firstDayOfMonth = Carbon::now('Asia/Manila')->startOfMonth()->toDateString();

        // KUNG WALANG PINILI NA DATE O SEARCH, GAWING DEFAULT ANG FIRST DAY OF THE MONTH HANGGANG NGAYON
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

        return view('manager.verify_payments', compact(
            'branch_name', 
            'payments', 
            'start_date', 
            'end_date', 
            'search'
        ));
    }

    public function approve(Request $request)
    {
        $request->validate([
            'approve_id' => 'required|exists:transactions,id',
        ]);

        $user = auth()->user();

        // Security check
        if (!in_array($user->role, ['manager', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $currentDateTime = Carbon::now('Asia/Manila');

        // 1. Kunin muna ang transaksyon pati ang email at pangalan ng customer bago i-update
        $transaction = DB::table('transactions as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->select('t.*', 'u.email', 'u.fullname')
            ->where('t.id', $request->approve_id)
            ->where('t.branch_id', $user->branch_id)
            ->first();

        if (!$transaction) {
            return redirect()->back()->with('error', 'Transaction not found.');
        }

        // 2. I-update ang payment status sa database patungong 'Paid' kasama ang tamang petsa ngayon
        DB::table('transactions')
            ->where('id', $request->approve_id)
            ->where('branch_id', $user->branch_id)
            ->update([
                'payment_status' => 'Paid',
                'date_paid' => $currentDateTime,
            ]);

        // 3. Magpadala ng email notification sa customer na aprubado na ang bayad
        if (!empty($transaction->email)) {
            $transaction->payment_status = 'Paid';

            try {
                Mail::to($transaction->email)->send(new OrderReadyMail($transaction));
            } catch (\Exception $e) {
                Log::error('Payment approval email notification failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('manager.verify.payments', ['status' => 'verified'])->with('success', 'Payment approved and email notification sent successfully.');
    }

    // --- SUBMIT PAYMENT METHOD FOR MANAGER (KUNG KAILANGAN MAG-SUBMIT MANUALLY) ---
    public function submitPayment(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'payment_ref' => 'required|string',
            'method' => 'required|string',
            'proof_of_payment' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = auth()->user();

        if (!in_array($user->role, ['manager', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $currentDateTime = Carbon::now('Asia/Manila');

        if ($request->hasFile('proof_of_payment')) {
            $file = $request->file('proof_of_payment');
            $filename = "PAY_" . time() . "_" . $request->transaction_id . "." . $file->getClientOriginalExtension();
            
            $destinationPath = public_path('uploads/payments');
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            if (move_uploaded_file($file->getPathname(), $destinationPath . DIRECTORY_SEPARATOR . $filename)) {
                $target_file = "uploads/payments/" . $filename;

                DB::table('transactions')
                    ->where('id', $request->transaction_id)
                    ->where('user_id', $user->id)
                    ->update([
                        'payment_method' => $request->method,
                        'payment_reference' => $request->payment_ref,
                        'proof_of_payment' => $target_file,
                        'payment_status' => 'Pending Verification',
                        'date_paid' => $currentDateTime,
                    ]);

                return back()->with('success', 'Payment submitted successfully! Please wait for verification.');
            }
        }

        return back()->with('error', 'Upload Error: Could not save the file.');
    }
}