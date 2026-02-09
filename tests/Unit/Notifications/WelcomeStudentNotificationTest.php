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
        $notification = new WelcomeStudentNotification($student, $registrationUrl, [
            'tenant_name' => 'Gym Titan',
            'logo_url' => 'https://gym.example.com/logo.png',
            'brand_url' => 'https://gym.example.com',
            'color_base' => '#112233',
            'color_dark' => '#0f172a',
            'color_light' => '#f8fafc',
        ]);

        $mailMessage = $notification->toMail(new \stdClass());

        $this->assertSame('Completa tu registro en Gym Titan', $mailMessage->subject);
        $this->assertSame(['notifications@fittrack.com.ar', 'Gym Titan'], $mailMessage->from);
        $this->assertSame('emails.tenant.student-welcome', $mailMessage->markdown);
        $this->assertSame('Gym Titan', $mailMessage->viewData['tenantName']);
        $this->assertSame('Juan', $mailMessage->viewData['studentFirstName']);
        $this->assertSame($registrationUrl, $mailMessage->viewData['registrationUrl']);
        $this->assertSame('https://gym.example.com/logo.png', $mailMessage->viewData['logoUrl']);
        $this->assertSame('https://gym.example.com', $mailMessage->viewData['brandUrl']);
        $this->assertSame('#112233', $mailMessage->viewData['colorBase']);
        $this->assertSame('#0f172a', $mailMessage->viewData['colorDark']);
        $this->assertSame('#f8fafc', $mailMessage->viewData['colorLight']);
    }
}
