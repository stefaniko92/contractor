<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CurrentYearIncomeWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected function getColumns(): int
    {
        return 2;
    }

    protected function getStats(): array
    {
        $userId = Auth::id();
        $currentYear = now()->year;

        // Calculate annual income from domestic invoices only
        $annualIncome = Invoice::where('invoices.user_id', $userId)
            ->join('clients', 'invoices.client_id', '=', 'clients.id')
            ->where('clients.is_domestic', true)
            ->whereYear('invoices.issue_date', $currentYear)
            ->whereNotNull('invoices.issue_date')
            ->sum('invoices.amount');

        // Pausalac limit for current year (6 million RSD)
        $pausalaciLimit = 6000000;
        $remainingLimit = $pausalaciLimit - $annualIncome;
        $percentageUsed = ($annualIncome / $pausalaciLimit) * 100;

        $color = 'success';
        if ($percentageUsed > 80) {
            $color = 'danger';
        } elseif ($percentageUsed > 60) {
            $color = 'warning';
        }

        $icon = $percentageUsed > 80 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle';

        return [
            Stat::make('Godišnji prihod', number_format($annualIncome, 0, ',', '.').' RSD')
                ->description('Od ukupno '.number_format($pausalaciLimit, 0, ',', '.').' RSD')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($color)
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Preostalo do limita', number_format($remainingLimit, 0, ',', '.').' RSD')
                ->description(number_format($percentageUsed, 1).'% iskorišćeno')
                ->descriptionIcon($icon)
                ->color($color),
        ];
    }
}
