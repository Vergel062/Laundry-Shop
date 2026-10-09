<?php

namespace App\Services;

use App\Exceptions\DailyServiceQueueFull;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyServiceQueue
{
    private const DAILY_LIMIT = 50;

    public function assignNext(int $branchId, Carbon $assignedAt): array
    {
        $queueDate = $assignedAt->copy()->setTimezone('Asia/Manila')->toDateString();
        $lastQueueNumber = $this->lockBranchAndGetLastQueueNumber($branchId, $queueDate);

        return [
            'queue_date' => $queueDate,
            'queue_number' => $lastQueueNumber + 1,
        ];
    }

    public function assertCanAcceptRequest(int $branchId, Carbon $requestedAt): void
    {
        $queueDate = $requestedAt->copy()->setTimezone('Asia/Manila')->toDateString();
        $lastQueueNumber = $this->lockBranchAndGetLastQueueNumber($branchId, $queueDate);

        if ($lastQueueNumber >= self::DAILY_LIMIT) {
            throw new DailyServiceQueueFull();
        }
    }

    public function isFull(int $branchId, Carbon $date): bool
    {
        $queueDate = $date->copy()->setTimezone('Asia/Manila')->toDateString();
        $count = DB::table('transactions')
            ->where('branch_id', $branchId)
            ->where('queue_date', $queueDate)
            ->whereNotNull('queue_number')
            ->count();

        return $count >= self::DAILY_LIMIT;
    }

    public function ensureExistingOrdersHaveNumbers(int $branchId): void
    {
        if (!$this->hasUnnumberedOrders($branchId)) {
            return;
        }

        DB::transaction(function () use ($branchId) {
            DB::table('branches')->where('id', $branchId)->lockForUpdate()->first();
            $this->backfillUnnumberedOrders($branchId);
        });
    }

    private function backfillUnnumberedOrders(int $branchId): void
    {
        $existingNumbers = DB::table('transactions')
            ->where('branch_id', $branchId)
            ->whereNotNull('queue_date')
            ->whereNotNull('queue_number')
            ->select('queue_date', DB::raw('MAX(queue_number) as last_number'))
            ->groupBy('queue_date')
            ->pluck('last_number', 'queue_date')
            ->all();

        $orders = DB::table('transactions')
            ->where('branch_id', $branchId)
            ->whereNull('queue_number')
            ->where(function ($query) {
                $query->whereNull('payment_status')
                    ->orWhere('payment_status', '!=', 'Service Request');
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->get(['id', 'created_at']);

        foreach ($orders as $order) {
            $queueDate = Carbon::parse($order->created_at, 'Asia/Manila')->toDateString();
            $nextNumber = (int) ($existingNumbers[$queueDate] ?? 0) + 1;

            DB::table('transactions')
                ->where('id', $order->id)
                ->update([
                    'queue_date' => $queueDate,
                    'queue_number' => $nextNumber,
                ]);

            $existingNumbers[$queueDate] = $nextNumber;
        }
    }

    private function lockBranchAndGetLastQueueNumber(int $branchId, string $queueDate): int
    {
        $branch = DB::table('branches')
            ->where('id', $branchId)
            ->lockForUpdate()
            ->first(['id']);

        if (!$branch) {
            throw new \RuntimeException('The service branch could not be found.');
        }

        $this->backfillUnnumberedOrders($branchId);

        $dayQueue = DB::table('transactions')
            ->where('branch_id', $branchId)
            ->where('queue_date', $queueDate);
        $assignedCount = (clone $dayQueue)->whereNotNull('queue_number')->count();
        $lastQueueNumber = (int) ((clone $dayQueue)->max('queue_number') ?? 0);

        if ($assignedCount >= self::DAILY_LIMIT || $lastQueueNumber >= self::DAILY_LIMIT) {
            throw new DailyServiceQueueFull();
        }

        return $lastQueueNumber;
    }

    private function hasUnnumberedOrders(int $branchId): bool
    {
        return DB::table('transactions')
            ->where('branch_id', $branchId)
            ->whereNull('queue_number')
            ->where(function ($query) {
                $query->whereNull('payment_status')
                    ->orWhere('payment_status', '!=', 'Service Request');
            })
            ->exists();
    }
}
