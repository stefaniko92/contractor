@php
    $statePath = $getStatePath();
    $inputType = $getExtraAttribute('data-input-type') ?? 'text';
    $selectOptions = json_decode($getExtraAttribute('data-select-options'), true) ?? [];
@endphp

<div {{ $attributes->class(['flex items-center gap-2']) }}>
    <input
            type="{{ $inputType }}"
            wire:model="{{ $statePath }}.value"
            class="filament-input w-full"
    />

    <select
            wire:model="{{ $statePath }}.type"
            class="filament-input"
    >
        @foreach($selectOptions as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
