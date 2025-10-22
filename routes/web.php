<?php

use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\KpoController;
use App\Http\Controllers\Webhooks\SefEfakturaWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// KPO Book routes - requires authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/kpo/download/{year}', [KpoController::class, 'download'])->name('kpo.download');

    // Invoice PDF routes
    Route::get('/invoices/{invoice}/preview', [InvoicePdfController::class, 'preview'])->name('invoices.preview');
    Route::get('/invoices/{invoice}/download', [InvoicePdfController::class, 'download'])->name('invoices.download');
    Route::get('/invoices/{invoice}/print', [InvoicePdfController::class, 'print'])->name('invoices.print');
});

// SEF/EFaktura Webhook routes - no auth required but token-based verification
Route::post('/webhooks/sef-efaktura/{user_id}', [SefEfakturaWebhookController::class, 'handle'])->name('webhooks.sef-efaktura');
