<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentRejectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('transactions');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('email');
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('ref_number');
            $table->string('payment_status');
            $table->string('proof_of_payment')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('payment_rejection_reason')->nullable();
            $table->dateTime('date_paid')->nullable();
        });

        DB::table('users')->insert([
            'id' => 10,
            'fullname' => 'Laundry Customer',
            'email' => 'customer@example.com',
        ]);
    }

    public function test_staff_can_reject_a_pending_payment_for_their_branch(): void
    {
        Mail::fake();
        $transactionId = $this->createPendingTransaction(3);

        $response = $this->actingAs($this->user(20, 'staff', 3))
            ->put(route('staff.verify.payments.reject'), [
                'reject_id' => $transactionId,
                'rejection_reason' => 'The uploaded proof is unreadable.',
            ]);

        $response->assertRedirect(route('staff.verify.payments'));
        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'payment_status' => 'Unpaid',
            'payment_rejection_reason' => 'The uploaded proof is unreadable.',
            'date_paid' => null,
        ]);
    }

    public function test_staff_cannot_reject_a_payment_from_another_branch(): void
    {
        $transactionId = $this->createPendingTransaction(4);

        $this->actingAs($this->user(20, 'staff', 3))
            ->put(route('staff.verify.payments.reject'), [
                'reject_id' => $transactionId,
                'rejection_reason' => 'The uploaded proof is unreadable.',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'payment_status' => 'Pending Verification',
            'payment_rejection_reason' => null,
        ]);
    }

    public function test_manager_can_reject_a_pending_payment_for_their_branch(): void
    {
        Mail::fake();
        $transactionId = $this->createPendingTransaction(3);

        $this->actingAs($this->user(21, 'manager', 3))
            ->put(route('manager.verify.payments.reject'), [
                'reject_id' => $transactionId,
                'rejection_reason' => 'Please upload the full receipt.',
            ])
            ->assertRedirect(route('manager.verify.payments'));

        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'payment_status' => 'Unpaid',
            'payment_rejection_reason' => 'Please upload the full receipt.',
        ]);
    }

    public function test_rejection_reason_is_required(): void
    {
        $transactionId = $this->createPendingTransaction(3);

        $this->actingAs($this->user(20, 'staff', 3))
            ->put(route('staff.verify.payments.reject'), [
                'reject_id' => $transactionId,
                'rejection_reason' => '   ',
            ])
            ->assertSessionHasErrors('rejection_reason');

        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'payment_status' => 'Pending Verification',
        ]);
    }

    public function test_customer_resubmission_clears_the_rejection_reason(): void
    {
        Storage::fake('public');
        $transactionId = $this->createPendingTransaction(3);
        DB::table('transactions')->where('id', $transactionId)->update([
            'payment_status' => 'Unpaid',
            'payment_rejection_reason' => 'The uploaded proof is unreadable.',
        ]);

        $this->actingAs($this->user(10, 'customer', 3))
            ->post(route('payment.submit'), [
                'transaction_id' => $transactionId,
                'payment_ref' => 'PAY-123',
                'method' => 'GCash',
                'proof_of_payment' => UploadedFile::fake()->createWithContent(
                    'proof.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/FZ8AAAAASUVORK5CYII=')
                ),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'payment_status' => 'Pending Verification',
            'payment_rejection_reason' => null,
            'payment_reference' => 'PAY-123',
        ]);
    }

    public function test_rejected_payment_cannot_be_approved_again_until_resubmitted(): void
    {
        Mail::fake();
        $transactionId = $this->createPendingTransaction(3);
        $staff = $this->user(20, 'staff', 3);

        $this->actingAs($staff)
            ->put(route('staff.verify.payments.reject'), [
                'reject_id' => $transactionId,
                'rejection_reason' => 'The uploaded proof is unreadable.',
            ]);

        $this->actingAs($staff)
            ->put(route('staff.verify.payments.approve'), [
                'approve_id' => $transactionId,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'payment_status' => 'Unpaid',
            'payment_rejection_reason' => 'The uploaded proof is unreadable.',
        ]);
    }

    private function createPendingTransaction(int $branchId): int
    {
        return DB::table('transactions')->insertGetId([
            'user_id' => 10,
            'branch_id' => $branchId,
            'ref_number' => 'REF-' . uniqid(),
            'payment_status' => 'Pending Verification',
            'proof_of_payment' => 'payment_proofs/proof.jpg',
            'date_paid' => now(),
        ]);
    }

    private function user(int $id, string $role, ?int $branchId): User
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
