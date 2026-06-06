<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\LegalPageController;
use Illuminate\Support\Facades\Route;

Route::post('/contact', [ContactController::class, 'store']);
Route::get('/legal/{type}', [LegalPageController::class, 'show']);
