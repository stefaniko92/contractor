<?php

use App\Http\Controllers\KpoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// KPO Book routes - requires authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/kpo/download/{year}', [KpoController::class, 'download'])->name('kpo.download');
});
