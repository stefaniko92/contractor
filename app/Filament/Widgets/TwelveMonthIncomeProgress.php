<?php

namespace App\Filament\Widgets;

use App\PausalIncomeAnalytics;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class TwelveMonthIncomeProgress extends Widget
{
    protected string $view = 'filament.widgets.twelve-month-income-progress';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    public string $projectionDate;

    public function mount(): void
    {
        $this->projectionDate = now()->toDateString();
    }

    public function showToday(): void
    {
        $this->projectionDate = now()->toDateString();
    }

    public function showNovemberProjection(): void
    {
        $novemberFirst = now()->setMonth(11)->startOfMonth();

        if ($novemberFirst->isPast()) {
            $novemberFirst->addYear();
        }

        $this->projectionDate = $novemberFirst->toDateString();
    }

    /**
     * @return array{analytics: array{period_start: Carbon, period_end: Carbon, income: float, remaining: float, percentage_used: float, falling_off: float, change_from_today: float, limit: int}}
     */
    protected function getViewData(): array
    {
        return [
            'analytics' => app(PausalIncomeAnalytics::class)->forDate(
                Auth::id(),
                Carbon::parse($this->projectionDate),
            ),
        ];
    }
}
