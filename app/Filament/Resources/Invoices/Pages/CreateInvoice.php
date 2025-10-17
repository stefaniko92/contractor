<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    public function mount(): void
    {
        parent::mount();

        $user = Auth::user();

        // Check if user can create more invoices
        if (! $user->canCreateInvoice()) {
            $limit = $user->getMonthlyInvoiceLimit();
            $current = $user->getMonthlyInvoiceCount();

            Notification::make()
                ->title('Dostigli ste mesečni limit faktura')
                ->body("Trenutno ste kreirali {$current} od {$limit} dozvoljenih faktura za ovaj mesec. Nadogradite na Basic plan za neograničen broj faktura.")
                ->danger()
                ->persistent()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('upgrade')
                        ->label('Nadogradi na Basic')
                        ->url('/admin/subscription-management')
                        ->button(),
                ])
                ->send();

            $this->redirect('/admin/invoices');
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate total amount from invoice items
        $totalAmount = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (isset($item['amount']) && is_numeric($item['amount'])) {
                    $totalAmount += (float) $item['amount'];
                }
            }
        }

        $data['amount'] = $totalAmount;

        return $data;
    }

    protected function beforeCreate(): void
    {
        // Double-check before creating
        $user = Auth::user();

        if (! $user->canCreateInvoice()) {
            Notification::make()
                ->title('Dostigli ste mesečni limit faktura')
                ->body('Ne možete kreirati više faktura ovog meseca.')
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
