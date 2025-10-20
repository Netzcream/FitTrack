<?php

use App\Http\Middleware\Tenant\EnsureStudentAccessEnabled;
use App\Models\Tenant\Student;
use Illuminate\Support\Facades\Route;

Route::middleware(['tenant.auth', 'role:Alumno', EnsureStudentAccessEnabled::class])
    ->prefix('student')
    ->as('student.')
    ->group(function () {
        Route::get('/', \App\Livewire\Tenant\Student\Dashboard::class)->name('dashboard');
        Route::get('/progress', \App\Livewire\Tenant\Student\Progress::class)->name('progress');
        Route::get('/workout-today', \App\Livewire\Tenant\Student\WorkoutToday::class)->name('workout-today');
        Route::get('/messages', \App\Livewire\Tenant\Student\Messages::class)->name('messages');
        Route::get('/payments', \App\Livewire\Tenant\Student\Payments::class)->name('payments');
        Route::get('/plan/{plan}/download', [\App\Http\Controllers\Tenant\StudentPlanController::class, 'download'])
            ->name('download-plan');
    });
