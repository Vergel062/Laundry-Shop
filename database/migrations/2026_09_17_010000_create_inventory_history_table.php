<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->string('item_name', 100);
            $table->enum('movement_type', ['IN', 'OUT', 'ADJUSTMENT']);
            $table->unsignedInteger('quantity')->default(0);
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('source', 100);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['branch_id', 'created_at']);
            $table->index('inventory_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_history');
    }
};
