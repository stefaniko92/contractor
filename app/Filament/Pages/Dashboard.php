<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CurrentYearIncomeGroup;
use App\Filament\Widgets\PendingObligationsWidget;
use App\Filament\Widgets\TwelveMonthIncomeGroup;
use App\Filament\Widgets\UnpaidInvoicesWidget;
use App\Filament\Widgets\WelcomeWidget;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\View\View;

class Dashboard extends BaseDashboard
{
    public function mount(): void
    {
        if (auth()->user()->onboarding_seen_at === null) {
            $this->mountAction('onboarding');
        }
    }

    protected function getHeaderActions(): array
    {
        return [$this->onboardingAction()];
    }

    public function onboardingAction(): Action
    {
        return Action::make('onboarding')
            ->label('Vodič za početak')
            ->icon('heroicon-o-book-open')
            ->color('gray')
            ->modalHeading('Prvi koraci u Pausalci')
            ->modalWidth('2xl')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->mountUsing(function (): void {
                $user = auth()->user();

                if ($user->onboarding_seen_at === null) {
                    $user->onboarding_seen_at = now();
                    $user->save();
                }
            })
            ->modalContent(fn (): View => view('filament.pages.onboarding-guide', [
                'companyUrl' => CompanyInfo::getUrl(),
            ]));
    }

    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }

    public function getWidgets(): array
    {
        return [
            // First row: Welcome + Neplaćene fakture + Obaveze za plaćanje
            WelcomeWidget::class,
            UnpaidInvoicesWidget::class,
            PendingObligationsWidget::class,

            // Second row: Left (50%) - Current year stats + chart
            CurrentYearIncomeGroup::class,

            // Second row: Right (50%) - 12-month stats + chart
            TwelveMonthIncomeGroup::class,
        ];
    }
}
