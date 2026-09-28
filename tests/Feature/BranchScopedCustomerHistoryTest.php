<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\UserController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BranchScopedCustomerHistoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('profile_pic')->nullable();
            $table->string('referral_code')->nullable();
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
            $table->string('ref_number')->nullable();
            $table->string('order_status')->nullable();
            $table->string('payment_status')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('weight_kg', 10, 2)->default(0);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->integer('points_earned')->default(0);
            $table->integer('points_redeemed')->default(0);
            $table->string('source');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('landing_contents', function (Blueprint $table) {
            $table->id();
            $table->string('content_key')->unique();
            $table->text('content_value')->nullable();
        });

        DB::table('users')->insert([
            'id' => 10,
            'fullname' => 'Branch Customer',
            'email' => 'customer@example.com',
            'phone' => '09123456789',
            'referral_code' => 'CUSTOMER10',
            'role' => 'customer',
            'branch_id' => 1,
        ]);

        DB::table('transactions')->insert([
            [
                'id' => 101,
                'user_id' => 10,
                'branch_id' => 1,
                'ref_number' => 'CARMONA-ORDER',
                'order_status' => 'Claimed',
                'payment_status' => 'Paid',
                'created_at' => now(),
            ],
            [
                'id' => 202,
                'user_id' => 10,
                'branch_id' => 2,
                'ref_number' => 'BINAN-ORDER',
                'order_status' => 'Claimed',
                'payment_status' => 'Paid',
                'created_at' => now(),
            ],
        ]);

        DB::table('loyalty_points')->insert([
            [
                'user_id' => 10,
                'branch_id' => 1,
                'points_earned' => 100,
                'points_redeemed' => 20,
                'source' => 'Carmona Reward',
            ],
            [
                'user_id' => 10,
                'branch_id' => 2,
                'points_earned' => 30,
                'points_redeemed' => 5,
                'source' => 'Binan Reward',
            ],
        ]);
    }

    public function test_dashboard_shows_only_history_and_points_from_the_active_branch(): void
    {
        $customer = new User();
        $customer->forceFill([
            'id' => 10,
            'role' => 'customer',
            'branch_id' => 1,
        ]);

        $this->actingAs($customer);
        $controller = app(UserController::class);

        $carmonaData = $controller->index()->getData();
        $this->assertSame([101], $carmonaData['transactions']->pluck('id')->all());
        $this->assertSame(80, (int) $carmonaData['currentPoints']);

        DB::table('users')->where('id', 10)->update(['branch_id' => 2]);
        $binanData = $controller->index()->getData();

        $this->assertSame([202], $binanData['transactions']->pluck('id')->all());
        $this->assertSame(25, (int) $binanData['currentPoints']);
    }

    public function test_rewards_adjustments_use_only_the_customers_active_branch_balance(): void
    {
        DB::table('users')->where('id', 10)->update(['branch_id' => 2]);
        $admin = new User();
        $admin->forceFill(['id' => 99, 'role' => 'admin']);
        $this->actingAs($admin);

        $this->postJson(route('admin.loyalty.process'), [
            'user_id' => 10,
            'action' => 'redeem',
            'amount' => 26,
            'source' => 'Test redemption',
        ])->assertStatus(400);

        $this->postJson(route('admin.loyalty.process'), [
            'user_id' => 10,
            'action' => 'add',
            'amount' => 5,
            'source' => 'Test reward',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('loyalty_points', [
            'user_id' => 10,
            'branch_id' => 2,
            'points_earned' => 5,
            'source' => 'Test reward',
        ]);
        $this->assertDatabaseMissing('loyalty_points', [
            'user_id' => 10,
            'branch_id' => 1,
            'points_earned' => 5,
            'source' => 'Test reward',
        ]);
    }
}
