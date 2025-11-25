<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class IconLayoutTest extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::CodeBracket;

    protected static ?string $title = 'Icon Layout Test';

    protected static ?string $navigationLabel = 'Icon Layout Test';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.icon-layout-test';

    public static function getNavigationGroup(): ?string
    {
        return 'Testing & Development';
    }
}
