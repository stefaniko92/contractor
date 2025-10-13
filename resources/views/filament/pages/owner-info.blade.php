<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit.prevent="save">
            {{ $this->form }}
            <x-filament::button type="submit" color="primary">
                {{ __('owner.actions.save') }}
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>
