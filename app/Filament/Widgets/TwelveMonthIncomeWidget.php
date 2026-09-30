<?php

namespace App\Filament\Widgets;

use App\PausalIncomeAnalytics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TwelveMonthIncomeWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected function getColumns(): int
    {
        return 2;
    }

    protected function getStats(): array
    {
        $analytics = app(PausalIncomeAnalytics::class)->forDate(Auth::id(), now());

        $color = 'success';
        if ($analytics['percentage_used'] > 80) {
            $color = 'danger';
        } elseif ($analytics['percentage_used'] > 60) {
            $color = 'warning';
        }

        $icon = $analytics['percentage_used'] > 80 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle';

        return [
            Stat::make('Prihod u 365 dana', number_format($analytics['income'], 0, ',', '.').' RSD')
                ->description('Od ukupno '.number_format($analytics['limit'], 0, ',', '.').' RSD')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($color)
                ->chart([12, 15, 18, 20, 22, 25, 28, 30, 32, 35, 38, 40]),

            Stat::make('Preostalo do limita', number_format($analytics['remaining'], 0, ',', '.').' RSD')
                ->description(number_format($analytics['percentage_used'], 1).'% iskorišćeno')
                ->descriptionIcon($icon)
                ->color($color),
        ];
    }
}
