<?php

namespace App\Filament\Widgets;

use Filament\Widgets\AccountWidget;

class WelcomeWidget extends AccountWidget
{
    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];
}
