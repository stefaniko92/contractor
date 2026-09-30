<x-filament-panels::page>
    @php
        $analytics = $this->getAnalytics();
        $limitColor = fn (float $percentage): string => $percentage >= 80 ? 'bg-danger-600' : ($percentage >= 60 ? 'bg-warning-500' : 'bg-success-600');
    @endphp

    <div class="space-y-6">
        <div class="rounded-lg bg-primary-700 px-6 py-6 text-white shadow-sm sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-100">Poslovni pregled</p>
                    <h2 class="mt-1 text-2xl font-semibold">Analitika fakturisanja</h2>
                    <p class="mt-2 text-sm text-primary-100">{{ $analytics['period']['label'] }} · {{ $analytics['period']['start']->format('d.m.Y') }} - {{ $analytics['period']['end']->format('d.m.Y') }}</p>
                </div>

                <label class="block w-full lg:w-64">
                    <span class="mb-1 block text-sm font-medium text-primary-100">Period</span>
                    <select wire:model.live="period" class="block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm font-medium text-gray-950 shadow-sm">
                    <option value="30_days">Poslednjih 30 dana</option>
                    <option value="90_days">Poslednjih 90 dana</option>
                    <option value="year">Tekuća godina</option>
                    <option value="365_days">Poslednjih 365 dana</option>
                    <option value="custom">Prilagođeni period</option>
                    </select>
                </label>
            </div>
            @if ($period === 'custom')
                <form wire:submit="applyDateRange" class="mt-5 flex flex-wrap items-end gap-3">
                    <label class="block">
                        <span class="mb-1 block text-sm text-white">Od</span>
                        <input type="date" wire:model="dateFrom" required class="rounded-lg border-0 bg-white px-3 py-2 text-sm text-gray-950" />
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-sm text-white">Do</span>
                        <input type="date" wire:model="dateTo" required class="rounded-lg border-0 bg-white px-3 py-2 text-sm text-gray-950" />
                    </label>
                    <x-filament::button type="submit" color="gray" icon="heroicon-m-funnel" wire:loading.attr="disabled">Primeni</x-filament::button>
                    @error('dateFrom') <p class="w-full text-sm text-white" role="alert">{{ $message }}</p> @enderror
                    @error('dateTo') <p class="w-full text-sm text-white" role="alert">{{ $message }}</p> @enderror
                </form>
            @endif
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-gray-200 border-t-4 border-t-primary-600 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Fakturisano</p>
                <p class="mt-2 text-lg font-semibold text-gray-950 dark:text-white">{{ $analytics['invoiced'] }}</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $analytics['invoice_count'] }} faktura u periodu</p>
            </div>
            <div class="rounded-lg border border-gray-200 border-t-4 border-t-success-600 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Naplaćeno</p>
                <p class="mt-2 text-lg font-semibold text-success-600">{{ $analytics['paid'] }}</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Fakture označene kao naplaćene</p>
            </div>
            <div class="rounded-lg border border-gray-200 border-t-4 border-t-warning-500 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Čeka naplatu</p>
                <p class="mt-2 text-lg font-semibold text-warning-600">{{ $analytics['outstanding'] }}</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Izdate i delimično naplaćene fakture</p>
            </div>
            <div class="rounded-lg border border-gray-200 border-t-4 border-t-danger-600 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Preko roka</p>
                <p class="mt-2 text-lg font-semibold {{ $analytics['overdue_count'] > 0 ? 'text-danger-600' : 'text-gray-950 dark:text-white' }}">{{ $analytics['overdue'] }}</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $analytics['overdue_count'] }} faktura sa isteklim rokom</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2">
                @livewire(\App\Filament\Widgets\AnalyticsRevenueChart::class, ['period' => $period, 'dateFrom' => $appliedDateFrom, 'dateTo' => $appliedDateTo], key('analytics-revenue-'.$period.'-'.$appliedDateFrom.'-'.$appliedDateTo))
            </div>
            <div>
                @livewire(\App\Filament\Widgets\AnalyticsInvoiceStatusChart::class, ['period' => $period, 'dateFrom' => $appliedDateFrom, 'dateTo' => $appliedDateTo], key('analytics-status-'.$period.'-'.$appliedDateFrom.'-'.$appliedDateTo))
            </div>
        </div>

        <div>
            <x-filament::section heading="Najveći klijenti" description="Rangirani po RSD prometu; ostale valute ostaju odvojene.">
                @if ($analytics['clients']->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nema faktura za izabrani period.</p>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-white/10">
                        @foreach ($analytics['clients'] as $index => $client)
                            <div class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">{{ $index + 1 }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-gray-950 dark:text-white">{{ $client['name'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $client['invoices'] }} faktura</p>
                                    </div>
                                </div>
                                <p class="max-w-[45%] break-words text-right text-sm font-medium text-gray-950 dark:text-white">{{ $client['amount'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <x-filament::section heading="Valute" description="Valute se nikada ne sabiraju međusobno.">
                @if ($analytics['currencies']->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nema faktura za izabrani period.</p>
                @else
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($analytics['currencies'] as $currency)
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-950 dark:text-white">{{ $currency['currency'] }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $currency['invoices'] }} faktura</span>
                                </div>
                                <p class="mt-2 text-lg font-semibold text-gray-950 dark:text-white">{{ number_format($currency['amount'], 0, ',', '.') }} {{ $currency['currency'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>

            <x-filament::section heading="Pausalni limiti" description="Domaći RSD promet koji ulazi u obračun.">
                <div class="space-y-5">
                    @foreach (['pausal_6m' => '6M u tekućoj godini', 'pausal_8m' => '8M u pokretnih 365 dana'] as $key => $label)
                        @php
                            $limit = $key === 'pausal_6m' ? 6_000_000 : 8_000_000;
                            $data = $analytics[$key];
                            $progress = min(max($data['percentage'], 0), 100);
                        @endphp
                        <div>
                            <div class="flex justify-between gap-4 text-sm">
                                <span class="font-medium text-gray-950 dark:text-white">{{ $label }}</span>
                                <span class="text-gray-600 dark:text-gray-300">{{ number_format($progress, 1, ',', '.') }}%</span>
                            </div>
                            <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                <div class="h-full rounded-full {{ $limitColor($progress) }}" style="width: {{ $progress }}%"></div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ number_format($data['income'], 0, ',', '.') }} od {{ number_format($limit, 0, ',', '.') }} RSD · preostaje {{ number_format($data['remaining'], 0, ',', '.') }} RSD</p>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
