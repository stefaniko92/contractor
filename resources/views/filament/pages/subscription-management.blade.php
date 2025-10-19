<x-filament-panels::page>
    @php
        $status = $this->getSubscriptionStatus();
        $plans = config('subscriptions.plans');
        $user = Auth::user();
    @endphp

    {{-- Current Status --}}
    <x-filament::section>
        <x-slot name="heading">
            Trenutni status pretplate
        </x-slot>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-lg font-semibold">{{ $status['plan_name'] }}</span>
                        @if($status['status'] === 'active')
                            <x-filament::badge color="success">Aktivna</x-filament::badge>
                            @if(isset($status['on_trial']) && $status['on_trial'])
                                <x-filament::badge color="warning">
                                    Probni period ({{ \Carbon\Carbon::parse($status['trial_ends_at'])->diffInDays() }} dana)
                                </x-filament::badge>
                            @endif
                        @elseif($status['status'] === 'grandfathered')
                            <x-filament::badge color="warning">Grandfather</x-filament::badge>
                        @else
                            <x-filament::badge color="gray">Free</x-filament::badge>
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        @if($status['status'] === 'free')
                            {{ $status['current_invoices'] }} / {{ $status['monthly_invoices'] }} faktura ovog meseca
                        @else
                            {{ $status['current_invoices'] }} faktura kreirano ovog meseca
                        @endif
                    </p>
                </div>
                @if(isset($status['next_billing_date']))
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Sledeća naplata: {{ \Carbon\Carbon::createFromTimestamp($status['next_billing_date'])->format('d.m.Y') }}
                    </div>
                @endif
            </div>

            @if($status['status'] === 'free' && $status['current_invoices'] > 0)
                <div class="pt-4 border-t">
                    <div class="flex justify-between text-sm mb-2">
                        <span>Iskorišćenost</span>
                        <span>{{ round(($status['current_invoices'] / $status['monthly_invoices']) * 100) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        @php
                            $percentage = min(($status['current_invoices'] / $status['monthly_invoices']) * 100, 100);
                        @endphp
                        <div class="h-2 rounded-full {{ $percentage >= 100 ? 'bg-red-500' : 'bg-primary-500' }}"
                             style="width: {{ $percentage }}%">
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>

    {{-- Pricing Plans --}}
    @if($status['status'] !== 'grandfathered')
        <x-filament::section>
            <x-slot name="heading">
                Dostupni planovi
            </x-slot>
            <x-slot name="description">
                Izaberite plan koji vam najbolje odgovara
            </x-slot>

            <div class="fi-sc fi-sc-has-gap fi-grid" style="display: grid; gap: 1.5rem; grid-template-columns: repeat(1, minmax(0, 1fr)); --cols-lg: repeat(3, minmax(0, 1fr));">
                <style>
                    @media (min-width: 1024px) {
                        .fi-grid[style*="--cols-lg"] {
                            grid-template-columns: var(--cols-lg) !important;
                        }
                    }
                </style>
                {{-- Free Plan --}}
                <x-filament::section
                    icon="heroicon-o-gift"
                    icon-color="success"
                    collapsible
                    :collapsed="false">
                    <x-slot name="heading">
                        Free Plan
                    </x-slot>
                    <x-slot name="description">
                        Za početak
                    </x-slot>

                    @php
                        $freeFeatures = $plans['free']['features'];
                        if ($status['status'] === 'free') {
                            $freeButtonLabel = 'Trenutni plan';
                            $freeButtonColor = 'gray';
                            $freeButtonDisabled = true;
                        } else {
                            $freeButtonLabel = 'Free plan';
                            $freeButtonColor = 'gray';
                            $freeButtonDisabled = true;
                        }
                    @endphp

                    {!! view('filament.components.plan-features', [
                        'features' => $freeFeatures,
                        'price' => '0 RSD',
                        'yearly_price' => 'Zauvek besplatno',
                        'buttonLabel' => $freeButtonLabel,
                        'buttonColor' => $freeButtonColor,
                        'outlined' => false,
                        'disabled' => $freeButtonDisabled,
                        'showBadge' => $status['status'] === 'free',
                        'badgeLabel' => 'Trenutni plan',
                    ]) !!}
                </x-filament::section>

                {{-- Basic Monthly --}}
                <x-filament::section
                    icon="heroicon-o-star"
                    icon-color="primary"
                    collapsible
                    :collapsed="false">
                    <x-slot name="heading">
                        Basic - Mesečno
                    </x-slot>
                    <x-slot name="description">
                        Neograničeno fakturisanje
                    </x-slot>

                    @php
                        $isCurrentMonthly = $status['status'] === 'active' && isset($status['billing_cycle']) && str_contains(strtolower($status['billing_cycle']), 'month');
                        if ($status['status'] === 'free') {
                            $monthlyButtonLabel = 'Započni besplatno (7 dana)';
                            $monthlyButtonColor = 'primary';
                            $monthlyButtonOutlined = false;
                            $monthlyButtonAction = 'subscribeMonthly';
                        } elseif ($isCurrentMonthly) {
                            $monthlyButtonLabel = 'Trenutni plan';
                            $monthlyButtonColor = 'primary';
                            $monthlyButtonOutlined = false;
                            $monthlyButtonAction = null;
                        } else {
                            $monthlyButtonLabel = 'Promeni na mesečno';
                            $monthlyButtonColor = 'primary';
                            $monthlyButtonOutlined = true;
                            $monthlyButtonAction = 'subscribeMonthly';
                        }
                    @endphp

                    {!! view('filament.components.plan-features', [
                        'features' => $plans['basic_monthly']['features'],
                        'price' => number_format($plans['basic_monthly']['price']) . ' RSD',
                        'yearly_price' => 'Po mesecu',
                        'buttonLabel' => $monthlyButtonLabel,
                        'buttonColor' => $monthlyButtonColor,
                        'outlined' => $monthlyButtonOutlined,
                        'disabled' => $monthlyButtonAction === null,
                        'wireClick' => $monthlyButtonAction,
                        'showBadge' => $isCurrentMonthly,
                        'badgeLabel' => 'Trenutni plan',
                    ]) !!}
                </x-filament::section>

                {{-- Basic Yearly --}}
                <x-filament::section
                    icon="heroicon-o-sparkles"
                    icon-color="warning"
                    collapsible
                    :collapsed="false">
                    <x-slot name="heading">
                        Basic - Godišnje
                    </x-slot>
                    <x-slot name="description">
                        Neograničeno fakturisanje + ušteda
                    </x-slot>
                    @php
                        $isCurrentYearly = $status['status'] === 'active' && isset($status['billing_cycle']) && str_contains(strtolower($status['billing_cycle']), 'year');
                    @endphp
                    @if(!$isCurrentYearly)
                        <x-slot name="headerEnd">
                            <x-filament::badge color="success">
                                Preporučeno
                            </x-filament::badge>
                        </x-slot>
                    @endif

                    @php
                        if ($status['status'] === 'free') {
                            $yearlyButtonLabel = 'Započni besplatno (7 dana)';
                            $yearlyButtonColor = 'primary';
                            $yearlyButtonOutlined = false;
                            $yearlyButtonAction = 'subscribeYearly';
                        } elseif ($isCurrentYearly) {
                            $yearlyButtonLabel = 'Trenutni plan';
                            $yearlyButtonColor = 'primary';
                            $yearlyButtonOutlined = false;
                            $yearlyButtonAction = null;
                        } else {
                            $yearlyButtonLabel = 'Promeni na godišnje';
                            $yearlyButtonColor = 'primary';
                            $yearlyButtonOutlined = true;
                            $yearlyButtonAction = 'subscribeYearly';
                        }

                        $yearlyFeatures = array_merge(
                            $plans['basic_yearly']['features'],
                            ['Ušteda od 2 meseca']
                        );
                    @endphp

                    {!! view('filament.components.plan-features', [
                        'features' => $yearlyFeatures,
                        'price' => number_format($plans['basic_yearly']['price']) . ' RSD',
                        'yearly_price' => 'Po godini',
                        'savingsBadge' => 'Ušteda ' . number_format($plans['basic_yearly']['savings']) . ' RSD',
                        'buttonLabel' => $yearlyButtonLabel,
                        'buttonColor' => $yearlyButtonColor,
                        'outlined' => $yearlyButtonOutlined,
                        'disabled' => $yearlyButtonAction === null,
                        'wireClick' => $yearlyButtonAction,
                        'showBadge' => $isCurrentYearly,
                        'badgeLabel' => 'Trenutni plan',
                    ]) !!}
                </x-filament::section>
            </div>

            {{-- Trust Info --}}
            <div class="text-center text-sm text-gray-600 dark:text-gray-400 mt-6">
                <p>Sigurna naplata preko Stripe • Sve kartice prihvaćene • Otkaži bilo kada</p>
            </div>
        </x-filament::section>
    @endif

    {{-- FAQ --}}
    <x-filament::section>
        <x-slot name="heading">
            Često postavljana pitanja
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold mb-2">Kako funkcioniše probni period?</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Dobijate 7 dana besplatnog probnog perioda. Možete otkazati bilo kada pre isteka probnog perioda bez naplate.
                </p>
            </div>

            <div>
                <h4 class="font-semibold mb-2">Mogu li otkazati pretplatu?</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Da, možete otkazati pretplatu bilo kada. Imaćete pristup do kraja naplaćenog perioda.
                </p>
            </div>

            <div>
                <h4 class="font-semibold mb-2">Šta se dešava posle otkazivanja?</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Nakon otkazivanja, prelazite na Free plan sa limitom od 3 fakture mesečno.
                </p>
            </div>

            <div>
                <h4 class="font-semibold mb-2">Koje načine plaćanja prihvatate?</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Prihvatamo sve glavne kreditne i debitne kartice putem Stripe platforme.
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
