<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TwelveMonthIncomeGroup extends Widget
{
    protected string $view = 'filament.widgets.twelve-month-income-group';

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];
}
