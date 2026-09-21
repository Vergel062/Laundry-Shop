<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class LoyaltyController extends Controller
{
    public function index(Request $request)
    {
        // 1. Filter & Search Logic
        $search = $request->input('search', '');
        
        $usersQuery = DB::table('users as u')
            ->select(
                'u.id', 
                'u.fullname', 
                'u.email', 
                'u.referral_code',
                DB::raw('COALESCE((SELECT SUM(lp.points_earned - lp.points_redeemed) FROM loyalty_points lp WHERE lp.user_id = u.id), 0) as balance')
            )
            ->where('u.role', 'customer');

        if (!empty($search)) {
            $usersQuery->where(function($q) use ($search) {
                $q->where('u.fullname', 'like', "%{$search}%")
                  ->orWhere('u.email', 'like', "%{$search}%")
                  ->orWhere('u.referral_code', 'like', "%{$search}%");
            });
        }

        $customers = $usersQuery->orderBy('balance', 'desc')->get();

        // 2. Stats Summary
        $total_points_awarded = DB::table('loyalty_points')->sum('points_earned') ?? 0;
        $total_active_members = $customers->count();

        return view('admin.loyalty_program', compact('customers', 'search', 'total_points_awarded', 'total_active_members'));
    }

    public function processPoints(Request $request)
    {
        // 1. Validation
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'action'  => 'required|in:add,redeem',
            'amount'  => 'required|numeric|min:1',
            'source'  => 'required|string'
        ]);

        $userId = $request->user_id;
        $action = $request->action;
        $amount = (float) $request->amount;
        $source = $request->source;

        try {
            // 2. Kung magre-redeem, i-check muna kung sapat ang puntos
            if ($action === 'redeem') {
                $currentBalance = DB::table('loyalty_points')
                    ->where('user_id', $userId)
                    ->select(DB::raw('COALESCE(SUM(points_earned - points_redeemed), 0) as balance'))
                    ->value('balance');

                if ($currentBalance < $amount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient points balance! Current balance is ' . number_format($currentBalance) . ' pts.'
                    ], 400);
                }
            }

            // 3. I-save sa database (Walang updated_at para maiwasan ang SQL error)
            DB::transaction(function () use ($userId, $action, $amount, $source) {
                DB::table('loyalty_points')->insert([
                    'user_id'         => $userId,
                    'points_earned'   => ($action === 'add') ? $amount : 0,
                    'points_redeemed' => ($action === 'redeem') ? $amount : 0,
                    'source'          => $source,
                    'created_at'      => now(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Points successfully ' . ($action === 'add' ? 'added' : 'redeemed') . '!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database Error: ' . $e->getMessage()
            ], 500);
        }
    }
}