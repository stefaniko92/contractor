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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
            {{-- Free Plan --}}
            <div class="h-full">
            <x-filament::section class="h-full flex flex-col">
                <x-slot name="heading">
                    Free Plan
                </x-slot>
                <x-slot name="description">
                    Za početak
                </x-slot>

                <div class="space-y-6 flex-1 flex flex-col">
                    <div>
                        <div class="text-3xl font-bold">0 RSD</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Zauvek besplatno</div>
                    </div>

                    <ul class="space-y-2 text-sm flex-1">
                        @foreach($plans['free']['features'] as $feature)
                            <li class="flex gap-2">
                                <span class="text-green-500">✓</span>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-auto">
                        @if($status['status'] === 'free')
                            <x-filament::badge color="primary" class="mb-3">
                                Trenutni plan
                            </x-filament::badge>
                        @endif
                        <x-filament::button
                            disabled
                            color="gray"
                            class="w-full">
                            {{ $status['status'] === 'free' ? 'Trenutni plan' : 'Free plan' }}
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
            </div>

            {{-- Basic Monthly --}}
            <div class="h-full">
            <x-filament::section class="h-full flex flex-col">
                <x-slot name="heading">
                    Basic - Mesečno
                </x-slot>
                <x-slot name="description">
                    Neograničeno fakturisanje
                </x-slot>

                <div class="space-y-6 flex-1 flex flex-col">
                    <div>
                        <div class="text-3xl font-bold">{{ number_format($plans['basic_monthly']['price']) }} RSD</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Po mesecu</div>
                    </div>

                    <ul class="space-y-2 text-sm flex-1">
                        @foreach($plans['basic_monthly']['features'] as $feature)
                            <li class="flex gap-2">
                                <span class="text-green-500">✓</span>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-auto">
                        @if($status['status'] === 'active' && isset($status['billing_cycle']) && str_contains(strtolower($status['billing_cycle']), 'month'))
                            <x-filament::badge color="primary" class="mb-3">
                                Trenutni plan
                            </x-filament::badge>
                        @endif

                        @if($status['status'] === 'free')
                            <x-filament::button
                                wire:click="subscribeMonthly"
                                color="primary"
                                class="w-full">
                                Započni besplatno (7 dana)
                            </x-filament::button>
                        @elseif($status['status'] === 'active' && isset($status['billing_cycle']) && str_contains(strtolower($status['billing_cycle']), 'month'))
                            <x-filament::button
                                disabled
                                color="primary"
                                class="w-full">
                                Trenutni plan
                            </x-filament::button>
                        @else
                            <x-filament::button
                                wire:click="subscribeMonthly"
                                outlined
                                class="w-full">
                                Promeni na mesečno
                            </x-filament::button>
                        @endif
                    </div>
                </div>
            </x-filament::section>
            </div>

            {{-- Basic Yearly --}}
            <div class="h-full">
            <x-filament::section class="h-full flex flex-col">
                <x-slot name="heading">
                    Basic - Godišnje
                </x-slot>
                <x-slot name="description">
                    Neograničeno fakturisanje + ušteda
                </x-slot>
                <x-slot name="headerEnd">
                    @if(!($status['status'] === 'active' && isset($status['billing_cycle']) && str_contains(strtolower($status['billing_cycle']), 'year')))
                        <x-filament::badge color="success">
                            Preporučeno
                        </x-filament::badge>
                    @endif
                </x-slot>

                <div class="space-y-6 flex-1 flex flex-col">
                    <div>
                        <div class="text-3xl font-bold">{{ number_format($plans['basic_yearly']['price']) }} RSD</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Po godini</div>
                        <div class="mt-2">
                            <x-filament::badge color="success">
                                Ušteda {{ number_format($plans['basic_yearly']['savings']) }} RSD
                            </x-filament::badge>
                        </div>
                    </div>

                    <ul class="space-y-2 text-sm flex-1">
                        @foreach($plans['basic_yearly']['features'] as $feature)
                            <li class="flex gap-2">
                                <span class="text-green-500">✓</span>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-auto">
                        @if($status['status'] === 'active' && isset($status['billing_cycle']) && str_contains(strtolower($status['billing_cycle']), 'year'))
                            <x-filament::badge color="primary" class="mb-3">
                                Trenutni plan
                            </x-filament::badge>
                        @endif

                        @if($status['status'] === 'free')
                            <x-filament::button
                                wire:click="subscribeYearly"
                                color="primary"
                                class="w-full">
                                Započni besplatno (7 dana)
                            </x-filament::button>
                        @elseif($status['status'] === 'active' && isset($status['billing_cycle']) && str_contains(strtolower($status['billing_cycle']), 'year'))
                            <x-filament::button
                                disabled
                                color="primary"
                                class="w-full">
                                Trenutni plan
                            </x-filament::button>
                        @else
                            <x-filament::button
                                wire:click="subscribeYearly"
                                outlined
                                class="w-full">
                                Promeni na godišnje
                            </x-filament::button>
                        @endif
                    </div>
                </div>
            </x-filament::section>
            </div>
        </div>
    @endif

    {{-- Trust Info --}}
    @if($status['status'] !== 'grandfathered')
        <div class="text-center text-sm text-gray-600 dark:text-gray-400">
            <p>Sigurna naplata preko Stripe • Sve kartice prihvaćene • Otkaži bilo kada</p>
        </div>
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
