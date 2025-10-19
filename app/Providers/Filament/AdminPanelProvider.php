<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use App\Filament\Pages\SubscriptionManagement;
use App\Http\Middleware\SetLocale;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->registration(Register::class)
            ->passwordReset()
            ->brandLogo(function () {
                $currentRoute = request()->route()?->getName();
                $authRoutes = ['filament.admin.auth.login', 'filament.admin.auth.register', 'filament.admin.auth.password-reset.request', 'filament.admin.auth.password-reset.reset'];

                if (in_array($currentRoute, $authRoutes)) {
                    return asset('images/pausalci-logo-transparent.png');
                }

                return asset('images/pausalci-small.png');
            })
            ->brandLogoHeight('2.5rem')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                SetLocale::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('Fakturisanje')
                    ->icon('heroicon-o-document-text'),
                NavigationGroup::make('Moja kompanija')
                    ->icon('heroicon-o-building-office'),
            ])
            ->userMenuItems([
                'subscription' => MenuItem::make()
                    ->label(function () {
                        $user = Auth::user();
                        if ($user->is_grandfathered) {
                            return 'Pretplata (Grandfather)';
                        }
                        if ($user->subscribed('default')) {
                            return 'Pretplata (Aktivna)';
                        }

                        return 'Pretplata (Free)';
                    })
                    ->url(fn () => SubscriptionManagement::getUrl())
                    ->icon('heroicon-o-credit-card'),
                'logout' => fn (Action $action) => $action->label('Odjavi se'),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => view('filament.components.language-switcher')
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => '<style>
                    .fi-form-actions,
                    .fi-fo-actions,
                    form > .fi-section:last-child,
                    [wire\\:submit] > div:last-child {
                        margin-top: 15px !important;
                    }
                </style>'
            );
    }
}
