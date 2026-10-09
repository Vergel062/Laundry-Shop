<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\ManagerServiceController;
use App\Http\Controllers\Auth\ServiceController;
use App\Http\Controllers\Auth\ServiceHistoryController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TransactionHistoryActiveOrdersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::connection()->getPdo()->sqliteCreateFunction('FIELD', function ($value, ...$values) {
            $position = array_search($value, $values, true);

            return $position === false ? count($values) + 1 : $position + 1;
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('role');
            $table->unsignedBigInteger('branch_id')->nullable();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name');
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('ref_number');
            $table->timestamp('created_at');
            $table->string('order_status')->nullable();
            $table->string('payment_status')->nullable();
            $table->date('queue_date')->nullable();
            $table->unsignedSmallInteger('queue_number')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
        });

        DB::table('branches')->insert([
            ['id' => 1, 'branch_name' => 'Carmona'],
            ['id' => 2, 'branch_name' => 'Biñan'],
        ]);

        DB::table('users')->insert([
            ['id' => 10, 'fullname' => 'Customer One', 'role' => 'customer', 'branch_id' => 1],
            ['id' => 11, 'fullname' => 'Customer Two', 'role' => 'customer', 'branch_id' => 2],
        ]);

        DB::table('transactions')->insert([
            $this->transaction(1, 10, 1, '2026-09-20', 'Pending', 'Unpaid'),
            $this->transaction(2, 10, 1, '2026-09-21', 'Ready', 'Pending Verification'),
            $this->transaction(3, 10, 1, '2026-09-22', 'Claimed', 'Unpaid'),
            $this->transaction(4, 10, 1, '2026-09-23', 'Claimed', 'Paid'),
            $this->transaction(5, 10, 1, '2026-09-24', 'Cancelled', 'Unpaid'),
            $this->transaction(6, 10, 1, '2026-10-01', 'Claimed', 'Paid'),
            $this->transaction(7, 11, 2, '2026-09-25', 'Pending', 'Unpaid'),
        ]);
    }

    public function test_staff_and_manager_lists_keep_older_active_orders_but_hide_completed_history(): void
    {
        foreach ([
            [ServiceController::class, 'staff'],
            [ManagerServiceController::class, 'manager'],
        ] as [$controllerClass, $role]) {
            $this->actingAs($this->user(20, $role, 1));
            $response = app($controllerClass)->index($this->octoberRequest());

            $this->assertSame([1, 2, 3, 6], $response->getData()['transactions']->pluck('id')->all());
        }
    }

    public function test_admin_transaction_history_keeps_active_orders_from_previous_month(): void
    {
        $this->actingAs($this->user(1, 'admin', null));
        $response = app(ServiceHistoryController::class)->index($this->octoberRequest());

        $ids = $response->getData()['list']->pluck('id')->all();
        sort($ids);
        $this->assertSame([1, 2, 3, 6, 7], $ids);
    }

    private function octoberRequest(): Request
    {
        return Request::create('/', 'GET', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-01',
        ]);
    }

    private function transaction(
        int $id,
        int $userId,
        int $branchId,
        string $date,
        string $orderStatus,
        string $paymentStatus
    ): array {
        return [
            'id' => $id,
            'user_id' => $userId,
            'branch_id' => $branchId,
            'ref_number' => 'LC-' . $id,
            'created_at' => $date . ' 12:00:00',
            'order_status' => $orderStatus,
            'payment_status' => $paymentStatus,
            'total_amount' => 100,
        ];
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
