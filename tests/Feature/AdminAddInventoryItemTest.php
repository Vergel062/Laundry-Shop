<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminAddInventoryItemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name');
        });

        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('item_name', 100);
            $table->unsignedInteger('stock_level')->default(0);
            $table->string('unit');
            $table->unsignedInteger('min_threshold')->default(2);
            $table->string('system_key', 40)->nullable();
            $table->timestamp('last_updated')->nullable();
        });

        Schema::create('inventory_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->string('item_name', 100);
            $table->string('movement_type');
            $table->unsignedInteger('quantity')->default(0);
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('source', 100);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        DB::table('branches')->insert([
            ['id' => 1, 'branch_name' => 'Carmona Branch'],
            ['id' => 2, 'branch_name' => 'Biñan Branch'],
        ]);
    }

    public function test_admin_can_add_an_inventory_item_and_record_its_starting_stock(): void
    {
        $response = $this->actingAs($this->user('admin'))
            ->post(route('admin.inventory.store'), [
                'branch_id' => 1,
                'item_name' => '  Bleach  ',
                'stock_level' => 12,
                'unit' => 'Gallons',
                'min_threshold' => 4,
                'return_branch_id' => 1,
            ]);

        $response->assertRedirect(route('admin.inventory', ['branch_id' => 1, 'status' => 'added']));
        $this->assertDatabaseHas('inventory', [
            'branch_id' => 1,
            'item_name' => 'Bleach',
            'stock_level' => 12,
            'unit' => 'Gallons',
            'min_threshold' => 4,
        ]);
        $this->assertDatabaseHas('inventory_history', [
            'branch_id' => 1,
            'item_name' => 'Bleach',
            'movement_type' => 'IN',
            'quantity' => 12,
            'stock_before' => 0,
            'stock_after' => 12,
            'source' => 'Admin added inventory item',
        ]);
    }

    public function test_inventory_page_shows_all_branches_by_default(): void
    {
        DB::table('inventory')->insert([
            'branch_id' => 1,
            'item_name' => 'Detergent',
            'stock_level' => 12,
            'unit' => 'Sachets',
            'min_threshold' => 4,
        ]);

        $this->actingAs($this->user('admin'))
            ->get(route('admin.inventory'))
            ->assertOk()
            ->assertSee('Detergent')
            ->assertSee('All Branches')
            ->assertSee('id="editInventoryBranch"', false);
    }

    public function test_inventory_page_shows_only_the_selected_branch_items(): void
    {
        DB::table('inventory')->insert([
            [
                'branch_id' => 1,
                'item_name' => 'Carmona Detergent',
                'stock_level' => 12,
                'unit' => 'Sachets',
                'min_threshold' => 4,
            ],
            [
                'branch_id' => 2,
                'item_name' => 'Binan Detergent',
                'stock_level' => 15,
                'unit' => 'Sachets',
                'min_threshold' => 4,
            ],
        ]);

        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.inventory', ['branch_id' => 1]))
            ->assertOk()
            ->assertSee('Carmona Detergent');

        $inventoryTable = explode('id="editInventoryItemModal"', $response->getContent(), 2)[0];
        $this->assertStringNotContainsString('Binan Detergent', $inventoryTable);
    }

    public function test_admin_cannot_add_the_same_item_twice_to_one_branch(): void
    {
        DB::table('inventory')->insert([
            'branch_id' => 1,
            'item_name' => 'Bleach',
            'stock_level' => 5,
            'unit' => 'Gallons',
            'min_threshold' => 2,
        ]);

        $this->actingAs($this->user('admin'))
            ->from(route('admin.inventory'))
            ->post(route('admin.inventory.store'), [
                'branch_id' => 1,
                'item_name' => 'Bleach',
                'stock_level' => 1,
                'unit' => 'Gallons',
                'min_threshold' => 2,
            ])
            ->assertSessionHasErrors('item_name');

        $this->assertSame(1, DB::table('inventory')->where('branch_id', 1)->count());
    }

    public function test_non_admin_cannot_add_inventory_items(): void
    {
        $this->actingAs($this->user('manager'))
            ->post(route('admin.inventory.store'), [
                'branch_id' => 1,
                'item_name' => 'Bleach',
                'stock_level' => 1,
                'unit' => 'Gallons',
                'min_threshold' => 2,
            ])
            ->assertForbidden();

        $this->assertSame(0, DB::table('inventory')->count());
    }

    public function test_admin_can_edit_an_item_name_and_threshold_without_changing_stock_or_history(): void
    {
        $inventoryId = DB::table('inventory')->insertGetId([
            'branch_id' => 1,
            'item_name' => 'Bleach',
            'stock_level' => 12,
            'unit' => 'Gallons',
            'min_threshold' => 4,
        ]);

        $this->actingAs($this->user('admin'))
            ->put(route('admin.inventory.items.update'), [
                'edit_branch_id' => 1,
                'item_id' => $inventoryId,
                'item_name' => 'Laundry Bleach',
                'min_threshold' => 8,
                'return_branch_id' => 1,
            ])
            ->assertRedirect(route('admin.inventory', ['branch_id' => 1, 'status' => 'item-updated']));

        $this->assertDatabaseHas('inventory', [
            'id' => $inventoryId,
            'item_name' => 'Laundry Bleach',
            'stock_level' => 12,
            'min_threshold' => 8,
        ]);
        $this->assertSame(0, DB::table('inventory_history')->count());
    }

    public function test_renaming_a_system_item_keeps_its_automatic_stock_tracking_key(): void
    {
        $inventoryId = DB::table('inventory')->insertGetId([
            'branch_id' => 1,
            'item_name' => 'Detergent',
            'stock_level' => 12,
            'unit' => 'Sachets',
            'min_threshold' => 4,
            'system_key' => 'detergent',
        ]);

        $this->actingAs($this->user('admin'))
            ->put(route('admin.inventory.items.update'), [
                'edit_branch_id' => 1,
                'item_id' => $inventoryId,
                'item_name' => 'Laundry Soap',
                'min_threshold' => 8,
            ])
            ->assertRedirect();

        \App\Services\InventoryHistoryService::adjustBySystemKey(
            1,
            'detergent',
            -2,
            'Staff service order usage'
        );

        $this->assertDatabaseHas('inventory', [
            'id' => $inventoryId,
            'item_name' => 'Laundry Soap',
            'stock_level' => 10,
            'system_key' => 'detergent',
        ]);
    }

    public function test_admin_cannot_rename_an_item_to_an_existing_name_in_the_same_branch(): void
    {
        $itemId = DB::table('inventory')->insertGetId([
            'branch_id' => 1,
            'item_name' => 'Bleach',
            'stock_level' => 12,
            'unit' => 'Gallons',
            'min_threshold' => 4,
        ]);
        DB::table('inventory')->insert([
            'branch_id' => 1,
            'item_name' => 'Detergent',
            'stock_level' => 20,
            'unit' => 'Sachets',
            'min_threshold' => 15,
        ]);

        $this->actingAs($this->user('admin'))
            ->from(route('admin.inventory'))
            ->put(route('admin.inventory.items.update'), [
                'edit_branch_id' => 1,
                'item_id' => $itemId,
                'item_name' => 'Detergent',
                'min_threshold' => 8,
            ])
            ->assertSessionHasErrors('item_name');

        $this->assertDatabaseHas('inventory', [
            'id' => $itemId,
            'item_name' => 'Bleach',
            'min_threshold' => 4,
        ]);
    }

    public function test_non_admin_cannot_edit_inventory_item_details(): void
    {
        $itemId = DB::table('inventory')->insertGetId([
            'branch_id' => 1,
            'item_name' => 'Bleach',
            'stock_level' => 12,
            'unit' => 'Gallons',
            'min_threshold' => 4,
        ]);

        $this->actingAs($this->user('manager'))
            ->put(route('admin.inventory.items.update'), [
                'edit_branch_id' => 1,
                'item_id' => $itemId,
                'item_name' => 'Laundry Bleach',
                'min_threshold' => 8,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('inventory', [
            'id' => $itemId,
            'item_name' => 'Bleach',
            'min_threshold' => 4,
        ]);
    }

    private function user(string $role): User
    {
        $user = new User();
        $user->forceFill([
            'id' => 10,
            'role' => $role,
        ]);

        return $user;
    }
}
