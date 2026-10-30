<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'service_request_rejection_reason')) {
                $table->text('service_request_rejection_reason')->nullable();
            }

            if (!Schema::hasColumn('transactions', 'service_request_rejection_read_at')) {
                $table->timestamp('service_request_rejection_read_at')->nullable();
            }

            if (!Schema::hasColumn('transactions', 'service_request_rejection_deleted_at')) {
                $table->timestamp('service_request_rejection_deleted_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $columns = [
                'service_request_rejection_reason',
                'service_request_rejection_read_at',
                'service_request_rejection_deleted_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
