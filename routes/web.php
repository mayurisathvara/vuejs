<?php

use Illuminate\Support\Facades\Route;

// Serve Vue.js SPA for all routes
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
