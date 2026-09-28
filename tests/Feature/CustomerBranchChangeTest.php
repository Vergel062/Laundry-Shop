<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerBranchChangeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->unsignedBigInteger('branch_id')->nullable();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name');
            $table->dateTime('archive_date')->nullable();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('order_status')->nullable();
            $table->string('payment_status')->nullable();
        });

        DB::table('branches')->insert([
            ['id' => 1, 'branch_name' => 'Carmona', 'archive_date' => null],
            ['id' => 2, 'branch_name' => 'Biñan', 'archive_date' => null],
            ['id' => 3, 'branch_name' => 'Archived', 'archive_date' => now()],
        ]);
    }

    public function test_customer_can_change_branch_after_orders_are_complete_without_moving_history(): void
    {
        DB::table('users')->insert(['id' => 10, 'role' => 'customer', 'branch_id' => 1]);
        DB::table('transactions')->insert([
            'id' => 100,
            'user_id' => 10,
            'branch_id' => 1,
            'order_status' => 'Claimed',
            'payment_status' => 'Paid',
        ]);
        DB::table('transactions')->insert([
            'id' => 101,
            'user_id' => 10,
            'branch_id' => 1,
            'order_status' => 'Cancelled',
            'payment_status' => 'Unpaid',
        ]);

        $this->actingAs($this->customer(10))
            ->from('/user')
            ->post(route('user.change-branch'), ['branch_id' => 2])
            ->assertRedirect(route('user.dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => 10, 'branch_id' => 2]);
        $this->assertDatabaseHas('transactions', ['id' => 100, 'user_id' => 10]);
        $this->assertSame(1, (int) DB::table('transactions')->where('id', 100)->value('branch_id'));
    }

    public function test_customer_cannot_change_branch_with_a_pending_service_request(): void
    {
        DB::table('users')->insert(['id' => 10, 'role' => 'customer', 'branch_id' => 1]);
        DB::table('transactions')->insert([
            'user_id' => 10,
            'order_status' => 'Pending',
            'payment_status' => 'Service Request',
        ]);

        $this->actingAs($this->customer(10))
            ->from('/user')
            ->post(route('user.change-branch'), ['branch_id' => 2])
            ->assertRedirect('/user')
            ->assertSessionHas('branch_error');

        $this->assertDatabaseHas('users', ['id' => 10, 'branch_id' => 1]);
    }

    public function test_customer_cannot_change_branch_with_unpaid_or_unclaimed_laundry(): void
    {
        DB::table('users')->insert(['id' => 10, 'role' => 'customer', 'branch_id' => 1]);
        DB::table('transactions')->insert([
            [
                'user_id' => 10,
                'order_status' => 'Claimed',
                'payment_status' => 'Unpaid',
            ],
            [
                'user_id' => 10,
                'order_status' => 'Ready',
                'payment_status' => 'Paid',
            ],
        ]);

        $this->actingAs($this->customer(10))
            ->from('/user')
            ->post(route('user.change-branch'), ['branch_id' => 2])
            ->assertSessionHas('branch_error');

        $this->assertDatabaseHas('users', ['id' => 10, 'branch_id' => 1]);
    }

    public function test_only_customers_can_change_branches_and_archived_branches_are_rejected(): void
    {
        $staff = $this->customer(10, 'staff');

        $this->actingAs($staff)
            ->post(route('user.change-branch'), ['branch_id' => 2])
            ->assertForbidden();

        $this->actingAs($this->customer(10))
            ->from('/user')
            ->post(route('user.change-branch'), ['branch_id' => 3])
            ->assertSessionHasErrors('branch_id');
    }

    private function customer(int $id, string $role = 'customer'): User
    {
        $user = new User();
        $user->forceFill([
            'id' => $id,
            'role' => $role,
            'branch_id' => 1,
        ]);

        return $user;
    }
}
