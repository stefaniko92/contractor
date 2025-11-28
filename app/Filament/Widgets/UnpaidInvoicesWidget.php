<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class UnpaidInvoicesWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

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

        return [
            Stat::make('Neplaćene fakture', $unpaidInvoices)
                ->description('Čekaju uplatu')
                ->descriptionIcon('heroicon-m-clock')
                ->color($unpaidInvoices > 5 ? 'warning' : 'info'),
        ];
    }
}
