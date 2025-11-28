<?php

namespace App\Filament\Widgets;

use App\Models\Income;
use App\Models\Invoice;
use App\Models\Obligation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PausalaciStatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 2;

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

        // Count uncharged invoices (neplaćene)
        $unpaidInvoices = Invoice::where('user_id', $userId)
            ->where('status', 'uncharged')
            ->count();

        // Count pending obligations
        $pendingObligations = Obligation::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        return [
            Stat::make('Godišnji prihod', number_format($annualIncome, 0, ',', '.').' RSD')
                ->description('Od ukupno '.number_format($pausalaciLimit, 0, ',', '.').' RSD')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($percentageUsed > 80 ? 'danger' : ($percentageUsed > 60 ? 'warning' : 'success'))
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Preostalo do limita', number_format($remainingLimit, 0, ',', '.').' RSD')
                ->description(number_format($percentageUsed, 1).'% iskorišćeno')
                ->descriptionIcon($percentageUsed > 80 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($percentageUsed > 80 ? 'danger' : ($percentageUsed > 60 ? 'warning' : 'success')),

            Stat::make('Neplaćene fakture', $unpaidInvoices)
                ->description('Čekaju uplatu')
                ->descriptionIcon('heroicon-m-clock')
                ->color($unpaidInvoices > 5 ? 'warning' : 'info'),

            Stat::make('Obaveze za plaćanje', $pendingObligations)
                ->description('Porezi i doprinosi')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($pendingObligations > 0 ? 'warning' : 'success'),
        ];
    }
}
