<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;

class TransactionHistoryFilter
{
    public static function applyDateRangeWithActiveOrders(Builder $query, string $startDate, string $endDate): void
    {
        $query->where(function (Builder $dateQuery) use ($startDate, $endDate) {
            $dateQuery->whereBetween('t.created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ])->orWhere(function (Builder $activeQuery) {
                $activeQuery->where('t.order_status', '!=', 'Cancelled')
                    ->where(function (Builder $incompleteQuery) {
                        $incompleteQuery->whereNull('t.order_status')
                            ->orWhere('t.order_status', '!=', 'Claimed')
                            ->orWhereNull('t.payment_status')
                            ->orWhere('t.payment_status', '!=', 'Paid');
                    });
            });
        });
    }
}
