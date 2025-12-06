<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PersonController;
use App\Http\Controllers\Api\InteractionController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::post('/register-device', [AuthController::class, 'registerDevice']);

// Protected routes (JWT authentication required)
Route::middleware('jwt.auth')->group(function () {
    // People endpoints
    Route::get('/people', [PersonController::class, 'index']);
    Route::get('/people/{id}', [PersonController::class, 'show']);

    // Interaction endpoints
    Route::post('/interactions/like', [InteractionController::class, 'like']);
    Route::post('/interactions/dislike', [InteractionController::class, 'dislike']);
    Route::get('/interactions/liked', [InteractionController::class, 'likedPeople']);

    // Admin endpoints
    Route::get('/admin', [AdminController::class, 'show']);
    Route::put('/admin', [AdminController::class, 'update']);
});
