<?php

namespace App\Livewire\Auth\Tenant;

use App\Notifications\Tenant\ResetPasswordNotification;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.tenant.auth')]
class ForgotPassword extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink(
            $this->only('email'),
            function ($user, string $token): void {
                $user->notify(new ResetPasswordNotification($token));
            }
        );

        session()->flash('status', __('Se enviará un enlace de restablecimiento si la cuenta existe.'));
    }
}
