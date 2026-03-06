<?php

use Illuminate\Support\Facades\Route;

// SPA - Catch all routes and return the main view
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
