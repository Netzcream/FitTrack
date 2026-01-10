<?php

use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [\App\Http\Controllers\Central\AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Central Messaging Routes
|--------------------------------------------------------------------------
| Routes for messaging between Central and Tenants
*/
Route::middleware(['auth:sanctum'])->prefix('messages')->group(function () {
    Route::get('/conversations', [\App\Http\Controllers\Central\MessagingController::class, 'index']);
    Route::post('/conversations', [\App\Http\Controllers\Central\MessagingController::class, 'store']);
    Route::get('/conversations/{id}', [\App\Http\Controllers\Central\MessagingController::class, 'show']);
    Route::post('/conversations/{id}/messages', [\App\Http\Controllers\Central\MessagingController::class, 'sendMessage']);
    Route::post('/conversations/{id}/read', [\App\Http\Controllers\Central\MessagingController::class, 'markAsRead']);
    Route::post('/conversations/{id}/mute', [\App\Http\Controllers\Central\MessagingController::class, 'toggleMute']);
    Route::delete('/conversations/{id}', [\App\Http\Controllers\Central\MessagingController::class, 'destroy']);
    Route::get('/unread-count', [\App\Http\Controllers\Central\MessagingController::class, 'unreadCount']);
});
