<?php

use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\UserAchievementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Loyalty program endpoints
Route::get('/users/{user}/achievements', [UserAchievementController::class, 'show']);
Route::post('/users/{user}/purchases', [PurchaseController::class, 'store']);
