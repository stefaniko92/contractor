<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php
        $isDisabled = false; // Add proper method later
        $hasError = $errors->has($getStatePath());
    @endphp

    <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-2
        {{$hasError ? 'ring-danger-600 dark:ring-danger-600' : 'ring-gray-950/10 dark:ring-white/20 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500'}}"
        x-data="{}" 
        {{$attributes->merge($getExtraAttributes(), escape: false)}}>
        
        <input
            {!! \Filament\Support\prepare_inherited_attributes($getExtraInputAttributeBag()) !!}
            autocapitalize="{{ $getAutocapitalize() }}"
            autocomplete="{{ $getAutocomplete() }}"
            @disabled($isDisabled)
            id="{{ $getId() }}"
            inputmode="{{ $getInputMode() }}"
            placeholder="{{ $getPlaceholder() }}"
            type="{{ $getType() }}"
            wire:model="{{ $getStatePath() }}"
            style="-webkit-appearance: none; -moz-appearance: textfield;"
            class="fi-input block flex-1 border-0 py-1.5 pl-3 pr-0 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 bg-transparent"
        />

        @if ($getSelectComponent)
            <select
                wire:model="{{ $getSelectComponent ? $getSelectComponent->getStatePath() : '' }}"
                class="border-0 bg-transparent py-1.5 text-gray-900 dark:text-gray-100 sm:text-sm cursor-pointer text-center w-8"
                style="background-image: none; outline: none !important; box-shadow: none !important;"
                onfocus="this.style.outline='none'; this.style.boxShadow='none';"
                onblur="this.style.outline='none'; this.style.boxShadow='none';"
            >
                @if($getSelectComponent && method_exists($getSelectComponent, 'getOptions'))
                    @foreach($getSelectComponent->getOptions() as $value => $label)
                        <option value="{{ $value }}" @if(old($getSelectComponent->getName(), $getSelectComponent->getState()) == $value) selected @endif>
                            {{ $label }}
                        </option>
                    @endforeach
                @endif
            </select>
        @endif
    </div>
</x-dynamic-component>

<style>
/* Remove unwanted focus styles from select in combined input */
.fi-input-wrp select:focus {
    outline: none !important;
    box-shadow: none !important;
    border-color: inherit !important;
}

.fi-input-wrp select:focus-visible {
    outline: none !important;
    box-shadow: none !important;
}

/* Hide number input spinners completely */
.fi-input-wrp input[type="number"]::-webkit-outer-spin-button,
.fi-input-wrp input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none !important;
    margin: 0 !important;
}

.fi-input-wrp input[type="number"] {
    -moz-appearance: textfield !important;
}
</style>
