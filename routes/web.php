<?php

use Illuminate\Support\Facades\Route;

// Email verification web route — must come before the SPA catch-all so Laravel
// handles the signed link click rather than serving the Vue shell.
Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\AuthController::class, 'verifyEmail'])
    ->middleware('throttle:6,1')
    ->name('verification.verify');

// Serve Vue.js SPA for all other routes
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
