<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-end space-x-2">
            <span class="text-sm text-gray-600">Jezik:</span>
            @foreach($this->getViewData()['availableLocales'] as $locale => $data)
                <button 
                    wire:click="changeLanguage('{{ $locale }}')"
                    class="flex items-center space-x-1 px-3 py-1 rounded-md text-sm transition-colors
                           {{ $this->getViewData()['currentLocale'] === $locale 
                              ? 'bg-amber-100 text-amber-800 font-medium' 
                              : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                >
                    <span>{{ $data['flag'] }}</span>
                    <span>{{ $data['name'] }}</span>
                </button>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>