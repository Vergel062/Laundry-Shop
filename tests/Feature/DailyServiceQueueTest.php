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

        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('item_name');
            $table->string('unit');
            $table->integer('stock_level')->default(0);
            $table->string('system_key', 40)->nullable();
            $table->timestamp('last_updated')->nullable();
        });

        Schema::create('inventory_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->string('item_name');
            $table->string('movement_type');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            $table->string('source');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('daily_service_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('transaction_id');
            $table->date('request_date');
            $table->timestamp('created_at')->nullable();
            $table->unique(['user_id', 'request_date']);
            $table->index(['branch_id', 'request_date']);
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

    public function test_customer_can_submit_only_one_service_request_per_day(): void
    {
        $today = Carbon::now('Asia/Manila')->toDateString();
        DB::table('daily_service_requests')->insert([
            'branch_id' => 2,
            'user_id' => 10,
            'transaction_id' => 100,
            'request_date' => $today,
        ]);

        $this->actingAs($this->user(10, 'customer', 1))
            ->post(route('user.service.request'))
            ->assertRedirect(route('user.dashboard'))
            ->assertSessionHas('queue_error', 'You have already submitted a service request today. You can request again tomorrow.');

        $this->assertDatabaseMissing('transactions', ['payment_status' => 'Service Request']);
    }

    public function test_customer_can_submit_another_request_on_the_next_day(): void
    {
        DB::table('daily_service_requests')->insert([
            'branch_id' => 1,
            'user_id' => 10,
            'transaction_id' => 100,
            'request_date' => Carbon::now('Asia/Manila')->subDay()->toDateString(),
        ]);

        $this->actingAs($this->user(10, 'customer', 1))
            ->post(route('user.service.request'))
            ->assertRedirect(route('user.dashboard'))
            ->assertSessionHas('success', 'Service request sent to your branch.');

        $this->assertDatabaseHas('transactions', [
            'user_id' => 10,
            'payment_status' => 'Service Request',
        ]);
        $this->assertDatabaseCount('daily_service_requests', 2);
    }

    public function test_branch_rejects_customer_requests_after_20_have_been_submitted_today(): void
    {
        $today = Carbon::now('Asia/Manila')->toDateString();
        $requests = [];
        for ($userId = 100; $userId < 120; $userId++) {
            $requests[] = [
                'branch_id' => 1,
                'user_id' => $userId,
                'transaction_id' => $userId,
                'request_date' => $today,
            ];
        }
        DB::table('daily_service_requests')->insert($requests);

        $this->actingAs($this->user(10, 'customer', 1))
            ->post(route('user.service.request'))
            ->assertRedirect(route('user.dashboard'))
            ->assertSessionHas('queue_error', 'This branch has reached its limit of 20 service requests for today. Requests will open again tomorrow.');

        $this->assertDatabaseMissing('transactions', ['payment_status' => 'Service Request']);
        $this->assertDatabaseCount('daily_service_requests', 20);
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

    public function test_staff_order_charges_only_for_sachets_beyond_the_combined_free_quota_and_deducts_selected_items(): void
    {
        $powderId = $this->createSachetItem(1, 'Powder Detergent', 'detergent', 8);
        $softenerId = $this->createSachetItem(1, 'Downy Softener', 'softener', 6);

        $this->actingAs($this->user(20, 'staff', 1))
            ->postJson(route('staff.save.order'), [
                'user_id' => 10,
                'weight' => 4,
                'service' => 'Wash-Dry',
                'det_inventory_id' => $powderId,
                'det_qty' => 2,
                'soft_inventory_id' => $softenerId,
                'soft_qty' => 1,
                'amount' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('transactions', ['total_amount' => 130]);
        $this->assertDatabaseHas('inventory', ['id' => $powderId, 'stock_level' => 6]);
        $this->assertDatabaseHas('inventory', ['id' => $softenerId, 'stock_level' => 5]);
        $this->assertDatabaseCount('inventory_history', 2);
    }

    public function test_manager_order_charges_ten_pesos_for_each_sachet_after_the_first_three(): void
    {
        $powderId = $this->createSachetItem(1, 'Detergent', 'detergent', 8);
        $softenerId = $this->createSachetItem(1, 'Downy / Softener', 'softener', 8);

        $this->actingAs($this->user(21, 'manager', 1))
            ->postJson(route('manager.save.order'), [
                'user_id' => 10,
                'weight' => 4,
                'service' => 'Wash-Dry',
                'det_inventory_id' => $powderId,
                'det_qty' => 2,
                'soft_inventory_id' => $softenerId,
                'soft_qty' => 2,
                'amount' => 130,
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('transactions', ['total_amount' => 140]);
        $this->assertDatabaseHas('inventory', ['id' => $powderId, 'stock_level' => 6]);
        $this->assertDatabaseHas('inventory', ['id' => $softenerId, 'stock_level' => 6]);
    }

    public function test_order_rejects_inventory_from_another_branch_without_saving_or_deducting(): void
    {
        $otherBranchPowderId = $this->createSachetItem(2, 'Powder Detergent', 'detergent', 8);

        $this->actingAs($this->user(20, 'staff', 1))
            ->postJson(route('staff.save.order'), [
                'user_id' => 10,
                'weight' => 4,
                'service' => 'Wash-Dry',
                'det_inventory_id' => $otherBranchPowderId,
                'det_qty' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseHas('inventory', ['id' => $otherBranchPowderId, 'stock_level' => 8]);
        $this->assertDatabaseCount('inventory_history', 0);
    }

    public function test_order_rolls_back_if_selected_sachet_stock_is_insufficient(): void
    {
        $powderId = $this->createSachetItem(1, 'Powder Detergent', 'detergent', 1);

        $this->actingAs($this->user(20, 'staff', 1))
            ->postJson(route('staff.save.order'), [
                'user_id' => 10,
                'weight' => 4,
                'service' => 'Wash-Dry',
                'det_inventory_id' => $powderId,
                'det_qty' => 2,
            ])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseHas('inventory', ['id' => $powderId, 'stock_level' => 1]);
        $this->assertDatabaseCount('inventory_history', 0);
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

    private function createSachetItem(int $branchId, string $name, string $systemKey, int $stock): int
    {
        return DB::table('inventory')->insertGetId([
            'branch_id' => $branchId,
            'item_name' => $name,
            'unit' => 'Sachets',
            'stock_level' => $stock,
            'system_key' => $systemKey,
        ]);
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
