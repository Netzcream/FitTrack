<?php

namespace Tests\Feature\Api;

use App\Models\Tenant;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentWeightEntry;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StudentProfileApiTest extends TestCase
{
    public function test_profile_returns_latest_weight_and_imc_from_weight_entries(): void
    {
        $context = $this->createProfileContext();
        $headers = $this->apiHeaders($context['token'], $context['tenant']->id);

        tenancy()->initialize($context['tenant']);
        try {
            StudentWeightEntry::create([
                'student_id' => $context['student']->id,
                'weight_kg' => 82.20,
                'source' => 'manual',
                'recorded_at' => now()->subDays(3),
            ]);

            StudentWeightEntry::create([
                'student_id' => $context['student']->id,
                'weight_kg' => 79.40,
                'source' => 'manual',
                'recorded_at' => now()->subDay(),
            ]);
        } finally {
            tenancy()->end();
        }

        $response = $this
            ->withHeaders($headers)
            ->getJson('/api/profile');

        $response->assertOk();
        $response->assertJsonPath('data.weight_kg', 79.4);
        $response->assertJsonPath('data.imc', 26.5);
    }

    public function test_profile_preferences_endpoint_updates_contact_and_notifications(): void
    {
        $context = $this->createProfileContext();
        $headers = $this->apiHeaders($context['token'], $context['tenant']->id);

        $response = $this
            ->withHeaders($headers)
            ->patchJson('/api/profile/preferences', [
                'contact' => [
                    'phone' => '1144556677',
                    'timezone' => 'America/Argentina/Cordoba',
                    'language' => 'en',
                ],
                'notifications' => [
                    'new_plan' => false,
                    'session_reminder' => true,
                    'weekly_summary' => true,
                ],
            ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Preferencias de contacto actualizadas correctamente');
        $response->assertJsonPath('data.phone', '1144556677');
        $response->assertJsonPath('data.timezone', 'America/Argentina/Cordoba');
        $response->assertJsonPath('data.language', 'en');
        $response->assertJsonPath('data.notifications.new_plan', false);
        $response->assertJsonPath('data.notifications.session_reminder', true);
        $response->assertJsonPath('data.notifications.weekly_summary', true);

        tenancy()->initialize($context['tenant']);
        try {
            $student = Student::findOrFail($context['student']->id);

            $this->assertSame('1144556677', $student->phone);
            $this->assertSame('America/Argentina/Cordoba', data_get($student->data, 'timezone'));
            $this->assertSame('en', data_get($student->data, 'communication_data.language'));
            $this->assertFalse((bool) data_get($student->data, 'notifications.new_plan'));
            $this->assertTrue((bool) data_get($student->data, 'notifications.session_reminder'));
            $this->assertTrue((bool) data_get($student->data, 'notifications.weekly_summary'));
        } finally {
            tenancy()->end();
        }
    }

    public function test_profile_patch_weight_creates_weight_entry_and_updates_imc(): void
    {
        $context = $this->createProfileContext();
        $headers = $this->apiHeaders($context['token'], $context['tenant']->id);

        $response = $this
            ->withHeaders($headers)
            ->patchJson('/api/profile', [
                'weight_kg' => 82.0,
            ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Perfil actualizado correctamente');
        $this->assertSame(82.0, (float) $response->json('data.weight_kg'));
        $this->assertSame(27.4, (float) $response->json('data.imc'));

        tenancy()->initialize($context['tenant']);
        try {
            $this->assertDatabaseHas('student_weight_entries', [
                'student_id' => $context['student']->id,
                'source' => 'api',
            ]);
        } finally {
            tenancy()->end();
        }
    }

    /**
     * @return array{
     *   tenant: Tenant,
     *   token: string,
     *   user: User,
     *   student: Student
     * }
     */
    private function createProfileContext(): array
    {
        Notification::fake();

        $tenant = $this->actingAsTenant();

        $user = User::factory()->create([
            'email' => 'student.profile@example.com',
            'name' => 'Student Profile',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'status' => 'active',
            'email' => $user->email,
            'first_name' => 'Luciano',
            'last_name' => 'Pujol',
            'phone' => '1152615412',
            'goal' => 'Bajar de peso',
            'is_user_enabled' => true,
            'billing_frequency' => 'monthly',
            'account_status' => 'on_time',
            'data' => [
                'birth_date' => '1983-04-19',
                'gender' => 'male',
                'height_cm' => 173,
                'weight_kg' => 81.2,
                'timezone' => 'America/Argentina/Buenos_Aires',
                'communication_data' => [
                    'language' => 'es',
                ],
                'notifications' => [
                    'new_plan' => true,
                    'session_reminder' => true,
                ],
                'training_data' => [
                    'experience' => 'intermediate',
                    'days_per_week' => 4,
                ],
            ],
        ]);

        $token = $user->createToken('api-test-token')->plainTextToken;

        tenancy()->end();

        return [
            'tenant' => $tenant,
            'token' => $token,
            'user' => $user,
            'student' => $student,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function apiHeaders(string $token, string $tenantId): array
    {
        return [
            'Authorization' => 'Bearer ' . $token,
            'X-Tenant-ID' => $tenantId,
        ];
    }
}
