<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Pokretni limit od 8M RSD
        </x-slot>
        <x-slot name="description">
            Stanje za bilo koji datum, u stvarnom periodu od prethodnih 365 dana.
        </x-slot>

        @php
            $progress = min(max($analytics['percentage_used'], 0), 100);
            $color = $progress >= 80 ? 'bg-danger-600' : ($progress >= 60 ? 'bg-warning-500' : 'bg-success-600');
        @endphp

        <div class="space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="w-full sm:max-w-xs">
                    <label class="fi-fo-field-wrp-label mb-1 inline-block text-sm font-medium text-gray-950 dark:text-white">
                        Prikaži stanje na datum
                    </label>
                    <input
                        type="date"
                        wire:model.live="projectionDate"
                        class="fi-input block w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white"
                    />
                </div>

                <div class="flex gap-2">
                    <x-filament::button size="sm" color="gray" wire:click="showToday">Danas</x-filament::button>
                    <x-filament::button size="sm" color="gray" wire:click="showNovemberProjection">1. novembar</x-filament::button>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">U periodu od 365 dana</p>
                    <p class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ number_format($analytics['income'], 0, ',', '.') }} RSD</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $analytics['period_start']->format('d.m.Y') }} - {{ $analytics['period_end']->format('d.m.Y') }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Preostaje do 8M limita</p>
                    <p class="mt-1 text-xl font-semibold {{ $analytics['remaining'] < 0 ? 'text-danger-600' : 'text-success-600' }}">{{ number_format($analytics['remaining'], 0, ',', '.') }} RSD</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ number_format($analytics['percentage_used'], 1, ',', '.') }}% iskorišćeno</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Promena u odnosu na danas</p>
                    <p class="mt-1 text-xl font-semibold {{ $analytics['change_from_today'] > 0 ? 'text-warning-600' : 'text-gray-950 dark:text-white' }}">{{ $analytics['change_from_today'] > 0 ? '+' : '' }}{{ number_format($analytics['change_from_today'], 0, ',', '.') }} RSD</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $analytics['falling_off'] > 0 ? number_format($analytics['falling_off'], 0, ',', '.').' RSD izlazi iz perioda' : 'Nema iznosa koji izlazi iz perioda' }}</p>
                </div>
            </div>

            <div>
                <div class="mb-2 flex justify-between text-sm">
                    <span class="font-medium text-gray-950 dark:text-white">Iskorišćenost limita</span>
                    <span class="font-medium text-gray-600 dark:text-gray-300">{{ number_format($progress, 1, ',', '.') }}%</span>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                    <div class="h-full rounded-full transition-all duration-500 {{ $color }}" style="width: {{ $progress }}%"></div>
                </div>
            </div>

            <p class="text-xs text-gray-500 dark:text-gray-400">
                Projekcija koristi trenutno unete domaće fakture koje ulaze u obračun; ne pretpostavlja buduće prihode koji još nisu evidentirani.
            </p>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
