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

class ManagerServiceController extends Controller
{
    /**
     * Ipakita ang Service List para sa Manager.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Security check para sa Manager at Admin
        if (!in_array($user->role, ['manager', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $branch_id = $user->branch_id ?? session('branch_id');
        
        // Kunin ang pangalan ng branch
        $branch = DB::table('branches')->where('id', $branch_id)->first();
        $branch_name = $branch ? $branch->branch_name : 'Unknown Branch';

        // Kunin ang mga customer na nakarehistro sa branch ng manager
        $customers = DB::table('users')
            ->where('role', 'customer')
            ->where('branch_id', $branch_id)
            ->orderBy('fullname', 'asc')
            ->get();

        // Dates setup (Asia/Manila)
        $currentDate = Carbon::now('Asia/Manila')->toDateString();
        $firstDayOfMonth = Carbon::now('Asia/Manila')->startOfMonth()->toDateString();

        // Inputs mula sa Filter / Search
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $search_val = $request->input('search', '');

        // Base Query para sa Service List
        $query = DB::table('transactions as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->where('t.branch_id', $branch_id);

        // Date Range Filter
        if (empty($start_date) || empty($end_date)) {
            $start_date = $firstDayOfMonth;
            $end_date = $currentDate;
        }

        $query->whereBetween('t.created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);

        // Search Filter (Ref # o Name)
        if (!empty($search_val)) {
            $query->where(function ($q) use ($search_val) {
                $q->where('t.ref_number', 'LIKE', "%{$search_val}%")
                  ->orWhere('u.fullname', 'LIKE', "%{$search_val}%");
            });
        }

        // Transactions Result Ordering
        $transactions = $query->select('t.*', 'u.fullname')
            ->orderByRaw("FIELD(t.order_status, 'Claimed', 'Cancelled') ASC")
            ->orderByRaw("(t.payment_status = 'Unpaid') DESC")
            ->orderBy('t.created_at', 'DESC')
            ->get();

        return view('manager.service_list', compact(
            'branch_name',
            'start_date',
            'end_date',
            'search_val',
            'transactions',
            'customers'
        ));
    }

    /**
     * Mag-store ng bagong Order mula sa Manager.
     */
    public function storeOrder(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required',
                'weight' => 'required|numeric',
                'service' => 'required|in:Wash Only,Dry Only,Wash-Dry,Wash-Dry-Fold,Comforter (Wash Only),Comforter (Dry Only),Comforter (Wash-Dry),Comforter (Wash-Dry-Fold)',
                'det_qty' => 'nullable|integer|min:0',
                'soft_qty' => 'nullable|integer|min:0',
                'amount' => 'required|numeric',
                'request_id' => 'nullable|integer|exists:transactions,id',
            ]);

            $manager_id = auth()->id() ?? session('id') ?? session('user_id');
            $my_branch = auth()->user()->branch_id ?? session('branch_id');
            $refNumber = 'LC-' . strtoupper(substr(uniqid(), -6));
            
            $phTime = Carbon::now('Asia/Manila');
            $basePrices = [
                'Wash Only' => 70,
                'Dry Only' => 60,
                'Wash-Dry' => 130,
                'Wash-Dry-Fold' => 170,
                'Comforter (Wash Only)' => 140,
                'Comforter (Dry Only)' => 130,
                'Comforter (Wash-Dry)' => 200,
                'Comforter (Wash-Dry-Fold)' => 240,
            ];
            $weight = (float) $request->weight;
            $amount = $basePrices[$request->service] + (max(0, $weight - 8) * 10);

            DB::transaction(function () use ($request, $manager_id, $my_branch, $refNumber, $phTime, $amount, $weight) {
                $requestQuery = DB::table('transactions')
                    ->where('id', $request->input('request_id'))
                    ->where('branch_id', $my_branch)
                    ->where('user_id', $request->user_id)
                    ->where('order_status', 'Pending')
                    ->where('payment_status', 'Service Request');

                if ($request->filled('request_id') && $requestQuery->exists()) {
                    $requestQuery->update([
                        'staff_id' => $manager_id,
                        'ref_number' => $refNumber,
                        'weight_kg' => $weight,
                        'service_type' => $request->service,
                        'total_amount' => $amount,
                        'payment_status' => 'Unpaid',
                    ]);
                } else {
                    DB::table('transactions')->insert([
                        'user_id' => $request->user_id,
                        'staff_id' => $manager_id,
                        'branch_id' => $my_branch,
                        'ref_number' => $refNumber,
                        'weight_kg' => $weight,
                        'service_type' => $request->service,
                        'total_amount' => $amount,
                        'order_status' => 'Pending',
                        'payment_status' => 'Unpaid',
                        'created_at' => $phTime,
                    ]);
                }

                // Bawasan ang Inventory kung may ginamit na Detergent
                $detQty = $request->input('det_qty', 0);
                if ($detQty > 0) {
                    InventoryHistoryService::adjustByItemName($my_branch, '%Detergent%', -$detQty, 'Manager service order usage', $manager_id);
                }

                // Bawasan ang Inventory kung may ginamit na Fabric Softener (Downy)
                $softQty = $request->input('soft_qty', 0);
                if ($softQty > 0) {
                    InventoryHistoryService::adjustByItemName($my_branch, '%Downy%', -$softQty, 'Manager service order usage', $manager_id);
                }
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Order saved and inventory updated successfully!'
                ]);
            }

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

    /**
     * I-update ang Order Status at Payment Status.
     */
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

        // Magpadala ng Email kapag naging 'Ready' ang order
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

        return redirect()->route('manager.services')->with('success', 'Transaction updated successfully.');
    }

    /**
     * I-print ang Resibo mula sa Manager side.
     */
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