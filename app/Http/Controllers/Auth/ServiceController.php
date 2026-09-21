<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryHistoryService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OrderReadyMail;
use Carbon\Carbon;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Security check kung staff o angkop ang role
        if (!in_array($user->role, ['staff', 'manager', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $branch_id = $user->branch_id ?? session('branch_id');
        
        // Kunin ang pangalan ng branch
        $branch = DB::table('branches')->where('id', $branch_id)->first();
        $branch_name = $branch ? $branch->branch_name : 'Unknown Branch';

        // Kunin ang mga customer na nakarehistro sa tamang branch
        $customers = DB::table('users')
            ->where('role', 'customer')
            ->where('branch_id', $branch_id)
            ->orderBy('fullname', 'asc')
            ->get();

        // Kunin ang petsa ngayon at ang unang araw ng kasalukuyang buwan ayon sa Pilipinas
        $currentDate = Carbon::now('Asia/Manila')->toDateString();
        $firstDayOfMonth = Carbon::now('Asia/Manila')->startOfMonth()->toDateString();

        // Kunin ang mga input mula sa filter form o URL
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $search_val = $request->input('search', '');

        // Base Query para sa Service List
        $query = DB::table('transactions as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->where('t.branch_id', $branch_id);

        // KUNG WALANG SPECIFIC NA DATE NA PINILI ANG USER (Default: First day of the month hanggang ngayon)
        if (empty($start_date) || empty($end_date)) {
            $start_date = $firstDayOfMonth;
            $end_date = $currentDate;

            // Ipinapakita ang mga transaksyon simula unang araw ng buwan hanggang ngayon
            $query->whereBetween('t.created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        } else {
            // Kung gumamit ng date filter range
            $query->whereBetween('t.created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        }

        // Filter para sa Search (Reference number o Pangalan ng Customer)
        if (!empty($search_val)) {
            $query->where(function ($q) use ($search_val) {
                $q->where('t.ref_number', 'LIKE', "%{$search_val}%")
                  ->orWhere('u.fullname', 'LIKE', "%{$search_val}%");
            });
        }

        // PAG-AYOS NG PRIORITY
        $transactions = $query->select('t.*', 'u.fullname')
            ->orderByRaw("FIELD(t.order_status, 'Claimed', 'Cancelled') ASC")
            ->orderByRaw("(t.payment_status = 'Unpaid') DESC")
            ->orderBy('t.created_at', 'DESC')
            ->get();

        return view('staff.service_list', compact(
            'branch_name',
            'start_date',
            'end_date',
            'search_val',
            'transactions',
            'customers' // <-- Ipinaloob na rito ang $customers variable
        ));
    }

    public function storeOrder(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required',
                'weight' => 'required|numeric',
                'service' => 'required',
                'det_qty' => 'nullable|integer|min:0',
                'soft_qty' => 'nullable|integer|min:0',
                'amount' => 'required|numeric',
            ]);

            $staff_id = auth()->id() ?? session('id') ?? session('user_id') ?? 1;
            $my_branch = auth()->user()->branch_id ?? session('branch_id') ?? 1;
            $refNumber = 'LC-' . strtoupper(substr(uniqid(), -6));
            
            $phTime = Carbon::now('Asia/Manila');

            DB::transaction(function () use ($request, $staff_id, $my_branch, $refNumber, $phTime) {
                DB::table('transactions')->insert([
                    'user_id' => $request->user_id,
                    'staff_id' => $staff_id,
                    'branch_id' => $my_branch,
                    'ref_number' => $refNumber,
                    'weight_kg' => $request->weight,
                    'service_type' => $request->service,
                    'total_amount' => $request->amount,
                    'order_status' => 'Pending',
                    'payment_status' => 'Unpaid',
                    'created_at' => $phTime,
                ]);

                $detQty = $request->input('det_qty', 0);
                if ($detQty > 0) {
                    InventoryHistoryService::adjustByItemName($my_branch, '%Detergent%', -$detQty, 'Staff service order usage', $staff_id);
                }

                $softQty = $request->input('soft_qty', 0);
                if ($softQty > 0) {
                    InventoryHistoryService::adjustByItemName($my_branch, '%Downy%', -$softQty, 'Staff service order usage', $staff_id);
                }
            });

            // Kung AJAX request
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Order saved and inventory updated successfully!'
                ]);
            }

            // Kung karaniwang Form Submit / Blade submission
            return redirect()->back()->with('success', 'New service order added successfully!');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to save order: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'new_status' => 'required|string',
            'payment_status' => 'required|string',
        ]);

        $transaction = DB::table('transactions as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->select('t.*', 'u.email', 'u.fullname')
            ->where('t.id', $request->transaction_id)
            ->first();

        if (!$transaction) {
            return redirect()->back()->with('error', 'Transaction not found.');
        }

        $old_status = $transaction->order_status;

        DB::table('transactions')
            ->where('id', $request->transaction_id)
            ->update([
                'order_status' => $request->new_status,
                'payment_status' => $request->payment_status,
            ]);

        if ($request->new_status === 'Ready' && $old_status !== 'Ready') {
            if (!empty($transaction->email)) {
                $transaction->payment_status = $request->payment_status;

                try {
                    Mail::to($transaction->email)->send(new OrderReadyMail($transaction));
                } catch (\Exception $e) {
                    Log::error('Email notification failed: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('staff.services')->with('success', 'Transaction updated successfully.');
    }

    public function printReceipt($id)
    {
        $data = DB::table('transactions as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->leftJoin('branches as b', 't.branch_id', '=', 'b.id')
            ->select('t.*', 'u.fullname', 'b.location as branch_location')
            ->where('t.id', $id)
            ->first();

        if (!$data) {
            abort(404, 'Error: Hindi nahanap ang order.');
        }

        return view('staff.print_receipt', compact('data'));
    }
}