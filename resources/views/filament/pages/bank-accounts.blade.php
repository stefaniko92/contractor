<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit.prevent="save">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit" color="primary">
                    {{ __('bank_accounts.actions.save') }}
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
