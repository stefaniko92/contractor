<?php

namespace App;

use App\Models\Invoice;
use Carbon\CarbonInterface;

class PausalIncomeAnalytics
{
    /**
     * @return array{
     *     period_start: CarbonInterface,
     *     period_end: CarbonInterface,
     *     income: float,
     *     remaining: float,
     *     percentage_used: float,
     *     falling_off: float,
     *     change_from_today: float,
     *     limit: int
     * }
     */
    public function forDate(int $userId, CarbonInterface $referenceDate): array
    {
        $periodEnd = $referenceDate->copy()->endOfDay();
        $periodStart = $periodEnd->copy()->subDays(364)->startOfDay();
        $today = now()->endOfDay();
        $todayPeriodStart = $today->copy()->subDays(364)->startOfDay();
        $incomeLimit = 8_000_000;

        $income = $this->incomeForPeriod($userId, $periodStart, $periodEnd);
        $todayIncome = $this->incomeForPeriod($userId, $todayPeriodStart, $today);

        $fallingOff = $periodStart->isAfter($todayPeriodStart)
            ? $this->incomeForPeriod($userId, $todayPeriodStart, $periodStart->copy()->subDay()->endOfDay())
            : 0.0;

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'income' => $income,
            'remaining' => $incomeLimit - $income,
            'percentage_used' => ($income / $incomeLimit) * 100,
            'falling_off' => $fallingOff,
            'change_from_today' => $income - $todayIncome,
            'limit' => $incomeLimit,
        ];
    }

    private function incomeForPeriod(int $userId, CarbonInterface $periodStart, CarbonInterface $periodEnd): float
    {
        return (float) Invoice::query()
            ->where('invoices.user_id', $userId)
            ->countsTowardsPausalIncome()
            ->join('clients', 'invoices.client_id', '=', 'clients.id')
            ->where('clients.is_domestic', true)
            ->whereNotNull('invoices.issue_date')
            ->whereBetween('invoices.issue_date', [$periodStart, $periodEnd])
            ->sum('invoices.amount');
    }
}
