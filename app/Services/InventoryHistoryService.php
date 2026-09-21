<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class InventoryHistoryService
{
    public static function adjustByItemName(
        int $branchId,
        string $pattern,
        int $delta,
        string $source,
        ?int $userId = null
    ): void {
        $item = DB::table('inventory')
            ->where('branch_id', $branchId)
            ->where('item_name', 'like', $pattern)
            ->first();

        if (!$item) {
            return;
        }

        $before = (int) $item->stock_level;
        $after = max(0, $before + $delta);
        DB::table('inventory')->where('id', $item->id)->update([
            'stock_level' => $after,
            'last_updated' => now(),
        ]);

        self::record($item, $before, $after, $source, $userId);
    }

    public static function setByItemName(
        int $branchId,
        string $pattern,
        int $stock,
        string $source,
        ?int $userId = null
    ): void {
        $item = DB::table('inventory')
            ->where('branch_id', $branchId)
            ->where('item_name', 'like', $pattern)
            ->first();

        if (!$item) {
            return;
        }

        $before = (int) $item->stock_level;
        DB::table('inventory')->where('id', $item->id)->update([
            'stock_level' => $stock,
            'last_updated' => now(),
        ]);

        self::record($item, $before, $stock, $source, $userId);
    }

    public static function record(
        object $item,
        int $before,
        int $after,
        string $source,
        ?int $userId = null
    ): void {
        if ($before === $after) {
            return;
        }

        $difference = $after - $before;

        DB::table('inventory_history')->insert([
            'inventory_id' => $item->id ?? null,
            'branch_id' => $item->branch_id,
            'item_name' => $item->item_name,
            'movement_type' => $difference > 0 ? 'IN' : 'OUT',
            'quantity' => abs($difference),
            'stock_before' => $before,
            'stock_after' => $after,
            'source' => $source,
            'user_id' => $userId,
            'created_at' => now(),
        ]);
    }
}
