<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanCreateInvoice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Check if user can create more invoices this month
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
                        ->url('/admin/subscription')
                        ->button(),
                ])
                ->send();

            return redirect('/admin/invoices');
        }

        return $next($request);
    }
}
