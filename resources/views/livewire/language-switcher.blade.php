<div class="fi-dropdown fi-user-menu" x-data="{ open: false }" @language-changed.window="window.location.reload()">
    <button 
        @click="open = !open"
        type="button"
        class="fi-dropdown-trigger flex items-center justify-center rounded-full bg-white outline-none transition duration-75 hover:bg-gray-50 focus:bg-primary-50 focus:ring-2 focus:ring-primary-600 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:bg-primary-500 h-9 w-9"
        x-bind:aria-expanded="open"
        aria-label="Language">
        <span class="text-lg">{{ $availableLocales[$currentLocale]['flag'] }}</span>
    </button>

    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fi-dropdown-panel absolute end-0 top-full z-10 mt-2 w-48 rounded-lg bg-white p-1 shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/20"
         style="display: none;">
        
        @foreach($availableLocales as $locale => $data)
            <button wire:click="changeLanguage('{{ $locale }}')"
                    type="button"
                    class="fi-dropdown-list-item flex w-full items-center justify-start gap-2 rounded-md p-2 text-sm outline-none transition duration-75 hover:bg-gray-50 focus:bg-gray-50 dark:hover:bg-white/5 dark:focus:bg-white/5 {{ $currentLocale === $locale ? 'bg-primary-50 text-primary-600 dark:bg-primary-400/10 dark:text-primary-400' : 'text-gray-700 dark:text-gray-200' }}">
                <span class="fi-dropdown-list-item-icon h-5 w-5 text-lg">{{ $data['flag'] }}</span>
                <span class="fi-dropdown-list-item-label truncate text-start">{{ $data['name'] }}</span>
            </button>
        @endforeach
    </div>
</div>