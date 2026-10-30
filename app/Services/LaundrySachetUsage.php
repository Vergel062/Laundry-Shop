<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LaundrySachetUsage
{
    private const SERVICE_BASE_PRICES = [
        'Wash Only' => 70,
        'Dry Only' => 60,
        'Wash-Dry' => 130,
        'Wash-Dry-Fold' => 170,
        'Comforter (Wash Only)' => 140,
        'Comforter (Dry Only)' => 130,
        'Comforter (Wash-Dry)' => 200,
        'Comforter (Wash-Dry-Fold)' => 240,
    ];

    public function itemsForBranch(int $branchId, string $type): Collection
    {
        $systemKey = $this->systemKey($type);
        $namePattern = $type === 'detergent' ? '%detergent%' : '%downy%';

        return DB::table('inventory')
            ->where('branch_id', $branchId)
            ->whereRaw("LOWER(unit) = 'sachets'")
            ->where(function ($query) use ($systemKey, $namePattern) {
                $query->where('system_key', $systemKey)
                    ->orWhereRaw('LOWER(item_name) LIKE ?', [$namePattern]);
            })
            ->orderBy('item_name')
            ->get(['id', 'item_name', 'stock_level']);
    }

    public function additionalCharge(int $detergentQuantity, int $softenerQuantity): int
    {
        return max(0, $detergentQuantity + $softenerQuantity - 3) * 10;
    }

    public function totalAmount(
        string $service,
        float $weight,
        int $detergentQuantity,
        int $softenerQuantity
    ): float {
        if (!isset(self::SERVICE_BASE_PRICES[$service])) {
            throw new InvalidArgumentException('Unsupported laundry service.');
        }

        return self::SERVICE_BASE_PRICES[$service]
            + (max(0, $weight - 8) * 10)
            + $this->additionalCharge($detergentQuantity, $softenerQuantity);
    }

    public function consume(
        int $branchId,
        ?int $detergentItemId,
        int $detergentQuantity,
        ?int $softenerItemId,
        int $softenerQuantity,
        string $source,
        ?int $userId
    ): void {
        $this->consumeItem($branchId, 'detergent', $detergentItemId, $detergentQuantity, $source, $userId);
        $this->consumeItem($branchId, 'softener', $softenerItemId, $softenerQuantity, $source, $userId);
    }

    private function consumeItem(
        int $branchId,
        string $type,
        ?int $itemId,
        int $quantity,
        string $source,
        ?int $userId
    ): void {
        if ($quantity === 0) {
            return;
        }

        if (!$itemId) {
            throw new InvalidArgumentException('Choose an inventory item for each sachet quantity greater than zero.');
        }

        $systemKey = $this->systemKey($type);
        $namePattern = $type === 'detergent' ? '%detergent%' : '%downy%';
        $item = DB::table('inventory')
            ->where('id', $itemId)
            ->where('branch_id', $branchId)
            ->whereRaw("LOWER(unit) = 'sachets'")
            ->where(function ($query) use ($systemKey, $namePattern) {
                $query->where('system_key', $systemKey)
                    ->orWhereRaw('LOWER(item_name) LIKE ?', [$namePattern]);
            })
            ->lockForUpdate()
            ->first();

        if (!$item) {
            throw new InvalidArgumentException('The selected sachet item is invalid for this branch.');
        }

        $before = (int) $item->stock_level;
        if ($quantity > $before) {
            throw new InvalidArgumentException("Insufficient stock for {$item->item_name}. Available: {$before}.");
        }

        $after = $before - $quantity;
        DB::table('inventory')->where('id', $item->id)->update([
            'stock_level' => $after,
            'last_updated' => now(),
        ]);

        InventoryHistoryService::record($item, $before, $after, $source, $userId);
    }

    private function systemKey(string $type): string
    {
        if (!in_array($type, ['detergent', 'softener'], true)) {
            throw new InvalidArgumentException('Unsupported sachet inventory type.');
        }

        return $type;
    }
}
