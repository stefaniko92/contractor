<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CurrentYearIncomeChart;
use App\Filament\Widgets\CurrentYearIncomeWidget;
use App\Filament\Widgets\QuickStatsWidget;
use App\Filament\Widgets\TwelveMonthIncomeChart;
use App\Filament\Widgets\TwelveMonthIncomeWidget;
use App\Filament\Widgets\WelcomeWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int | array
    {
        return [
            'md' => 1,
            'xl' => 2,
        ];
    }

    public function getWidgets(): array
    {
        return [
            // First row: Welcome (left) + Quick stats (right)
            WelcomeWidget::class,
            QuickStatsWidget::class,

            // Second row: Current year (6M) stats (left) + 12-month (8M) stats (right)
            CurrentYearIncomeWidget::class,
            TwelveMonthIncomeWidget::class,

            // Third row: Pie charts
            CurrentYearIncomeChart::class,
            TwelveMonthIncomeChart::class,
        ];
    }
}