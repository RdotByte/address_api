<?php

use App\Http\Controllers\PostcodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/postcode/lookup', [PostcodeController::class, 'lookup']);
    Route::get('/postcode/radius', [PostcodeController::class, 'radius']);
    Route::get('/postcode/distance', [PostcodeController::class, 'distance']);
});
