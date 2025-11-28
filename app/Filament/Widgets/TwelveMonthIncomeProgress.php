<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TwelveMonthIncomeProgress extends Widget
{
    protected string $view = 'filament.widgets.twelve-month-income-progress';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isDiscovered = false;
}
