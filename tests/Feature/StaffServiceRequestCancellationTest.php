<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StaffServiceRequestCancellationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->unsignedBigInteger('branch_id')->nullable();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('order_status');
            $table->string('payment_status');
        });
    }

    public function test_staff_can_cancel_a_pending_service_request_in_their_branch(): void
    {
        DB::table('transactions')->insert([
            'id' => 1,
            'branch_id' => 4,
            'order_status' => 'Pending',
            'payment_status' => 'Service Request',
        ]);

        $this->actingAs($this->user(1, 'staff', 4))
            ->post(route('staff.services.cancel-request', 1))
            ->assertRedirect(route('staff.services'));

        $this->assertDatabaseHas('transactions', [
            'id' => 1,
            'order_status' => 'Cancelled',
            'payment_status' => 'Service Request',
        ]);
    }

    public function test_manager_can_cancel_a_pending_service_request_in_their_branch(): void
    {
        DB::table('transactions')->insert([
            'id' => 1,
            'branch_id' => 4,
            'order_status' => 'Pending',
            'payment_status' => 'Service Request',
        ]);

        $this->actingAs($this->user(2, 'manager', 4))
            ->post(route('manager.services.cancel-request', 1))
            ->assertRedirect(route('manager.services'));

        $this->assertDatabaseHas('transactions', [
            'id' => 1,
            'order_status' => 'Cancelled',
        ]);
    }

    public function test_staff_cannot_cancel_an_approved_request_or_a_request_from_another_branch(): void
    {
        DB::table('transactions')->insert([
            [
                'id' => 1,
                'branch_id' => 4,
                'order_status' => 'Pending',
                'payment_status' => 'Unpaid',
            ],
            [
                'id' => 2,
                'branch_id' => 5,
                'order_status' => 'Pending',
                'payment_status' => 'Service Request',
            ],
        ]);

        $this->actingAs($this->user(1, 'staff', 4))
            ->post(route('staff.services.cancel-request', 1))
            ->assertSessionHas('error');

        $this->post(route('staff.services.cancel-request', 2))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('transactions', ['id' => 1, 'order_status' => 'Pending']);
        $this->assertDatabaseHas('transactions', ['id' => 2, 'order_status' => 'Pending']);
    }

    private function user(int $id, string $role, int $branchId): User
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
