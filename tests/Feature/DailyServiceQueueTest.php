<?php

namespace Tests\Feature;

use App\Exceptions\DailyServiceQueueFull;
use App\Models\User;
use App\Services\DailyServiceQueue;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DailyServiceQueueTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('fullname')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('staff_id')->default(0);
            $table->unsignedBigInteger('branch_id');
            $table->date('queue_date')->nullable();
            $table->unsignedSmallInteger('queue_number')->nullable();
            $table->string('ref_number')->default('');
            $table->decimal('weight_kg', 10, 2)->default(0);
            $table->string('service_type')->default('Wash-Dry');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('order_status')->nullable();
            $table->string('payment_status')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        DB::table('branches')->insert([
            ['id' => 1, 'branch_name' => 'Carmona'],
            ['id' => 2, 'branch_name' => 'Biñan'],
        ]);
        DB::table('users')->insert([
            ['id' => 10, 'role' => 'customer', 'fullname' => 'Queue Customer', 'branch_id' => 1],
            ['id' => 20, 'role' => 'staff', 'fullname' => 'Queue Staff', 'branch_id' => 1],
            ['id' => 21, 'role' => 'manager', 'fullname' => 'Queue Manager', 'branch_id' => 1],
        ]);
    }

    public function test_queue_numbers_increase_per_branch_and_restart_on_the_next_day(): void
    {
        $queue = app(DailyServiceQueue::class);
        $today = Carbon::parse('2026-10-09 10:00:00', 'Asia/Manila');

        $first = DB::transaction(fn () => $queue->assignNext(1, $today));
        $this->recordQueuedService(1, $first);
        $second = DB::transaction(fn () => $queue->assignNext(1, $today));
        $this->recordQueuedService(1, $second);
        $otherBranch = DB::transaction(fn () => $queue->assignNext(2, $today));
        $this->recordQueuedService(2, $otherBranch);
        $tomorrow = DB::transaction(fn () => $queue->assignNext(1, $today->copy()->addDay()));

        $this->assertSame(['queue_date' => '2026-10-09', 'queue_number' => 1], $first);
        $this->assertSame(2, $second['queue_number']);
        $this->assertSame(1, $otherBranch['queue_number']);
        $this->assertSame(1, $tomorrow['queue_number']);
    }

    public function test_staff_approval_assigns_queue_number_to_the_original_service_request(): void
    {
        $requestId = DB::table('transactions')->insertGetId([
            'user_id' => 10,
            'branch_id' => 1,
            'ref_number' => 'REQUEST-1',
            'order_status' => 'Pending',
            'payment_status' => 'Service Request',
            'created_at' => Carbon::now('Asia/Manila')->subMinutes(5),
        ]);

        $response = $this->actingAs($this->user(20, 'staff', 1))
            ->postJson(route('staff.save.order'), [
                'request_id' => $requestId,
                'user_id' => 10,
                'weight' => 4,
                'service' => 'Wash-Dry',
                'amount' => 130,
            ]);
        $response->assertOk()->assertJsonPath('status', 'success');

        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('transactions', [
            'id' => $requestId,
            'payment_status' => 'Unpaid',
            'queue_date' => Carbon::now('Asia/Manila')->toDateString(),
            'queue_number' => 1,
        ]);
    }

    public function test_manager_approval_uses_the_same_queue_and_preserves_request_record(): void
    {
        $requestId = DB::table('transactions')->insertGetId([
            'user_id' => 10,
            'branch_id' => 1,
            'ref_number' => 'REQUEST-2',
            'order_status' => 'Pending',
            'payment_status' => 'Service Request',
            'created_at' => Carbon::now('Asia/Manila')->subMinutes(5),
        ]);

        $this->actingAs($this->user(21, 'manager', 1))
            ->post(route('manager.save.order'), [
                'request_id' => $requestId,
                'user_id' => 10,
                'weight' => 4,
                'service' => 'Wash-Dry',
                'amount' => 130,
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('transactions', [
            'id' => $requestId,
            'payment_status' => 'Unpaid',
            'queue_date' => Carbon::now('Asia/Manila')->toDateString(),
            'queue_number' => 1,
        ]);
    }

    public function test_queue_rejects_another_service_after_the_branch_reaches_50_for_the_day(): void
    {
        $today = Carbon::now('Asia/Manila')->toDateString();
        for ($number = 1; $number <= 50; $number++) {
            DB::table('transactions')->insert([
                'user_id' => 10,
                'branch_id' => 1,
                'queue_date' => $today,
                'queue_number' => $number,
                'order_status' => 'Claimed',
                'payment_status' => 'Paid',
                'created_at' => now(),
            ]);
        }

        $response = $this->actingAs($this->user(20, 'staff', 1))
            ->postJson(route('staff.save.order'), [
                'user_id' => 10,
                'weight' => 4,
                'service' => 'Wash-Dry',
                'amount' => 130,
            ]);

        $response->assertStatus(422)->assertJsonPath('status', 'error');
        $this->assertDatabaseCount('transactions', 50);
    }

    public function test_customer_cannot_request_a_service_after_branch_cutoff(): void
    {
        $today = Carbon::now('Asia/Manila')->toDateString();
        for ($number = 1; $number <= 50; $number++) {
            DB::table('transactions')->insert([
                'user_id' => 10,
                'branch_id' => 1,
                'queue_date' => $today,
                'queue_number' => $number,
                'order_status' => 'Pending',
                'payment_status' => 'Unpaid',
                'created_at' => now(),
            ]);
        }

        $this->actingAs($this->user(10, 'customer', 1))
            ->post(route('user.service.request'))
            ->assertRedirect(route('user.dashboard'))
            ->assertSessionHas('queue_error');

        $this->assertDatabaseCount('transactions', 50);
        $this->assertDatabaseMissing('transactions', ['payment_status' => 'Service Request']);
    }

    public function test_manager_cannot_add_a_new_service_after_branch_cutoff(): void
    {
        $today = Carbon::now('Asia/Manila')->toDateString();
        for ($number = 1; $number <= 50; $number++) {
            DB::table('transactions')->insert([
                'user_id' => 10,
                'branch_id' => 1,
                'queue_date' => $today,
                'queue_number' => $number,
                'order_status' => 'Pending',
                'payment_status' => 'Unpaid',
                'created_at' => now(),
            ]);
        }

        $this->actingAs($this->user(21, 'manager', 1))
            ->postJson(route('manager.save.order'), [
                'user_id' => 10,
                'weight' => 4,
                'service' => 'Wash-Dry',
                'amount' => 130,
            ])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseCount('transactions', 50);
    }

    public function test_unapproved_requests_do_not_consume_queue_numbers(): void
    {
        DB::table('transactions')->insert([
            'user_id' => 10,
            'branch_id' => 1,
            'order_status' => 'Pending',
            'payment_status' => 'Service Request',
            'created_at' => now(),
        ]);

        $queue = app(DailyServiceQueue::class);
        $number = DB::transaction(fn () => $queue->assignNext(1, Carbon::now('Asia/Manila')));

        $this->assertSame(1, $number['queue_number']);
    }

    public function test_existing_confirmed_transactions_receive_numbers_without_numbering_pending_requests(): void
    {
        DB::table('transactions')->insert([
            [
                'user_id' => 10,
                'branch_id' => 1,
                'ref_number' => 'OLD-1',
                'order_status' => 'Pending',
                'payment_status' => 'Unpaid',
                'created_at' => '2026-10-08 09:00:00',
            ],
            [
                'user_id' => 10,
                'branch_id' => 1,
                'ref_number' => 'OLD-2',
                'order_status' => 'Pending',
                'payment_status' => 'Service Request',
                'created_at' => '2026-10-08 10:00:00',
            ],
        ]);

        app(DailyServiceQueue::class)->ensureExistingOrdersHaveNumbers(1);

        $this->assertDatabaseHas('transactions', [
            'ref_number' => 'OLD-1',
            'queue_date' => '2026-10-08',
            'queue_number' => 1,
        ]);
        $this->assertDatabaseHas('transactions', [
            'ref_number' => 'OLD-2',
            'queue_date' => null,
            'queue_number' => null,
        ]);
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

    private function recordQueuedService(int $branchId, array $queue): void
    {
        DB::table('transactions')->insert([
            'user_id' => 10,
            'branch_id' => $branchId,
            'queue_date' => $queue['queue_date'],
            'queue_number' => $queue['queue_number'],
            'order_status' => 'Pending',
            'payment_status' => 'Unpaid',
            'created_at' => $queue['queue_date'] . ' 09:00:00',
        ]);
    }
}
