<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class InvoicesOverview extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.invoices-overview';
}
