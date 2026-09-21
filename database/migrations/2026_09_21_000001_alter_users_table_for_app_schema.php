<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'fullname')) {
                $table->string('fullname', 100)->nullable()->after('id');
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->unique()->after('email');
            }

            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'staff', 'customer', 'manager'])->default('customer')->after('password');
            }

            if (!Schema::hasColumn('users', 'branch_id')) {
                $table->unsignedInteger('branch_id')->nullable()->after('role');
            }

            if (!Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code', 20)->nullable()->unique()->after('branch_id');
            }

            if (!Schema::hasColumn('users', 'referred_by')) {
                $table->string('referred_by', 20)->nullable()->after('referral_code');
            }

            if (!Schema::hasColumn('users', 'profile_pic')) {
                $table->string('profile_pic', 255)->nullable()->default('default_avatar.png')->after('referred_by');
            }

            if (!Schema::hasColumn('users', 'is_archived')) {
                $table->boolean('is_archived')->nullable()->default(false)->after('profile_pic');
            }

            if (!Schema::hasColumn('users', 'archive_date')) {
                $table->dateTime('archive_date')->nullable()->after('is_archived');
            }
        });

        if (!Schema::hasColumn('users', 'fullname')) {
            DB::statement('ALTER TABLE users ADD fullname VARCHAR(100) NULL AFTER id');
        }

        if (!Schema::hasColumn('users', 'phone')) {
            DB::statement('ALTER TABLE users ADD phone VARCHAR(20) NULL UNIQUE AFTER email');
        }

        if (!Schema::hasColumn('users', 'role')) {
            DB::statement("ALTER TABLE users ADD role ENUM('admin','staff','customer','manager') NOT NULL DEFAULT 'customer' AFTER password");
        }

        if (!Schema::hasColumn('users', 'branch_id')) {
            DB::statement('ALTER TABLE users ADD branch_id INT NULL AFTER role');
        }

        if (!Schema::hasColumn('users', 'referral_code')) {
            DB::statement('ALTER TABLE users ADD referral_code VARCHAR(20) NULL UNIQUE AFTER branch_id');
        }

        if (!Schema::hasColumn('users', 'referred_by')) {
            DB::statement('ALTER TABLE users ADD referred_by VARCHAR(20) NULL AFTER referral_code');
        }

        if (!Schema::hasColumn('users', 'profile_pic')) {
            DB::statement("ALTER TABLE users ADD profile_pic VARCHAR(255) NULL DEFAULT 'default_avatar.png' AFTER referred_by");
        }

        if (!Schema::hasColumn('users', 'is_archived')) {
            DB::statement('ALTER TABLE users ADD is_archived TINYINT(1) NULL DEFAULT 0 AFTER profile_pic');
        }

        if (!Schema::hasColumn('users', 'archive_date')) {
            DB::statement('ALTER TABLE users ADD archive_date DATETIME NULL AFTER is_archived');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['fullname', 'phone', 'role', 'branch_id', 'referral_code', 'referred_by', 'profile_pic', 'is_archived', 'archive_date'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
