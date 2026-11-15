<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'can_verify_payments')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('can_verify_payments')->default(true);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'can_verify_payments')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('can_verify_payments');
            });
        }
    }
};
