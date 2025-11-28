<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Prihod u 12 meseci (8M limit)
        </x-slot>

        @php
            $userId = Auth::id();
            $twelveMonthsAgo = now()->subMonths(12);

            $twelveMonthIncome = \App\Models\Invoice::where('invoices.user_id', $userId)
                ->join('clients', 'invoices.client_id', '=', 'clients.id')
                ->where('clients.is_domestic', true)
                ->where('invoices.issue_date', '>=', $twelveMonthsAgo)
                ->whereNotNull('invoices.issue_date')
                ->sum('invoices.amount');

            $twelveMonthLimit = 8000000;
            $remaining = $twelveMonthLimit - $twelveMonthIncome;
            $percentageUsed = ($twelveMonthIncome / $twelveMonthLimit) * 100;
        @endphp

        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="font-medium">Ostvareno: {{ number_format($twelveMonthIncome, 0, ',', '.') }} RSD</span>
                    <span class="font-medium">{{ number_format($percentageUsed, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-8 dark:bg-gray-700 overflow-hidden">
                    <div class="h-8 rounded-full transition-all duration-500 flex items-center justify-end pr-3"
                         style="width: {{ min($percentageUsed, 100) }}%; background-color: rgb(59, 130, 246);">
                        @if($percentageUsed > 5)
                            <span class="text-sm font-semibold text-white">{{ number_format($percentageUsed, 1) }}%</span>
                        @endif
                    </div>
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    Preostalo: {{ number_format($remaining, 0, ',', '.') }} RSD
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
