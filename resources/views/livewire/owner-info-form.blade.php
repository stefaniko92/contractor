<x-filament-widgets::widget>
    <x-filament::section>
        <form wire:submit.prevent="save">
            {{ $this->form }}
            <x-filament::button type="submit" color="primary">
                Save
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>