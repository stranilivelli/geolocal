<?php
// routes/api.php

use App\Http\Controllers\Api\LocationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Strutture convenzionate
    Route::get('/locations', [LocationController::class, 'index']);
    Route::get('/locations/{slug}', [LocationController::class, 'show']);

    // Dati per i filtri
    Route::get('/categories', [LocationController::class, 'categories']);
    Route::get('/provinces', [LocationController::class, 'provinces']);
});
