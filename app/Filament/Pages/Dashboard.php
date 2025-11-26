<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PausalaciStatsOverview;
use App\Filament\Widgets\TwelveMonthIncomeWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    public function getWidgets(): array
    {
        return [
            PausalaciStatsOverview::class,
            TwelveMonthIncomeWidget::class,
            AccountWidget::class,
        ];
    }
}