<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentProofTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('proof_of_payment')->nullable();
        });
    }

    public function test_customer_can_view_their_payment_proof_from_public_storage(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('payment_proofs/proof.jpg', 'proof-image');
        $transactionId = $this->createTransaction(12, 3, 'payment_proofs/proof.jpg');

        $response = $this->actingAs($this->user(12, 'customer'))
            ->get(route('payment.proof', $transactionId));

        $response->assertOk();
        $this->assertSame('proof-image', $response->streamedContent());
    }

    public function test_customer_cannot_view_another_customers_payment_proof(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('payment_proofs/proof.jpg', 'proof-image');
        $transactionId = $this->createTransaction(12, 3, 'payment_proofs/proof.jpg');

        $this->actingAs($this->user(13, 'customer'))
            ->get(route('payment.proof', $transactionId))
            ->assertForbidden();
    }

    public function test_staff_can_view_only_payment_proofs_for_their_branch(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('payment_proofs/proof.jpg', 'proof-image');
        $transactionId = $this->createTransaction(12, 3, 'payment_proofs/proof.jpg');

        $this->actingAs($this->user(20, 'staff', 3))
            ->get(route('payment.proof', $transactionId))
            ->assertOk();

        $this->actingAs($this->user(21, 'staff', 4))
            ->get(route('payment.proof', $transactionId))
            ->assertForbidden();
    }

    public function test_legacy_bare_payment_proof_filename_is_supported(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('uploads/payments/old-proof.jpg', 'legacy-image');
        $transactionId = $this->createTransaction(12, 3, 'old-proof.jpg');

        $response = $this->actingAs($this->user(12, 'customer'))
            ->get(route('payment.proof', $transactionId));

        $response->assertOk();
        $this->assertSame('legacy-image', $response->streamedContent());
    }

    private function createTransaction(int $userId, int $branchId, string $proofPath): int
    {
        return DB::table('transactions')->insertGetId([
            'user_id' => $userId,
            'branch_id' => $branchId,
            'proof_of_payment' => $proofPath,
        ]);
    }

    private function user(int $id, string $role, ?int $branchId = null): User
    {
        $user = new User();
        $user->forceFill([
            'id' => $id,
            'role' => $role,
            'branch_id' => $branchId,
        ]);

        return $user;
    }
}
