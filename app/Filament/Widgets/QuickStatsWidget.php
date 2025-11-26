<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Obligation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class QuickStatsWidget extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 1;

    protected function getColumns(): int
    {
        return 1;
    }

    protected function getStats(): array
    {
        $userId = Auth::id();

        // Count uncharged invoices (neplaćene)
        $unpaidInvoices = Invoice::where('user_id', $userId)
            ->where('status', 'uncharged')
            ->count();

        // Count pending obligations
        $pendingObligations = Obligation::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        return [
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