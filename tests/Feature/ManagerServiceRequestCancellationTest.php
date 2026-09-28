<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ManagerServiceRequestCancellationTest extends TestCase
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

    public function test_manager_cannot_cancel_a_request_from_another_branch(): void
    {
        DB::table('transactions')->insert([
            'id' => 1,
            'branch_id' => 5,
            'order_status' => 'Pending',
            'payment_status' => 'Service Request',
        ]);

        $manager = new User();
        $manager->forceFill([
            'id' => 2,
            'role' => 'manager',
            'branch_id' => 4,
        ]);

        $this->actingAs($manager)
            ->post(route('manager.services.cancel-request', 1))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('transactions', [
            'id' => 1,
            'order_status' => 'Pending',
        ]);
    }
}
