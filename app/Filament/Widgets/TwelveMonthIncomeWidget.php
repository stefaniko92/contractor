<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TwelveMonthIncomeWidget extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 2;

    protected function getStats(): array
    {
        $userId = Auth::id();

        // Calculate income from domestic invoices in the last 12 months
        $twelveMonthsAgo = now()->subMonths(12);

        $twelveMonthIncome = Invoice::where('invoices.user_id', $userId)
            ->join('clients', 'invoices.client_id', '=', 'clients.id')
            ->where('clients.is_domestic', true)
            ->where('invoices.issue_date', '>=', $twelveMonthsAgo)
            ->whereNotNull('invoices.issue_date')
            ->sum('invoices.amount');

        // Limit for last 12 months (8 million RSD)
        $twelveMonthLimit = 8000000;
        $remainingTwelveMonth = $twelveMonthLimit - $twelveMonthIncome;
        $percentageUsedTwelveMonth = ($twelveMonthIncome / $twelveMonthLimit) * 100;

        return [
            Stat::make('Prihod u 12 meseci', number_format($twelveMonthIncome, 0, ',', '.').' RSD')
                ->description('Od ukupno '.number_format($twelveMonthLimit, 0, ',', '.').' RSD')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($percentageUsedTwelveMonth > 80 ? 'danger' : ($percentageUsedTwelveMonth > 60 ? 'warning' : 'success'))
                ->chart([12, 15, 18, 20, 22, 25, 28, 30, 32, 35, 38, 40]),

            Stat::make('Preostalo do limita (12 mes.)', number_format($remainingTwelveMonth, 0, ',', '.').' RSD')
                ->description(number_format($percentageUsedTwelveMonth, 1).'% iskorišćeno')
                ->descriptionIcon($percentageUsedTwelveMonth > 80 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($percentageUsedTwelveMonth > 80 ? 'danger' : ($percentageUsedTwelveMonth > 60 ? 'warning' : 'success')),
        ];
    }
}