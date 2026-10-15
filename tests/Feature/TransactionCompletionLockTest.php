<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TransactionCompletionLockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fullname')->nullable();
            $table->string('email')->nullable();
            $table->string('role');
            $table->unsignedBigInteger('branch_id')->nullable();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('order_status');
            $table->string('payment_status');
        });

        DB::table('users')->insert([
            ['id' => 1, 'fullname' => 'Staff User', 'role' => 'staff', 'branch_id' => 4],
            ['id' => 2, 'fullname' => 'Manager User', 'role' => 'manager', 'branch_id' => 4],
            ['id' => 10, 'fullname' => 'Customer', 'role' => 'customer', 'branch_id' => 4],
        ]);
    }

    public function test_staff_cannot_edit_a_completed_paid_transaction(): void
    {
        $this->addTransaction(1, 'Claimed', 'Paid');

        $this->actingAs($this->user(1, 'staff'))
            ->put(route('staff.services.update'), $this->updatePayload())
            ->assertSessionHas('error', 'Completed transactions cannot be changed.');

        $this->assertDatabaseHas('transactions', [
            'id' => 1,
            'order_status' => 'Claimed',
            'payment_status' => 'Paid',
        ]);
    }

    public function test_manager_cannot_edit_a_completed_paid_transaction(): void
    {
        $this->addTransaction(1, 'Claimed', 'Paid');

        $this->actingAs($this->user(2, 'manager'))
            ->put(route('manager.services.update'), $this->updatePayload())
            ->assertSessionHas('error', 'Completed transactions cannot be changed.');

        $this->assertDatabaseHas('transactions', [
            'id' => 1,
            'order_status' => 'Claimed',
            'payment_status' => 'Paid',
        ]);
    }

    public function test_staff_cannot_change_paid_transaction_back_to_unpaid(): void
    {
        $this->addTransaction(1, 'Washing', 'Paid');

        $this->actingAs($this->user(1, 'staff'))
            ->put(route('staff.services.update'), $this->updatePayload('Drying', 'Unpaid'))
            ->assertSessionHas('error', 'A paid transaction cannot be changed back to unpaid.');

        $this->assertDatabaseHas('transactions', [
            'id' => 1,
            'order_status' => 'Washing',
            'payment_status' => 'Paid',
        ]);
    }

    public function test_manager_cannot_change_paid_transaction_back_to_unpaid(): void
    {
        $this->addTransaction(1, 'Washing', 'Paid');

        $this->actingAs($this->user(2, 'manager'))
            ->put(route('manager.services.update'), $this->updatePayload('Drying', 'Unpaid'))
            ->assertSessionHas('error', 'A paid transaction cannot be changed back to unpaid.');

        $this->assertDatabaseHas('transactions', [
            'id' => 1,
            'order_status' => 'Washing',
            'payment_status' => 'Paid',
        ]);
    }

    public function test_staff_can_still_update_an_incomplete_unpaid_transaction(): void
    {
        $this->addTransaction(1, 'Washing', 'Unpaid');

        $this->actingAs($this->user(1, 'staff'))
            ->put(route('staff.services.update'), $this->updatePayload('Drying', 'Unpaid'))
            ->assertRedirect(route('staff.services'))
            ->assertSessionHas('success', 'Transaction updated successfully.');

        $this->assertDatabaseHas('transactions', [
            'id' => 1,
            'order_status' => 'Drying',
            'payment_status' => 'Unpaid',
        ]);
    }

    private function addTransaction(int $id, string $orderStatus, string $paymentStatus): void
    {
        DB::table('transactions')->insert([
            'id' => $id,
            'user_id' => 10,
            'branch_id' => 4,
            'order_status' => $orderStatus,
            'payment_status' => $paymentStatus,
        ]);
    }

    private function updatePayload(string $status = 'Washing', string $payment = 'Paid'): array
    {
        return [
            'transaction_id' => 1,
            'new_status' => $status,
            'payment_status' => $payment,
        ];
    }

    private function user(int $id, string $role): User
    {
        $user = new User();
        $user->forceFill([
            'id' => $id,
            'role' => $role,
            'branch_id' => 4,
        ]);

        return $user;
    }
}
