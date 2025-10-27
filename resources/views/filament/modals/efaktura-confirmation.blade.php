<div class="space-y-8">
    <!-- Warning Section -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
        <div class="flex items-start">
            <svg class="text-amber-500 mr-3 mt-0.5" viewBox="0 0 20 20" fill="currentColor" style="width: 12px; height: 12px;">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-amber-900 mb-3">Pažnja!</h3>
                <div class="space-y-3 text-amber-800">
                    <div class="flex items-start">
                        <svg class="text-amber-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" style="width: 10px; height: 10px;">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-base leading-relaxed">
                            Kada se faktura šalje na sistem eFaktura, na sistemu će joj biti automatski dodeljen današnji datum izdavanja.
                        </p>
                    </div>
                    <div class="flex items-start">
                        <svg class="text-amber-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" style="width: 10px; height: 10px;">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-base leading-relaxed">
                            Faktura koja sadrži povezanu avansnu fakturu, neće moći da bude poslata osim ako avansna faktura nije prvo poslata na sistem eFaktura.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Information Section -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="flex items-center mb-4">
            <svg class="text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h4 class="text-lg font-semibold text-gray-900">Informacije o fakturi</h4>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-600">Broj fakture:</span>
                    <span class="font-bold text-gray-900 text-base">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-600">Iznos:</span>
                    <span class="font-bold text-green-600 text-base">{{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}</span>
                </div>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-600">Klijent:</span>
                    <span class="font-bold text-gray-900 text-base text-right max-w-xs">{{ $invoice->client->company_name ?? 'Nepoznat' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-600">Trenutni datum dospeća:</span>
                    <span class="font-bold text-blue-600 text-base">{{ $invoice->due_date?->format('d.m.Y') ?? 'Nije postavljen' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Instructions -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start">
            <svg class="text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" style="width: 12px; height: 12px;">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div class="ml-2">
                <p class="text-sm text-blue-800">
                    <span class="font-semibold">Važno:</span> Odaberite novi datum dospeća fakture pomoću kalendara u nastavku.
                </p>
            </div>
        </div>
    </div>
</div>