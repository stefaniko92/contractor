<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Fakture</h3>
                <p class="text-gray-600 mb-4">Upravljajte vašim fakturama - kreirajte nove, editujte postojeće i pratite plaćanja.</p>
                <a href="{{ route('filament.admin.resources.invoices.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700">
                    Prikaži sve fakture
                </a>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Klijenti</h3>
                <p class="text-gray-600 mb-4">Upravljajte bazom vaših klijenata - dodajte nove klijente ili editujte postojeće.</p>
                <a href="{{ route('filament.admin.resources.clients.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700">
                    Prikaži sve klijente
                </a>
            </div>
        </div>
    </div>
</x-filament-panels::page>