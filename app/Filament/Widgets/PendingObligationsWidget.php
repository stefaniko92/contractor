<?php

namespace App\Filament\Widgets;

use App\Models\Obligation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PendingObligationsWidget extends StatsOverviewWidget
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

        // Count pending obligations
        $pendingObligations = Obligation::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        return [
            Stat::make('Obaveze za plaćanje', $pendingObligations)
                ->description('Porezi i doprinosi')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($pendingObligations > 0 ? 'warning' : 'success'),
        ];
    }
}
