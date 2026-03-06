<?php

use App\Mail\PublicInvoiceGenerated;
use App\Mail\WelcomeNewUser;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Email Preview Routes (Development Only)
|--------------------------------------------------------------------------
|
| These routes are for previewing email templates in the browser.
| IMPORTANT: Only enable in local development environment!
|
*/

if (app()->environment('local')) {
    Route::prefix('email-preview')->group(function () {
        // Preview: Public Invoice Generated Email
        Route::get('/public-invoice', function () {
            $invoice = Invoice::with(['client', 'items', 'user'])->first();

            if (! $invoice) {
                return 'Nema faktura u bazi. Kreiraj bar jednu fakturu prvo.';
            }

            return new PublicInvoiceGenerated(
                $invoice,
                storage_path('app/public/test.pdf') // Dummy path
            );
        })->name('preview.email.invoice');

        // Preview: Welcome New User Email
        Route::get('/welcome-user', function () {
            $user = User::first();

            if (! $user) {
                return 'Nema korisnika u bazi. Kreiraj bar jednog korisnika prvo.';
            }

            return new WelcomeNewUser(
                $user,
                'test-reset-token-12345'
            );
        })->name('preview.email.welcome');

        // Preview: Contact Form Message Email
        Route::get('/contact-form', function () {
            return new \App\Mail\ContactFormMessage(
                'Stefan Rakić',
                'test@example.com',
                'Test Poruka sa Kontakt Forme',
                "Ovo je test poruka.\n\nPoslata sa kontakt forme na sajtu.\n\nSrdačan pozdrav,\nStefan"
            );
        })->name('preview.email.contact');
    });
}
