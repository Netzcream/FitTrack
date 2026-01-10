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
        Route::get('/messages', \App\Livewire\Tenant\Student\Messages::class)->name('messages');
        Route::get('/payments', \App\Livewire\Tenant\Student\Payments::class)->name('payments');
        // Updated to use assignment UUID (new model)
        Route::get('/plan/{assignment}/download', [\App\Http\Controllers\Tenant\StudentPlanController::class, 'downloadAssignment'])
            ->name('download-plan');
    });
