<?php

namespace App\Services;

use App\Exceptions\DailyServiceRequestLimitReached;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DailyServiceRequestLimit
{
    private const DAILY_LIMIT = 20;

    public function assertCanSubmit(int $branchId, int $userId, Carbon $requestedAt): void
    {
        $user = DB::table('users')
            ->where('id', $userId)
            ->lockForUpdate()
            ->first(['id']);

        if (!$user) {
            throw new RuntimeException('The customer account could not be found.');
        }

        $branch = DB::table('branches')
            ->where('id', $branchId)
            ->lockForUpdate()
            ->first(['id']);

        if (!$branch) {
            throw new RuntimeException('The service branch could not be found.');
        }

        if ($this->hasUserRequestedToday($userId, $requestedAt)) {
            throw new DailyServiceRequestLimitReached(
                'You have already submitted a service request today. You can request again tomorrow.'
            );
        }

        if ($this->isFull($branchId, $requestedAt)) {
            throw new DailyServiceRequestLimitReached(
                'This branch has reached its limit of 20 service requests for today. Requests will open again tomorrow.'
            );
        }
    }

    public function record(int $branchId, int $userId, int $transactionId, Carbon $requestedAt): void
    {
        DB::table('daily_service_requests')->insert([
            'branch_id' => $branchId,
            'user_id' => $userId,
            'transaction_id' => $transactionId,
            'request_date' => $requestedAt->copy()->setTimezone('Asia/Manila')->toDateString(),
            'created_at' => $requestedAt,
        ]);
    }

    public function isFull(int $branchId, Carbon $date): bool
    {
        $requestDate = $date->copy()->setTimezone('Asia/Manila')->toDateString();

        return DB::table('daily_service_requests')
            ->where('branch_id', $branchId)
            ->where('request_date', $requestDate)
            ->count() >= self::DAILY_LIMIT;
    }

    public function hasUserRequestedToday(int $userId, Carbon $date): bool
    {
        $requestDate = $date->copy()->setTimezone('Asia/Manila')->toDateString();

        return DB::table('daily_service_requests')
            ->where('user_id', $userId)
            ->where('request_date', $requestDate)
            ->exists();
    }
}
