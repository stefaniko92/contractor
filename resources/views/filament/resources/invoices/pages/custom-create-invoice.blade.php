<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}
        
        <div class="mt-8 flex justify-end">
            {{ $this->getFormActions() }}
        </div>
    </form>
    
    <x-filament-actions::modals />
</x-filament-panels::page>
