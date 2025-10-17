<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class SubscriptionManagement extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected string $view = 'filament.pages.subscription-management';

    protected static ?string $title = 'Upravljanje pretplatom';

    protected static ?int $navigationSort = 20;

    public string $billingCycle = 'monthly';

    public static function getNavigationGroup(): ?string
    {
        return 'Moja kompanija';
    }

    public static function getNavigationLabel(): string
    {
        return 'Pretplata';
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        if ($user->is_grandfathered) {
            return 'Grandfather';
        }

        if ($user->subscribed('default')) {
            $subscription = $user->subscription('default');

            if ($subscription->onTrial()) {
                return 'Probni period';
            }

            return 'Aktivna';
        }

        return 'Free';
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $user = Auth::user();

        if ($user->is_grandfathered) {
            return 'success';
        }

        if ($user->subscribed('default')) {
            $subscription = $user->subscription('default');

            if ($subscription->onTrial()) {
                return 'warning';
            }

            return 'success';
        }

        return 'gray';
    }

    public function getTitle(): string
    {
        return 'Upravljanje pretplatom';
    }

    public function mount(): void
    {
        // Any initialization logic
    }

    /**
     * Get current user's subscription status
     */
    public function getSubscriptionStatus(): array
    {
        $user = Auth::user();
        $plans = config('subscriptions.plans');

        if ($user->is_grandfathered) {
            return [
                'status' => 'grandfathered',
                'plan_name' => 'Grandfather (Besplatno zauvek)',
                'description' => 'Imate neograničen pristup svim funkcijama zauvek.',
                'monthly_invoices' => PHP_INT_MAX,
                'current_invoices' => $user->getMonthlyInvoiceCount(),
            ];
        }

        if ($user->subscribed('default')) {
            $subscription = $user->subscription('default');

            return [
                'status' => 'active',
                'plan_name' => 'Basic',
                'description' => 'Neograničen broj faktura',
                'monthly_invoices' => PHP_INT_MAX,
                'current_invoices' => $user->getMonthlyInvoiceCount(),
                'billing_cycle' => $subscription->stripe_price ?? 'Monthly',
                'next_billing_date' => $subscription->asStripeSubscription()->current_period_end ?? null,
                'on_trial' => $subscription->onTrial(),
                'trial_ends_at' => $subscription->trial_ends_at,
            ];
        }

        return [
            'status' => 'free',
            'plan_name' => 'Free',
            'description' => $plans['free']['description'],
            'monthly_invoices' => 3,
            'current_invoices' => $user->getMonthlyInvoiceCount(),
        ];
    }

    /**
     * Subscribe to monthly plan
     */
    public function subscribeMonthly(): void
    {
        $user = Auth::user();
        $priceId = config('subscriptions.plans.basic_monthly.stripe_price_id');

        if (empty($priceId)) {
            Notification::make()
                ->title('Greška')
                ->body('Stripe Price ID nije konfigurisan. Molimo kontaktirajte podršku.')
                ->danger()
                ->send();

            return;
        }

        try {
            $checkout = $user
                ->newSubscription('default', $priceId)
                ->trialDays(7)
                ->checkout([
                    'success_url' => route('filament.admin.pages.subscription-management').'?success=true',
                    'cancel_url' => route('filament.admin.pages.subscription-management').'?canceled=true',
                ]);

            $this->redirect($checkout->url);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Greška')
                ->body('Došlo je do greške prilikom kreiranja pretplate: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Subscribe to yearly plan
     */
    public function subscribeYearly(): void
    {
        $user = Auth::user();
        $priceId = config('subscriptions.plans.basic_yearly.stripe_price_id');

        if (empty($priceId)) {
            Notification::make()
                ->title('Greška')
                ->body('Stripe Price ID nije konfigurisan. Molimo kontaktirajte podršku.')
                ->danger()
                ->send();

            return;
        }

        try {
            $checkout = $user
                ->newSubscription('default', $priceId)
                ->trialDays(7)
                ->checkout([
                    'success_url' => route('filament.admin.pages.subscription-management').'?success=true',
                    'cancel_url' => route('filament.admin.pages.subscription-management').'?canceled=true',
                ]);

            $this->redirect($checkout->url);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Greška')
                ->body('Došlo je do greške prilikom kreiranja pretplate: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Open Stripe billing portal
     */
    public function manageBilling(): void
    {
        $user = Auth::user();

        try {
            $this->redirect($user->billingPortalUrl(
                route('filament.admin.pages.subscription-management')
            ));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Greška')
                ->body('Došlo je do greške: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        if ($user->is_grandfathered) {
            return [];
        }

        if ($user->subscribed('default')) {
            return [
                Action::make('manage_billing')
                    ->label('Upravljaj naplatom')
                    ->icon('heroicon-o-credit-card')
                    ->action('manageBilling'),
            ];
        }

        return [];
    }
}
