<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('inventory', 'system_key')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->string('system_key', 40)->nullable()->index();
            });
        }

        foreach ([
            'detergent' => '%detergent%',
            'softener' => '%downy%',
            'spray' => '%spray%',
            'lpg' => '%lpg%',
        ] as $systemKey => $pattern) {
            DB::table('inventory')
                ->whereNull('system_key')
                ->whereRaw('LOWER(item_name) LIKE ?', [$pattern])
                ->update(['system_key' => $systemKey]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('inventory', 'system_key')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->dropIndex(['system_key']);
                $table->dropColumn('system_key');
            });
        }
    }
};
