<?php

namespace App\Listeners\Tenant;

use App\Notifications\Tenant\PasswordResetCompletedNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NotifyStudentPasswordResetCompleted implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PasswordReset $event): void
    {
        if (! Schema::hasTable('students')) {
            return;
        }

        $user = $event->user;
        $student = $user?->student;

        if (! $student || ! $student->email) {
            return;
        }

        $student->notify(new PasswordResetCompletedNotification(
            $student,
            now()->toIso8601String()
        ));
    }

    public function failed(PasswordReset $event, \Throwable $exception): void
    {
        Log::error('Error al enviar notificacion de clave actualizada', [
            'user_id' => $event->user?->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
