<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación (Sin tenant)
|--------------------------------------------------------------------------
| Login detecta automáticamente el tenant
| Logout requiere autenticación
*/
Route::middleware('universal')->group(function () {
    Route::post('/auth/login', [\App\Http\Controllers\Central\AuthController::class, 'login']);
    Route::post('/auth/logout', [\App\Http\Controllers\Central\AuthController::class, 'logout'])
        ->middleware('auth:sanctum');
});

/*
|--------------------------------------------------------------------------
| Rutas de API Móvil (Con tenant)
|--------------------------------------------------------------------------
| Todas estas rutas requieren:
| - Header: X-Tenant-ID
| - Header: Authorization: Bearer {token}
*/
Route::middleware(['auth:sanctum', \App\Http\Middleware\Api\ApiTenancy::class])->group(function () {

    // Perfil del estudiante
    Route::get('/profile', [\App\Http\Controllers\Api\StudentApiController::class, 'show']);
    Route::patch('/profile', [\App\Http\Controllers\Api\StudentApiController::class, 'update']);

    // Planes de entrenamiento
    Route::get('/plans', [\App\Http\Controllers\Api\TrainingPlanApiController::class, 'index']);
    Route::get('/plans/current', [\App\Http\Controllers\Api\TrainingPlanApiController::class, 'current']);
    Route::get('/plans/{id}', [\App\Http\Controllers\Api\TrainingPlanApiController::class, 'show']);

    // Workouts (sesiones de entrenamiento)
    Route::post('/workouts', [\App\Http\Controllers\Api\WorkoutApiController::class, 'store']);
    Route::get('/workouts', [\App\Http\Controllers\Api\WorkoutApiController::class, 'index']);
    Route::get('/workouts/{id}', [\App\Http\Controllers\Api\WorkoutApiController::class, 'show']);

});
