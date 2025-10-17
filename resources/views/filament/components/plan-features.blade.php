<div class="space-y-3">
    @if(isset($features))
        <ul class="space-y-2 text-sm list-none">
            @foreach($features as $feature)
                <li class="text-gray-700 dark:text-gray-300">
                    <span class="inline-flex items-center gap-2">
                        <svg class="text-green-500 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>{{ $feature }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
    @endif

    @if(isset($price))
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $price }}</p>
            @if(isset($yearly_price))
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $yearly_price }}</p>
            @endif
        </div>
    @endif
</div>
