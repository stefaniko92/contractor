<x-filament-panels::page>
    <div class="space-y-6">
        <!-- AI Disclaimer -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        NAPOMENA
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>Uplatnice su generisane korišćenjem veštačke inteligencije. Preporučujemo da proverite podatke pre plaćanja.</p>
                    </div>
                </div>
            </div>
        </div>
        @forelse($this->getObligations() as $year => $obligations)
            <div class="rounded-lg shadow-sm p-6 fi-section">
                <h2 class="text-2xl font-bold mb-6">Prikaz poreskih naloga za godinu {{ $year }}</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($obligations->groupBy('type') as $type => $typeObligations)
                        <div class="rounded-lg p-6 ring-1 ring-gray-950/5 dark:ring-white/10 bg-white dark:bg-white/5">
                            <h3 class="text-lg font-semibold mb-2">
                                {{ $type === 'pio' ? 'DOPRINOS ZA PIO' : 'POREZ NA PRIHODE OD SAMOSTALNE DELATNOSTI' }}
                            </h3>

                            <div class="mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Iznos</p>
                                <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                                    {{ number_format($typeObligations->first()->amount, 2, ',', '.') }} RSD
                                </p>
                            </div>

                            <div class="space-y-2">
                                <p class="text-sm font-medium mb-2">Mesečne uplate:</p>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach($typeObligations as $obligation)
                                        @php
                                            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Avg', 'Sep', 'Okt', 'Nov', 'Dec'];
                                            $isPaid = $obligation->status === 'paid';
                                            $isOverdue = $obligation->status === 'overdue';
                                            $buttonColor = $isPaid ? 'success' : ($isOverdue ? 'danger' : 'gray');
                                        @endphp
                                        <button
                                            type="button"
                                            @click="$dispatch('open-modal', { id: 'payment-modal-{{ $obligation->id }}' })"
                                            class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium rounded-lg transition
                                                {{ $isPaid ? 'bg-green-600 text-white hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700' : ($isOverdue ? 'bg-red-600 text-white hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700' : 'border-2 border-primary-600 text-primary-600 hover:bg-primary-50 dark:border-primary-400 dark:text-primary-400 dark:hover:bg-primary-400/10') }}"
                                        >
                                            {{ $months[$obligation->month - 1] }}
                                            @if($isPaid)
                                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                        </button>

                                        <x-filament::modal id="payment-modal-{{ $obligation->id }}" width="lg">
                                            <x-slot name="heading">
                                                Detalji uplate - {{ $months[$obligation->month - 1] }} {{ $year }}
                                            </x-slot>

                                            <div class="space-y-4">
                                                <div class="grid grid-cols-2 gap-4 text-sm">
                                                    <div>
                                                        <p class="font-semibold">Tip:</p>
                                                        <p>{{ $obligation->description }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold">Iznos:</p>
                                                        <p>{{ number_format($obligation->amount, 2, ',', '.') }} RSD</p>
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold">Rok plaćanja:</p>
                                                        <p>{{ $obligation->due_date?->format('d.m.Y') }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold">Status:</p>
                                                        <p>
                                                            @if($isPaid)
                                                                <span class="text-green-600 dark:text-green-400">Plaćeno</span>
                                                            @elseif($isOverdue)
                                                                <span class="text-red-600 dark:text-red-400">Prekoračeno</span>
                                                            @else
                                                                <span>Na čekanju</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>

                                                @if($obligation->payment_code || $obligation->payment_recipient_account || $obligation->payment_reference)
                                                    <div class="border-t pt-4 space-y-4">
                                                        <div class="space-y-2">
                                                            <p class="font-semibold mb-3">Podaci za plaćanje:</p>
                                                            @if($obligation->payment_code)
                                                                <div class="flex justify-between text-sm">
                                                                    <span class="font-medium">Šifra plaćanja:</span>
                                                                    <span class="font-medium">{{ $obligation->payment_code }}</span>
                                                                </div>
                                                            @endif
                                                            @if($obligation->payment_recipient_account)
                                                                <div class="flex justify-between text-sm">
                                                                    <span class="font-medium">Račun primaoca:</span>
                                                                    <span class="font-medium">{{ $obligation->payment_recipient_account }}</span>
                                                                </div>
                                                            @endif
                                                            @if($obligation->payment_model)
                                                                <div class="flex justify-between text-sm">
                                                                    <span class="font-medium">Model:</span>
                                                                    <span class="font-medium">{{ $obligation->payment_model }}</span>
                                                                </div>
                                                            @endif
                                                            @if($obligation->payment_reference)
                                                                <div class="flex justify-between text-sm">
                                                                    <span class="font-medium">Poziv na broj:</span>
                                                                    <span class="font-medium">{{ $obligation->payment_reference }}</span>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        @php
                                                            $qrCode = $this->generateQrCode($obligation->id);
                                                        @endphp

                                                        @if($qrCode)
                                                            <div class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                                                <p class="text-sm font-semibold mb-3">Skenirajte QR kod za plaćanje:</p>
                                                                <div class="bg-white p-2 rounded shadow-sm">
                                                                    <img src="{{ $qrCode }}" alt="QR kod za plaćanje" class="w-64 h-64">
                                                                </div>
                                                                <p class="text-xs mt-2 text-center">NBS IPS QR kod</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif

                                                <div class="border-t pt-4 space-y-4" x-data="{ isPaid: {{ $isPaid ? 'true' : 'false' }}, paidAt: '{{ $obligation->paid_at?->format('Y-m-d') ?? now()->format('Y-m-d') }}' }">
                                                    <div>
                                                        <label class="flex items-center space-x-2">
                                                            <input
                                                                type="checkbox"
                                                                x-model="isPaid"
                                                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600"
                                                            >
                                                            <span class="text-sm font-medium">Označi kao plaćeno</span>
                                                        </label>
                                                    </div>

                                                    <div x-show="isPaid" x-cloak>
                                                        <label class="block text-sm font-medium mb-2">
                                                            Datum plaćanja
                                                        </label>
                                                        <input
                                                            type="date"
                                                            x-model="paidAt"
                                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                        >
                                                    </div>

                                                    <div class="flex justify-end space-x-2">
                                                        <x-filament::button
                                                            type="button"
                                                            color="gray"
                                                            @click="$dispatch('close-modal', { id: 'payment-modal-{{ $obligation->id }}' })"
                                                        >
                                                            Otkaži
                                                        </x-filament::button>
                                                        <x-filament::button
                                                            type="button"
                                                            @click="$wire.markObligationAsPaid({{ $obligation->id }}, isPaid, paidAt).then(() => { $dispatch('close-modal', { id: 'payment-modal-{{ $obligation->id }}' }) })"
                                                        >
                                                            Sačuvaj
                                                        </x-filament::button>
                                                    </div>
                                                </div>
                                            </div>
                                        </x-filament::modal>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-12 rounded-lg shadow-sm fi-section">
                <h3 class="text-sm font-medium">Nema poreskih obaveza</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Otpremite poresko rešenje da biste kreirali obaveze.
                </p>
            </div>
        @endforelse

        @if($this->getResolutions()->count() > 0)
            <div class="rounded-lg shadow-sm p-6 fi-section">
                <h3 class="text-lg font-semibold mb-4">Otpremljena rešenja</h3>
                <div class="space-y-2">
                    @foreach($this->getResolutions() as $resolution)
                        <div class="flex items-center justify-between p-3 rounded ring-1 ring-gray-950/5 dark:ring-white/10 bg-white dark:bg-white/5">
                            <div>
                                <p class="font-medium">
                                    {{ $resolution->type === 'pio' ? 'PIO' : 'Porez' }} - {{ $resolution->year }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $resolution->file_name }}</p>
                                @if($resolution->status === 'completed')
                                    <span class="inline-flex items-center px-2 py-1 mt-1 text-xs font-medium text-green-700 bg-green-100 rounded-full dark:bg-green-900/20 dark:text-green-400">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Obrađeno
                                    </span>
                                @elseif($resolution->status === 'processing')
                                    <span class="inline-flex items-center px-2 py-1 mt-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/20 dark:text-blue-400">
                                        U obradi...
                                    </span>
                                @elseif($resolution->status === 'failed')
                                    <span class="inline-flex items-center px-2 py-1 mt-1 text-xs font-medium text-red-700 bg-red-100 rounded-full dark:bg-red-900/20 dark:text-red-400">
                                        Greška
                                    </span>
                                @endif
                            </div>
                            <div>
                                <x-filament::button
                                    size="sm"
                                    color="gray"
                                    icon="heroicon-o-arrow-down-tray"
                                    wire:click="downloadResolution({{ $resolution->id }})"
                                >
                                    Preuzmi
                                </x-filament::button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
