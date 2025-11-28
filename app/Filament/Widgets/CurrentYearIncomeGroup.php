<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CurrentYearIncomeGroup extends Widget
{
    protected string $view = 'filament.widgets.current-year-income-group';

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];
}
