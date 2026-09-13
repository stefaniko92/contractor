<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\EfakturaInvoice;
use App\Models\Invoice;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OperationsOverview extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected string $view = 'filament.pages.operations-overview';

    protected static ?string $title = 'Operativni pregled';

    protected static ?string $navigationLabel = 'Operativni pregled';

    protected static string|\UnitEnum|null $navigationGroup = 'Administracija';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    /**
     * @return array{stats: array<string, int>, funnel: list<array{label: string, count: int, rate: ?float}>, blockers: Collection<int, array{step: string, name: string, email: string, created_at: string, url: string}>, drafts: Collection<int, array{invoice_number: string, user: string, client: string, age: string, url: string}>, sef_failures: Collection<int, array{invoice_number: string, user: string, status: string, error: string, occurred_at: string, url: string}>}
     */
    public function getOperations(): array
    {
        $since = now()->subDays(30);
        $newUsers = User::query()->where('created_at', '>=', $since);
        $registered = (clone $newUsers)->count();
        $withCompany = (clone $newUsers)->whereHas('userCompany')->count();
        $withClient = (clone $newUsers)->whereHas('clients')->count();
        $withInvoice = (clone $newUsers)->whereHas('invoices', function ($query): void {
            $query->where('invoice_document_type', 'faktura');
        })->count();
        $issuedInvoice = (clone $newUsers)->whereHas('invoices', function ($query): void {
            $query
                ->where('invoice_document_type', 'faktura')
                ->whereIn('status', ['issued', 'sent', 'charged', 'uncharged']);
        })->count();

        return [
            'stats' => [
                'total_users' => User::query()->count(),
                'new_users' => $registered,
                'paid_users' => User::query()
                    ->where('is_grandfathered', false)
                    ->whereHas('subscriptions', function ($query): void {
                        $query
                            ->where('stripe_status', 'active')
                            ->where('stripe_id', 'not like', 'free_plan_%');
                    })
                    ->count(),
                'trials' => User::query()
                    ->whereHas('subscriptions', function ($query): void {
                        $query
                            ->where('stripe_status', 'trialing')
                            ->where('trial_ends_at', '>', now());
                    })
                    ->count(),
                'sef_failures' => EfakturaInvoice::query()
                    ->whereNotNull('last_error')
                    ->where('last_error_at', '>=', $since)
                    ->count(),
                'drafts_older_than_day' => Invoice::query()
                    ->where('invoice_document_type', 'faktura')
                    ->where('status', 'in_preparation')
                    ->where('created_at', '<=', now()->subDay())
                    ->count(),
            ],
            'funnel' => [
                $this->funnelStep('Registrovani korisnici', $registered, $registered),
                $this->funnelStep('Uneli podatke o kompaniji', $withCompany, $registered),
                $this->funnelStep('Dodali prvog klijenta', $withClient, $registered),
                $this->funnelStep('Kreirali prvu fakturu', $withInvoice, $registered),
                $this->funnelStep('Izdali prvu fakturu', $issuedInvoice, $registered),
            ],
            'blockers' => $this->getBlockers($since),
            'drafts' => Invoice::query()
                ->with(['user:id,name', 'client:id,company_name'])
                ->where('invoice_document_type', 'faktura')
                ->where('status', 'in_preparation')
                ->where('created_at', '<=', now()->subDay())
                ->oldest('created_at')
                ->limit(8)
                ->get()
                ->map(fn (Invoice $invoice): array => [
                    'invoice_number' => $invoice->invoice_number,
                    'user' => $invoice->user->name,
                    'client' => $invoice->client->company_name,
                    'age' => $invoice->created_at->diffForHumans(),
                    'url' => InvoiceResource::getUrl('edit', ['record' => $invoice]),
                ]),
            'sef_failures' => EfakturaInvoice::query()
                ->with(['user:id,name', 'invoice:id,invoice_number'])
                ->whereNotNull('last_error')
                ->where('last_error_at', '>=', $since)
                ->latest('last_error_at')
                ->limit(8)
                ->get()
                ->map(fn (EfakturaInvoice $sefInvoice): array => [
                    'invoice_number' => $sefInvoice->invoice?->invoice_number ?? 'Obrisana faktura',
                    'user' => $sefInvoice->user->name,
                    'status' => $sefInvoice->status,
                    'error' => str($sefInvoice->last_error)->squish()->limit(140),
                    'occurred_at' => $sefInvoice->last_error_at->diffForHumans(),
                    'url' => $sefInvoice->invoice
                        ? InvoiceResource::getUrl('edit', ['record' => $sefInvoice->invoice])
                        : UserResource::getUrl('edit', ['record' => $sefInvoice->user]),
                ]),
        ];
    }

    /**
     * @return Collection<int, array{step: string, name: string, email: string, created_at: string, url: string}>
     */
    protected function getBlockers(\DateTimeInterface $since): Collection
    {
        return collect([
            ...$this->blockerUsers('Nema podatke o kompaniji', $since, fn ($query) => $query->whereDoesntHave('userCompany')),
            ...$this->blockerUsers('Nema nijednog klijenta', $since, fn ($query) => $query->whereHas('userCompany')->whereDoesntHave('clients')),
            ...$this->blockerUsers('Nema nijednu fakturu', $since, fn ($query) => $query->whereHas('clients')->whereDoesntHave('invoices', function ($query): void {
                $query->where('invoice_document_type', 'faktura');
            })),
        ])
            ->sortByDesc('created_at')
            ->take(12)
            ->values();
    }

    /**
     * @param  callable(Builder<User>): Builder<User>  $constraint
     * @return list<array{step: string, name: string, email: string, created_at: string, url: string}>
     */
    protected function blockerUsers(string $step, \DateTimeInterface $since, callable $constraint): array
    {
        return $constraint(User::query()->where('created_at', '>=', $since))
            ->latest('created_at')
            ->limit(4)
            ->get(['id', 'name', 'email', 'created_at'])
            ->map(fn (User $user): array => [
                'step' => $step,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->diffForHumans(),
                'url' => UserResource::getUrl('edit', ['record' => $user]),
            ])
            ->all();
    }

    /**
     * @return array{label: string, count: int, rate: ?float}
     */
    protected function funnelStep(string $label, int $count, int $registered): array
    {
        return [
            'label' => $label,
            'count' => $count,
            'rate' => $registered === 0 ? null : round(($count / $registered) * 100, 1),
        ];
    }
}
