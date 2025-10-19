<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit.prevent="create">
            {{ $this->form }}
            
            <div class="flex justify-end space-x-2" style="margin-top: 2.5rem;">
                @foreach($this->getFormActions() as $action)
                    {{ $action }}
                @endforeach
            </div>
        </form>
    </x-filament::section>
    
    <x-filament-actions::modals />
</x-filament-panels::page>