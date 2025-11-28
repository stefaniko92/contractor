<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Godišnji prihod (6M limit)
        </x-slot>

        @php
            $userId = Auth::id();
            $currentYear = now()->year;

            $annualIncome = \App\Models\Invoice::where('invoices.user_id', $userId)
                ->join('clients', 'invoices.client_id', '=', 'clients.id')
                ->where('clients.is_domestic', true)
                ->whereYear('invoices.issue_date', $currentYear)
                ->whereNotNull('invoices.issue_date')
                ->sum('invoices.amount');

            $pausalaciLimit = 6000000;
            $remaining = $pausalaciLimit - $annualIncome;
            $percentageUsed = ($annualIncome / $pausalaciLimit) * 100;
        @endphp

        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="font-medium">Ostvareno: {{ number_format($annualIncome, 0, ',', '.') }} RSD</span>
                    <span class="font-medium text-blue-600">{{ number_format($percentageUsed, 1) }}%</span>
                </div>
                <div class="relative w-full rounded-full" style="background-color: #f8fafc; height: 32px;">
                    <div class="absolute top-0 left-0 rounded-full transition-all duration-500 flex items-center justify-end pr-3"
                         style="width: {{ min($percentageUsed, 100) }}%; background-color: rgb(59, 130, 246); height: 32px;">
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
