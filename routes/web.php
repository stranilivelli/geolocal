<?php

use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LocationController::class, 'index'])->name('locations.index');
Route::get('/struttura/{slug}', [LocationController::class, 'show'])->name('locations.show');
