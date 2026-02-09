<?php

namespace Tests\Feature\Api;

use App\Models\Tenant;
use App\Models\Tenant\Student;
use App\Models\User;
use App\Services\Tenant\MessagingService;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MessagingApiAuthorizationTest extends TestCase
{
    public function test_student_can_fetch_own_conversation(): void
    {
        Notification::fake();

        $tenant = $this->actingAsTenant();

        $user = User::factory()->create([
            'email' => 'student.messaging@example.com',
            'name' => 'Student Messaging',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'status' => 'active',
            'email' => $user->email,
            'first_name' => 'Student',
            'last_name' => 'Messaging',
            'is_user_enabled' => true,
            'billing_frequency' => 'monthly',
            'account_status' => 'on_time',
        ]);

        $conversation = app(MessagingService::class)->findOrCreateConversation($student->id);
        $token = $user->createToken('api-messaging-test-token')->plainTextToken;

        tenancy()->end();

        $response = $this
            ->withHeaders($this->apiHeaders($token, $tenant->id))
            ->getJson('/api/messages/conversation?per_page=50');

        $response->assertOk();
        $response->assertJsonPath('conversation.id', $conversation->id);
        $response->assertJsonPath('conversation.student_id', $student->id);
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
