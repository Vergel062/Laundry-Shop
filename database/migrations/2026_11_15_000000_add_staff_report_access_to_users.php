<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'can_view_reports')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('can_view_reports')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'can_view_reports')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('can_view_reports');
            });
        }
    }
};
