<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentProofController extends Controller
{
    public function show(int $transactionId)
    {
        $viewer = auth()->user();
        $transaction = DB::table('transactions')
            ->where('id', $transactionId)
            ->first();

        abort_unless($viewer && $transaction, 404);

        $canView = match ($viewer->role) {
            'admin' => true,
            'customer' => (int) $transaction->user_id === (int) $viewer->id,
            'staff', 'manager' => (int) $transaction->branch_id === (int) $viewer->branch_id,
            default => false,
        };

        abort_unless($canView, 403);
        abort_if(empty($transaction->proof_of_payment), 404);

        $filename = basename(parse_url($transaction->proof_of_payment, PHP_URL_PATH) ?: '');
        abort_unless(
            preg_match('/\A[A-Za-z0-9][A-Za-z0-9_-]*\.(?:jpe?g|png|gif|webp)\z/i', $filename),
            404
        );

        $disk = Storage::disk('public');
        $candidates = [
            'payment_proofs/' . $filename,
            'uploads/payments/' . $filename,
        ];

        foreach ($candidates as $path) {
            if ($disk->exists($path)) {
                return $disk->response($path, $filename, [
                    'Cache-Control' => 'private, max-age=3600',
                ]);
            }
        }

        $legacyPublicPath = public_path('uploads/payments/' . $filename);
        abort_unless(is_file($legacyPublicPath), 404);

        return response()->file($legacyPublicPath, [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
