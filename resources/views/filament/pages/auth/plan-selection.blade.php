@php
    $plans = config('subscriptions.plans');
    $selectedPlan = $this->selectedPlan ?? 'free';
@endphp

<div class="fi-sc fi-sc-has-gap fi-grid" style="display: grid; gap: 1.5rem; grid-template-columns: repeat(1, minmax(0, 1fr)); --cols-lg: repeat(3, minmax(0, 1fr));">
    <style>
        @media (min-width: 1024px) {
            .fi-grid[style*="--cols-lg"] {
                grid-template-columns: var(--cols-lg) !important;
            }
        }
        .plan-card-selectable {
            cursor: pointer;
            transition: all 0.2s;
        }
        .plan-card-selectable:hover {
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        .plan-card-selected {
            border: 2px solid rgb(245 158 11) !important;
            box-shadow: 0 0 0 3px rgb(245 158 11 / 0.1);
        }
    </style>

    {{-- Free Plan --}}
    <div class="plan-card-selectable {{ $selectedPlan === 'free' ? 'plan-card-selected' : '' }}"
         wire:click="$set('selectedPlan', 'free')">
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
            @if($selectedPlan === 'free')
                <x-slot name="headerEnd">
                    <x-filament::badge color="success">
                        Izabrano
                    </x-filament::badge>
                </x-slot>
            @endif

            {!! view('filament.components.plan-features', [
                'features' => $plans['free']['features'],
                'price' => '0 RSD',
                'yearly_price' => 'Zauvek besplatno',
                'buttonLabel' => $selectedPlan === 'free' ? 'Izabrano' : 'Izaberi',
                'buttonColor' => $selectedPlan === 'free' ? 'success' : 'gray',
                'outlined' => $selectedPlan !== 'free',
                'disabled' => false,
            ]) !!}
        </x-filament::section>
    </div>

    {{-- Basic Monthly --}}
    <div class="plan-card-selectable {{ $selectedPlan === 'basic_monthly' ? 'plan-card-selected' : '' }}"
         wire:click="$set('selectedPlan', 'basic_monthly')">
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
            @if($selectedPlan === 'basic_monthly')
                <x-slot name="headerEnd">
                    <x-filament::badge color="primary">
                        Izabrano
                    </x-filament::badge>
                </x-slot>
            @endif

            {!! view('filament.components.plan-features', [
                'features' => $plans['basic_monthly']['features'],
                'price' => number_format($plans['basic_monthly']['price']) . ' RSD',
                'yearly_price' => 'Po mesecu',
                'buttonLabel' => $selectedPlan === 'basic_monthly' ? 'Izabrano' : 'Izaberi',
                'buttonColor' => $selectedPlan === 'basic_monthly' ? 'primary' : 'gray',
                'outlined' => $selectedPlan !== 'basic_monthly',
                'disabled' => false,
            ]) !!}
        </x-filament::section>
    </div>

    {{-- Basic Yearly --}}
    <div class="plan-card-selectable {{ $selectedPlan === 'basic_yearly' ? 'plan-card-selected' : '' }}"
         wire:click="$set('selectedPlan', 'basic_yearly')">
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
            @if($selectedPlan !== 'basic_yearly')
                <x-slot name="headerEnd">
                    <x-filament::badge color="success">
                        Preporučeno
                    </x-filament::badge>
                </x-slot>
            @else
                <x-slot name="headerEnd">
                    <x-filament::badge color="warning">
                        Izabrano
                    </x-filament::badge>
                </x-slot>
            @endif

            @php
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
                'buttonLabel' => $selectedPlan === 'basic_yearly' ? 'Izabrano' : 'Izaberi',
                'buttonColor' => $selectedPlan === 'basic_yearly' ? 'warning' : 'gray',
                'outlined' => $selectedPlan !== 'basic_yearly',
                'disabled' => false,
            ]) !!}
        </x-filament::section>
    </div>
</div>

{{-- Trust Info --}}
<div class="text-center text-sm text-gray-600 dark:text-gray-400 mt-6">
    <p>🔒 Sigurna naplata preko Stripe • Otkaži bilo kada</p>
</div>
