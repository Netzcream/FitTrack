<?php

namespace Tests\Unit\Services\Tenant;

use App\Enums\ParticipantType;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationParticipant;
use App\Models\Tenant\Student;
use App\Models\User;
use App\Services\Tenant\MessagingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MessagingServiceTest extends TestCase
{
    public function test_student_started_conversation_is_visible_to_trainer(): void
    {
        Notification::fake();
        $this->actingAsTenant();

        $studentRole = Role::findOrCreate('Alumno');
        $trainerRole = Role::findOrCreate('Entrenador');

        $trainer = User::factory()->create();
        $trainer->assignRole($trainerRole);

        $studentUser = User::factory()->create();
        $studentUser->assignRole($studentRole);

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
                ->where('participant_id', (string) $trainer->id)
                ->exists()
        );

        $this->assertFalse(
            ConversationParticipant::query()
                ->where('conversation_id', $conversation->id)
                ->where('participant_type', ParticipantType::TENANT)
                ->where('participant_id', (string) $studentUser->id)
                ->exists()
        );

        $trainerConversations = $service->getConversations(ParticipantType::TENANT, $trainer->id, 15);

        $this->assertSame(1, $trainerConversations->total());
    }

    public function test_trainer_list_repairs_historic_conversation_with_wrong_tenant_participant(): void
    {
        Notification::fake();
        $this->actingAsTenant();

        $studentRole = Role::findOrCreate('Alumno');
        $trainerRole = Role::findOrCreate('Entrenador');

        $trainer = User::factory()->create();
        $trainer->assignRole($trainerRole);

        $studentUser = User::factory()->create();
        $studentUser->assignRole($studentRole);

        $studentId = DB::table('students')->insertGetId([
            'uuid' => (string) Str::orderedUuid(),
            'user_id' => $studentUser->id,
            'status' => 'active',
            'email' => $studentUser->email,
            'first_name' => 'Alumno',
            'last_name' => 'Historico',
            'is_user_enabled' => true,
            'billing_frequency' => 'monthly',
            'account_status' => 'on_time',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $student = Student::query()->findOrFail($studentId);

        $conversation = Conversation::query()->create([
            'type' => 'tenant_student',
            'student_id' => $student->id,
            'subject' => null,
            'last_message_at' => now(),
        ]);

        ConversationParticipant::query()->create([
            'conversation_id' => $conversation->id,
            'participant_type' => ParticipantType::STUDENT,
            'participant_id' => (string) $student->id,
        ]);

        // Simula el bug previo: tenant participant guardado con el id del user Alumno.
        ConversationParticipant::query()->create([
            'conversation_id' => $conversation->id,
            'participant_type' => ParticipantType::TENANT,
            'participant_id' => (string) $studentUser->id,
        ]);

        DB::table('messages')->insert([
            'conversation_id' => $conversation->id,
            'sender_type' => ParticipantType::STUDENT->value,
            'sender_id' => (string) $student->id,
            'body' => 'Mensaje viejo',
            'status' => 'sent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(
            ConversationParticipant::query()
                ->where('conversation_id', $conversation->id)
                ->where('participant_type', ParticipantType::TENANT)
                ->where('participant_id', (string) $trainer->id)
                ->exists()
        );

        $this->actingAs($trainer);
        $service = app(MessagingService::class);
        $trainerConversations = $service->getConversations(ParticipantType::TENANT, $trainer->id, 15);

        $this->assertSame(1, $trainerConversations->total());
        $this->assertTrue(
            ConversationParticipant::query()
                ->where('conversation_id', $conversation->id)
                ->where('participant_type', ParticipantType::TENANT)
                ->where('participant_id', (string) $trainer->id)
                ->exists()
        );
    }
}
