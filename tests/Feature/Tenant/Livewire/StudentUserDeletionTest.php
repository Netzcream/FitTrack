<?php

namespace Tests\Feature\Tenant\Livewire;

use App\Livewire\Tenant\Students\Index as StudentsIndex;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentPlanAssignment;
use App\Models\Tenant\TrainingPlan;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class StudentUserDeletionTest extends TestCase
{
    public function test_deleting_user_also_deletes_linked_student_and_plan_assignments(): void
    {
        $this->actingAsTenant();

        [$user, $student, $assignment] = $this->createLinkedStudentWithAssignment();

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('student_plan_assignments', ['id' => $assignment->id]);
    }

    public function test_deleting_student_from_index_also_deletes_linked_user(): void
    {
        $this->actingAsTenant();

        [$user, $student, $assignment] = $this->createLinkedStudentWithAssignment();

        Livewire::test(StudentsIndex::class)
            ->call('confirmDelete', $student->uuid)
            ->call('delete')
            ->assertDispatched('student-deleted');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('student_plan_assignments', ['id' => $assignment->id]);
    }

    /**
     * @return array{0: User, 1: Student, 2: StudentPlanAssignment}
     */
    private function createLinkedStudentWithAssignment(): array
    {
        $email = Str::lower(fake()->unique()->safeEmail());

        $user = User::factory()->create([
            'email' => $email,
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'email' => $email,
            'first_name' => 'Test',
            'last_name' => 'Student',
        ]);

        $plan = TrainingPlan::create([
            'name' => 'Plan de prueba',
        ]);

        $assignment = StudentPlanAssignment::create([
            'student_id' => $student->id,
            'training_plan_id' => $plan->id,
            'name' => 'Asignacion de prueba',
        ]);

        return [$user, $student, $assignment];
    }
}
