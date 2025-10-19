<div style="display: flex; flex-direction: column; height: 100%; min-height: 400px;">
    <div class="space-y-3" style="flex-grow: 1;">
        @if(isset($price))
            <div>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $price }}</p>
                @if(isset($yearly_price))
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $yearly_price }}</p>
                @endif
                @if(isset($savingsBadge))
                    <div class="mt-2">
                        <x-filament::badge color="success">
                            {{ $savingsBadge }}
                        </x-filament::badge>
                    </div>
                @endif
            </div>
        @endif

        @if(isset($features))
            <ul class="space-y-2 text-sm list-none">
                @foreach($features as $feature)
                    <li class="text-gray-700 dark:text-gray-300" style="display: flex; align-items: flex-start; gap: 0.5rem;">
                        <svg class="text-green-500 dark:text-green-400" style="flex-shrink: 0; margin-top: 0.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span style="flex: 1;">{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if(isset($buttonLabel))
        <div style="margin-top: auto; padding-top: 1rem;">
            @if(isset($showBadge) && $showBadge && isset($badgeLabel))
                <x-filament::badge color="primary" class="mb-3">
                    {{ $badgeLabel }}
                </x-filament::badge>
            @endif

            @if(isset($wireClick) && $wireClick)
                <x-filament::button
                    :color="$buttonColor ?? 'primary'"
                    class="w-full"
                    :outlined="$outlined ?? false"
                    :disabled="$disabled ?? false"
                    wire:click="{{ $wireClick }}"
                >
                    {{ $buttonLabel }}
                </x-filament::button>
            @else
                <x-filament::button
                    :color="$buttonColor ?? 'primary'"
                    class="w-full"
                    :outlined="$outlined ?? false"
                    :disabled="$disabled ?? false"
                >
                    {{ $buttonLabel }}
                </x-filament::button>
            @endif
        </div>
    @endif
</div>
