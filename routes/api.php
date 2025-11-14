<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('universal')->group(function () {
    Route::post('/auth/login', [\App\Http\Controllers\Central\AuthController::class, 'login']);
});
