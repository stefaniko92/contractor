<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CurrentYearIncomeGroup;
use App\Filament\Widgets\PendingObligationsWidget;
use App\Filament\Widgets\TwelveMonthIncomeGroup;
use App\Filament\Widgets\UnpaidInvoicesWidget;
use App\Filament\Widgets\WelcomeWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }

    public function getWidgets(): array
    {
        return [
            // First row: Welcome + Neplaćene fakture + Obaveze za plaćanje
            WelcomeWidget::class,
            UnpaidInvoicesWidget::class,
            PendingObligationsWidget::class,

            // Second row: Left (50%) - Current year stats + chart
            CurrentYearIncomeGroup::class,

            // Second row: Right (50%) - 12-month stats + chart
            TwelveMonthIncomeGroup::class,
        ];
    }
}
