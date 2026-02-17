<?php

use App\Http\Middleware\Tenant\EnsureStudentAccessEnabled;
use App\Models\Tenant\Student;
use Illuminate\Support\Facades\Route;

Route::middleware(['tenant.auth', 'role:Alumno', EnsureStudentAccessEnabled::class])
    ->prefix('student')
    ->as('student.')
    ->group(function () {
        Route::get('/', \App\Livewire\Tenant\Student\Dashboard::class)->name('dashboard');
        Route::get('/profile', \App\Livewire\Tenant\Student\Profile::class)->name('profile');
        Route::get('/workout-today', \App\Livewire\Tenant\Student\WorkoutToday::class)->name('workout-today');
        Route::get('/workout/{workout}', \App\Livewire\Tenant\Student\WorkoutToday::class)->name('workout-show');
        Route::get('/progress', \App\Livewire\Tenant\Student\Progress::class)->name('progress');
        Route::get('/messages', \App\Livewire\Tenant\Student\Messages::class)->name('messages');
        // Ruta para procesar retorno de Mercado Pago (debe estar ANTES de la ruta Livewire)
        Route::get('/payments-callback', [\App\Http\Controllers\Tenant\PaymentController::class, 'handleMercadopagoReturn'])->name('payments-callback');
        // Ruta Livewire para la página de pagos
        Route::get('/payments', \App\Livewire\Tenant\Student\Payments::class)->name('payments');
        Route::post('/payments/verify-mercadopago', [\App\Http\Controllers\Tenant\PaymentController::class, 'verifyMercadoPago'])->name('verify-mercadopago');
        Route::get('/invoices', \App\Livewire\Tenant\Student\InvoicesHistory::class)->name('invoices');
        // Plan detail view for current/previous assignments
        Route::get('/plan/{assignment}', \App\Livewire\Tenant\Student\PlanDetail::class)
            ->name('plan-detail');
        // Updated to use assignment UUID (new model)
        Route::get('/plan/{assignment}/download', [\App\Http\Controllers\Tenant\StudentPlanController::class, 'downloadAssignment'])
            ->name('download-plan');
    });
