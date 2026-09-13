<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit.prevent="issueInvoice">
            {{ $this->form }}

            <p class="mt-6 text-sm text-gray-600 dark:text-gray-400">
                {{ __('create_invoice.actions.draft_notice') }}
            </p>

            <div class="flex justify-end space-x-2" style="margin-top: 2.5rem;">
                @foreach($this->getFormActions() as $action)
                    {{ $action }}
                @endforeach
            </div>
        </form>
    </x-filament::section>
    
    <x-filament-actions::modals />
</x-filament-panels::page>
