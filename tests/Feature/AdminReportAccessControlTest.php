<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminReportAccessControlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('email')->nullable();
            $table->string('role');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->boolean('can_view_reports')->default(false);
            $table->boolean('can_verify_payments')->default(true);
            $table->boolean('is_archived')->default(false);
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name');
            $table->dateTime('archive_date')->nullable();
        });

        DB::table('branches')->insert(['id' => 1, 'branch_name' => 'Carmona']);
        DB::table('branches')->insert(['id' => 2, 'branch_name' => 'Biñan']);
        DB::table('users')->insert([
            ['id' => 1, 'fullname' => 'Admin User', 'email' => 'admin@example.com', 'role' => 'admin', 'branch_id' => null, 'can_view_reports' => false, 'can_verify_payments' => true, 'is_archived' => false],
            ['id' => 2, 'fullname' => 'Staff One', 'email' => 'staff1@example.com', 'role' => 'staff', 'branch_id' => 1, 'can_view_reports' => false, 'can_verify_payments' => true, 'is_archived' => false],
            ['id' => 3, 'fullname' => 'Staff Two', 'email' => 'staff2@example.com', 'role' => 'staff', 'branch_id' => 1, 'can_view_reports' => false, 'can_verify_payments' => true, 'is_archived' => false],
            ['id' => 4, 'fullname' => 'Manager User', 'email' => 'manager@example.com', 'role' => 'manager', 'branch_id' => 1, 'can_view_reports' => false, 'can_verify_payments' => true, 'is_archived' => false],
            ['id' => 5, 'fullname' => 'Staff Three', 'email' => 'staff3@example.com', 'role' => 'staff', 'branch_id' => 2, 'can_view_reports' => false, 'can_verify_payments' => true, 'is_archived' => false],
        ]);
    }

    public function test_admin_can_grant_and_revoke_report_access_for_an_individual_staff_member(): void
    {
        $this->actingAs($this->user(1, 'admin'));

        $this->put(route('admin.access-control.staff.update', 2), ['can_view_reports' => 1, 'can_verify_payments' => 1])
            ->assertRedirect(route('admin.access-control'));
        $this->assertDatabaseHas('users', ['id' => 2, 'can_view_reports' => true]);
        $this->assertDatabaseHas('users', ['id' => 3, 'can_view_reports' => false]);

        $this->put(route('admin.access-control.staff.update', 2), ['can_view_reports' => 0, 'can_verify_payments' => 1])
            ->assertRedirect(route('admin.access-control'));
        $this->assertDatabaseHas('users', ['id' => 2, 'can_view_reports' => false]);
    }

    public function test_admin_can_revoke_payment_verification_without_changing_report_access(): void
    {
        $this->actingAs($this->user(1, 'admin'));

        $this->put(route('admin.access-control.staff.update', 2), [
            'can_view_reports' => 0,
            'can_verify_payments' => 0,
        ])->assertRedirect(route('admin.access-control'));

        $this->assertDatabaseHas('users', [
            'id' => 2,
            'can_view_reports' => false,
            'can_verify_payments' => false,
        ]);
    }

    public function test_access_control_list_shows_active_staff_only(): void
    {
        DB::table('users')->where('id', 3)->update(['is_archived' => true]);

        $response = $this->actingAs($this->user(1, 'admin'))
            ->get(route('admin.access-control'))
            ->assertOk();

        $this->assertSame([5, 2], $response->viewData('staff')->pluck('id')->all());
    }

    public function test_access_control_lists_staff_across_branches_and_can_filter_by_branch(): void
    {
        $this->actingAs($this->user(1, 'admin'));

        $this->get(route('admin.access-control'))
            ->assertOk()
            ->assertViewHas('staff', function ($staff) {
                return $staff->pluck('id')->all() === [5, 2, 3];
            });

        $this->get(route('admin.access-control', ['branch_id' => 2]))
            ->assertOk()
            ->assertViewHas('staff', function ($staff) {
                return $staff->pluck('id')->all() === [5];
            });
    }

    public function test_non_admin_cannot_change_report_access(): void
    {
        $this->actingAs($this->user(2, 'staff'))
            ->put(route('admin.access-control.staff.update', 3), ['can_view_reports' => 1, 'can_verify_payments' => 1])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => 3, 'can_view_reports' => false]);
    }

    private function user(int $id, string $role): User
    {
        $user = new User();
        $user->forceFill(['id' => $id, 'role' => $role]);

        return $user;
    }
}
