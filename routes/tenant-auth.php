<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Livewire\Auth\ConfirmPassword;
use App\Livewire\Auth\Tenant\Login;
use App\Livewire\Auth\Tenant\ForgotPassword;
use App\Livewire\Auth\Tenant\ResetPassword;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\VerifyEmail;
use Illuminate\Support\Facades\Route;



Route::middleware('universal')->group(function () {




    Route::middleware('tenant.guest')->group(function () {
        Route::get('login', Login::class)->name('login');
        //Route::get('register', Register::class)->name('register');
        Route::get('forgot-password', ForgotPassword::class)->name('password.request');
        Route::get('reset-password/{token}', ResetPassword::class)->name('password.reset');
        Route::get('auth/google/redirect', [GoogleLoginController::class, 'redirect'])
            ->name('google.redirect');
    });

    Route::middleware('auth')->group(function () {
        Route::get('verify-email', VerifyEmail::class)
            ->name('verification.notice');

        Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::get('confirm-password', ConfirmPassword::class)
            ->name('password.confirm');
    });

    Route::post('logout', App\Livewire\Actions\Logout::class)
        ->name('logout');
});
