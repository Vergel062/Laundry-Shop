<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\DailyServiceQueueFull;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\LaundrySachetUsage;
use App\Services\DailyServiceQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OrderReadyMail;
use Carbon\Carbon;
use App\Support\TransactionHistoryFilter;

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
        app(DailyServiceQueue::class)->ensureExistingOrdersHaveNumbers((int) $branch_id);
        
        // Kunin ang pangalan ng branch
        $branch = DB::table('branches')->where('id', $branch_id)->first();
        $branch_name = \App\Support\BranchName::format($branch->branch_name ?? null);

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

        TransactionHistoryFilter::applyDateRangeWithActiveOrders($query, $start_date, $end_date);

        // Search Filter (Ref # o Name)
        if (!empty($search_val)) {
            $query->where(function ($q) use ($search_val) {
                $q->where('t.ref_number', 'LIKE', "%{$search_val}%")
                  ->orWhere('u.fullname', 'LIKE', "%{$search_val}%");
            });
        }

        // Keep requests in FIFO order, then show recent service days first while
        // preserving the queue sequence within each day. Completed rows stay last.
        $transactions = $query->select('t.*', 'u.fullname')
            ->orderByRaw("CASE WHEN t.order_status = 'Pending' AND t.payment_status = 'Service Request' THEN 0 WHEN t.order_status = 'Cancelled' OR (t.order_status = 'Claimed' AND t.payment_status = 'Paid') THEN 2 ELSE 1 END ASC")
            ->orderByRaw("CASE WHEN t.order_status = 'Pending' AND t.payment_status = 'Service Request' THEN t.created_at END ASC")
            ->orderByRaw('CASE WHEN t.queue_number IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('t.queue_date', 'DESC')
            ->orderBy('t.queue_number')
            ->orderBy('t.created_at', 'DESC')
            ->orderBy('t.id', 'DESC')
            ->paginate(20)
            ->appends($request->query());

        $sachetUsage = app(LaundrySachetUsage::class);
        $powderItems = $sachetUsage->itemsForBranch((int) $branch_id, 'detergent');
        $softenerItems = $sachetUsage->itemsForBranch((int) $branch_id, 'softener');

        return view('manager.service_list', compact(
            'branch_name',
            'start_date',
            'end_date',
            'search_val',
            'transactions',
            'customers',
            'powderItems',
            'softenerItems'
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
                'weight' => 'required|numeric|min:0',
                'service' => 'required|in:Wash Only,Dry Only,Wash-Dry,Wash-Dry-Fold,Comforter (Wash Only),Comforter (Dry Only),Comforter (Wash-Dry),Comforter (Wash-Dry-Fold)',
                'det_qty' => 'nullable|integer|min:0',
                'soft_qty' => 'nullable|integer|min:0',
                'det_inventory_id' => 'nullable|integer',
                'soft_inventory_id' => 'nullable|integer',
                'request_id' => 'nullable|integer|exists:transactions,id',
            ]);

            $manager_id = auth()->id() ?? session('id') ?? session('user_id');
            $my_branch = auth()->user()->branch_id ?? session('branch_id');
            $refNumber = 'LC-' . strtoupper(substr(uniqid(), -6));
            
            $phTime = Carbon::now('Asia/Manila');
            $weight = (float) $request->weight;
            $detQty = (int) $request->input('det_qty', 0);
            $softQty = (int) $request->input('soft_qty', 0);
            $sachetUsage = app(LaundrySachetUsage::class);
            $amount = $sachetUsage->totalAmount($request->service, $weight, $detQty, $softQty);

            DB::transaction(function () use ($request, $manager_id, $my_branch, $refNumber, $phTime, $amount, $weight, $detQty, $softQty, $sachetUsage) {
                $queue = app(DailyServiceQueue::class)->assignNext((int) $my_branch, $phTime);
                $requestQuery = DB::table('transactions')
                    ->where('id', $request->input('request_id'))
                    ->where('branch_id', $my_branch)
                    ->where('user_id', $request->user_id)
                    ->where('order_status', 'Pending')
                    ->where('payment_status', 'Service Request');

                if ($request->filled('request_id')) {
                    $serviceRequest = $requestQuery->lockForUpdate()->first();
                    if (!$serviceRequest) {
                        throw new \InvalidArgumentException('The service request is no longer pending or does not belong to this branch.');
                    }

                    $requestQuery->update([
                        'staff_id' => $manager_id,
                        'ref_number' => $refNumber,
                        'weight_kg' => $weight,
                        'service_type' => $request->service,
                        'total_amount' => $amount,
                        'payment_status' => 'Unpaid',
                        'queue_date' => $queue['queue_date'],
                        'queue_number' => $queue['queue_number'],
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
                        'queue_date' => $queue['queue_date'],
                        'queue_number' => $queue['queue_number'],
                        'created_at' => $phTime,
                    ]);
                }

                $sachetUsage->consume(
                    (int) $my_branch,
                    $request->filled('det_inventory_id') ? (int) $request->input('det_inventory_id') : null,
                    $detQty,
                    $request->filled('soft_inventory_id') ? (int) $request->input('soft_inventory_id') : null,
                    $softQty,
                    'Manager service order usage',
                    $manager_id ? (int) $manager_id : null
                );
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Service added and inventory updated successfully!'
                ]);
            }

            return redirect()->back()->with('success', 'Service added successfully!');

        } catch (DailyServiceQueueFull $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->validator->errors()->first(),
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
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
            'new_status' => 'required|in:Pending,Washing,Drying,Ready,Claimed',
            'payment_status' => 'required|in:Unpaid,Paid',
        ]);

        $user = auth()->user();
        if (!$user || !in_array($user->role, ['manager', 'admin'], true)) {
            abort(403, 'Unauthorized action.');
        }

        $result = DB::transaction(function () use ($request, $user) {
            $transaction = DB::table('transactions')
                ->where('id', $request->transaction_id)
                ->where('branch_id', $user->branch_id)
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                return ['error' => 'Transaction not found.'];
            }

            if ($transaction->order_status === 'Claimed' && $transaction->payment_status === 'Paid') {
                return ['error' => 'Completed transactions cannot be changed.'];
            }

            if ($transaction->payment_status === 'Paid' && $request->payment_status === 'Unpaid') {
                return ['error' => 'A paid transaction cannot be changed back to unpaid.'];
            }

            DB::table('transactions')
                ->where('id', $request->transaction_id)
                ->update([
                    'order_status' => $request->new_status,
                    'payment_status' => $request->payment_status,
                ]);

            return [
                'transaction' => $transaction,
                'old_status' => $transaction->order_status,
            ];
        });

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        $transaction = $result['transaction'];
        $old_status = $result['old_status'];
        $customer = DB::table('users')
            ->where('id', $transaction->user_id)
            ->first(['email', 'fullname']);

        // Magpadala ng Email kapag naging 'Ready' ang order
        if ($request->new_status === 'Ready' && $old_status !== 'Ready') {
            if (!empty($customer->email)) {
                $transaction->email = $customer->email;
                $transaction->fullname = $customer->fullname;
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

    public function cancelServiceRequest($id)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $validated = request()->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $updated = DB::table('transactions')
            ->where('id', $id)
            ->where('branch_id', $user->branch_id)
            ->where('order_status', 'Pending')
            ->where('payment_status', 'Service Request')
            ->update([
                'order_status' => 'Cancelled',
                'service_request_rejection_reason' => $validated['reason'],
                'service_request_rejection_read_at' => null,
                'service_request_rejection_deleted_at' => null,
            ]);

        return redirect()->route('manager.services')->with(
            $updated ? 'success' : 'error',
            $updated ? 'Service request rejected and customer notified.' : 'Only pending service requests from your branch can be rejected.'
        );
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

        $paymentSettings = LandingContentController::paymentSettings();

        return view('staff.print_receipt', compact('data', 'paymentSettings'));
    }
}