<?php

namespace Tests\Unit\Services\Tenant;

use App\Enums\ParticipantType;
use App\Events\Tenant\MessageCreated;
use App\Models\Tenant\ConversationParticipant;
use App\Models\Tenant\Student;
use App\Models\User;
use App\Services\Tenant\MessagingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class MessagingServiceTest extends TestCase
{
    public function test_student_started_conversation_is_visible_to_tenant(): void
    {
        Notification::fake();
        $tenant = $this->actingAsTenant();

        $studentUser = User::factory()->create();

        $studentId = DB::table('students')->insertGetId([
            'uuid' => (string) Str::orderedUuid(),
            'user_id' => $studentUser->id,
            'status' => 'active',
            'email' => $studentUser->email,
            'first_name' => 'Alumno',
            'last_name' => 'Prueba',
            'is_user_enabled' => true,
            'billing_frequency' => 'monthly',
            'account_status' => 'on_time',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $student = Student::query()->findOrFail($studentId);

        $this->actingAs($studentUser);

        $service = app(MessagingService::class);
        $conversation = $service->findOrCreateConversation($student->id);

        $service->sendMessage(
            $conversation->id,
            ParticipantType::STUDENT,
            $student->id,
            'Hola profe'
        );

        $this->assertTrue(
            ConversationParticipant::query()
                ->where('conversation_id', $conversation->id)
                ->where('participant_type', ParticipantType::TENANT)
                ->where('participant_id', (string) MessagingService::TENANT_PARTICIPANT_ID)
                ->exists()
        );

        $tenantConversations = $service->getConversations(ParticipantType::TENANT, (string) $tenant->id, 15);

        $this->assertSame(1, $tenantConversations->total());
    }

    public function test_teacher_and_student_share_same_conversation_thread(): void
    {
        Notification::fake();
        $tenant = $this->actingAsTenant();

        $teacherUser = User::factory()->create();
        $studentUser = User::factory()->create();

        $studentId = DB::table('students')->insertGetId([
            'uuid' => (string) Str::orderedUuid(),
            'user_id' => $studentUser->id,
            'status' => 'active',
            'email' => $studentUser->email,
            'first_name' => 'Carlos',
            'last_name' => 'Alumno',
            'is_user_enabled' => true,
            'billing_frequency' => 'monthly',
            'account_status' => 'on_time',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $student = Student::query()->findOrFail($studentId);

        $service = app(MessagingService::class);

        $this->actingAs($teacherUser);
        $teacherConversation = $service->findOrCreateConversation($student->id);
        $service->sendMessage(
            $teacherConversation->id,
            ParticipantType::TENANT,
            $teacherUser->id,
            'Hola Carlos'
        );

        $this->actingAs($studentUser);
        $studentConversation = $service->getConversationForStudent($student->id);
        $this->assertNotNull($studentConversation);
        $this->assertSame($teacherConversation->id, $studentConversation->id);

        $service->sendMessage(
            $studentConversation->id,
            ParticipantType::STUDENT,
            $student->id,
            'Hola Profe'
        );

        $messages = $service->getMessages($teacherConversation->id, 50);
        $this->assertSame(2, $messages->total());

        $tenantConversations = $service->getConversations(ParticipantType::TENANT, (string) $tenant->id, 15);
        $this->assertSame(1, $tenantConversations->total());
    }

    public function test_send_message_dispatches_message_created_event(): void
    {
        Notification::fake();
        $tenant = $this->actingAsTenant();
        Event::fake([MessageCreated::class]);

        $studentUser = User::factory()->create();

        $studentId = DB::table('students')->insertGetId([
            'uuid' => (string) Str::orderedUuid(),
            'user_id' => $studentUser->id,
            'status' => 'active',
            'email' => $studentUser->email,
            'first_name' => 'Event',
            'last_name' => 'Student',
            'is_user_enabled' => true,
            'billing_frequency' => 'monthly',
            'account_status' => 'on_time',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(MessagingService::class);
        $conversation = $service->findOrCreateConversation($studentId);

        $service->sendMessage(
            $conversation->id,
            ParticipantType::STUDENT,
            $studentId,
            'Mensaje de prueba'
        );

        Event::assertDispatched(MessageCreated::class, function (MessageCreated $event) use ($tenant) {
            return $event->messageId > 0
                && $event->tenantId === (string) $tenant->id;
        });
    }
}
