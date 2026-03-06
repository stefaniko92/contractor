<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PublicInvoiceController;
use Illuminate\Support\Facades\Route;

Route::post('/public/generate-invoice', [PublicInvoiceController::class, 'generate'])
    ->middleware(['throttle:public_invoice', 'public_invoice_rate_limit'])
    ->name('api.public.generate-invoice');

Route::post('/public/contact', [ContactController::class, 'send'])
    ->middleware(['throttle:60,1'])
    ->name('api.public.contact');
