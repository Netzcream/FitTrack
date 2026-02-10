<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Central API Routes
|--------------------------------------------------------------------------
| IMPORTANTE: Esta es la API CENTRAL, NO de tenants
|
| Se sirve automáticamente desde:
|   - /api (por configuración de Laravel en bootstrap/app.php)
|   - api.<central-domain> (se registra adicional en bootstrap/app.php)
|
| NO requiere X-Tenant-ID
| NO requiere tenancy
| Esto es para administración y funciones globales
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| API Documentation
|--------------------------------------------------------------------------
*/
Route::get('/docs', [\App\Http\Controllers\Api\ApiDocsController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Autenticación Central
|--------------------------------------------------------------------------
*/
Route::post('/auth/login', [\App\Http\Controllers\Central\AuthController::class, 'login']);
Route::post('/auth/logout', [\App\Http\Controllers\Central\AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Rutas Centrales Autenticadas
|--------------------------------------------------------------------------
| Aceptan X-Tenant-ID para inicializar el contexto del tenant
*/
Route::middleware([\App\Http\Middleware\Api\ApiTenancy::class])->group(function () {

    // Home/Dashboard del estudiante
    Route::get('/home', [\App\Http\Controllers\Api\ProgressApiController::class, 'home']);

    // Perfil del estudiante
    Route::get('/profile', [\App\Http\Controllers\Api\StudentApiController::class, 'show']);
    Route::patch('/profile', [\App\Http\Controllers\Api\StudentApiController::class, 'update']);
    Route::patch('/profile/preferences', [\App\Http\Controllers\Api\StudentApiController::class, 'updatePreferences']);

    // Planes de entrenamiento
    Route::get('/plans', [\App\Http\Controllers\Api\TrainingPlanApiController::class, 'index']);
    Route::get('/plans/current', [\App\Http\Controllers\Api\TrainingPlanApiController::class, 'current']);
    Route::get('/plans/{id}', [\App\Http\Controllers\Api\TrainingPlanApiController::class, 'show']);

    // Workouts
    Route::prefix('workouts')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\WorkoutApiController::class, 'index']);
        Route::get('/today', [\App\Http\Controllers\Api\WorkoutApiController::class, 'today']);
        Route::get('/stats', [\App\Http\Controllers\Api\WorkoutApiController::class, 'stats']);
        Route::get('/{id}', [\App\Http\Controllers\Api\WorkoutApiController::class, 'show']);
        Route::post('/{id}/start', [\App\Http\Controllers\Api\WorkoutApiController::class, 'start']);
        Route::patch('/{id}', [\App\Http\Controllers\Api\WorkoutApiController::class, 'update']);
        Route::post('/{id}/complete', [\App\Http\Controllers\Api\WorkoutApiController::class, 'complete']);
        Route::post('/{id}/skip', [\App\Http\Controllers\Api\WorkoutApiController::class, 'skip']);
    });

    // Peso del estudiante
    Route::prefix('weight')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\StudentWeightApiController::class, 'index']);
        Route::get('/latest', [\App\Http\Controllers\Api\StudentWeightApiController::class, 'latest']);
        Route::get('/change', [\App\Http\Controllers\Api\StudentWeightApiController::class, 'change']);
        Route::get('/average', [\App\Http\Controllers\Api\StudentWeightApiController::class, 'average']);
        Route::post('/', [\App\Http\Controllers\Api\StudentWeightApiController::class, 'store']);
    });

    // Progreso
    Route::prefix('progress')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\ProgressApiController::class, 'dashboard']);
        Route::get('/', [\App\Http\Controllers\Api\ProgressApiController::class, 'index']);
        Route::get('/recent', [\App\Http\Controllers\Api\ProgressApiController::class, 'recent']);
    });

    // Pagos
    Route::get('/payments', [\App\Http\Controllers\Api\ProgressApiController::class, 'payments']);

    // Messaging (Student <-> Trainer)
    Route::prefix('messages')->group(function () {
        Route::get('/conversation', [\App\Http\Controllers\Api\MessagingController::class, 'show']);
        Route::post('/send', [\App\Http\Controllers\Api\MessagingController::class, 'sendMessage']);
        Route::post('/read', [\App\Http\Controllers\Api\MessagingController::class, 'markAsRead']);
        Route::post('/mute', [\App\Http\Controllers\Api\MessagingController::class, 'toggleMute']);
        Route::get('/unread-count', [\App\Http\Controllers\Api\MessagingController::class, 'unreadCount']);
    });

});
