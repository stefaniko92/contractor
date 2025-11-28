<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CurrentYearIncomeProgress extends Widget
{
    protected string $view = 'filament.widgets.current-year-income-progress';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isDiscovered = false;
}
