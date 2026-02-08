<?php

namespace Tests\Unit\Notifications;

use App\Models\Tenant\Student;
use App\Notifications\WelcomeStudentNotification;
use PHPUnit\Framework\TestCase;

class WelcomeStudentNotificationTest extends TestCase
{
    public function test_it_uses_mail_channel_only(): void
    {
        $student = new Student([
            'first_name' => 'Juan',
            'last_name' => 'Perez',
            'email' => 'juan@example.com',
        ]);

        $notification = new WelcomeStudentNotification(
            $student,
            'https://gym.example.com/reset-password/test-token?email=juan@example.com'
        );

        $this->assertSame(['mail'], $notification->via(new \stdClass()));
    }

    public function test_it_builds_mail_with_registration_url(): void
    {
        $student = new Student([
            'first_name' => 'Juan',
            'last_name' => 'Perez',
            'email' => 'juan@example.com',
        ]);

        $registrationUrl = 'https://gym.example.com/reset-password/test-token?email=juan@example.com';
        $notification = new WelcomeStudentNotification($student, $registrationUrl);

        $mailMessage = $notification->toMail(new \stdClass());

        $this->assertSame('Completa tu registro en FitTrack', $mailMessage->subject);
        $this->assertSame('Hola Juan!', $mailMessage->greeting);
        $this->assertSame('Definir mi clave', $mailMessage->actionText);
        $this->assertSame($registrationUrl, $mailMessage->actionUrl);
    }
}
