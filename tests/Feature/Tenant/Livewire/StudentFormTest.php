<?php

namespace Tests\Feature\Tenant\Livewire;

use App\Models\Tenant;
use App\Models\Tenant\Student;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\StudentPlanAssignment;
use App\Livewire\Tenant\Students\Form as StudentForm;
use App\Livewire\Tenant\Students\AssignPlan as AssignPlanComponent;
use App\Livewire\Tenant\Students\Index as StudentIndex;
use Livewire\Livewire;
use Tests\TestCase;
use Carbon\Carbon;

/**
 * Example Livewire Component Test
 *
 * This shows how to test Livewire components in a multi-tenant context.
 * Adjust based on your actual component path and methods.
 */
class StudentFormTest extends TestCase
{
    /**
     * Example: Test Livewire component creates student
     *
     * IMPORTANT: Replace 'Tenant.Students.StudentForm' with your actual component path
     * Find it in: app/Livewire/Tenant/Students/StudentForm.php
     */
    public function test_livewire_student_form_creates_student(): void
    {
        $tenant = $this->actingAsTenant();

        $response = Livewire::test(StudentForm::class)
            ->set('first_name', 'Juan')
            ->set('last_name', 'Pérez')
            ->set('email', 'juan@example.com')
            ->call('save');

        // Verify component returned to list or success
        // (adjust assertions based on your component behavior)
        $this->assertDatabaseHas('students', [
            'first_name' => 'Juan',
            'email' => 'juan@example.com',
        ]);
    }

    /**
     * Example: Test form validation
     */
    public function test_livewire_student_form_validation(): void
    {
        $this->actingAsTenant();

        $response = Livewire::test(StudentForm::class)
            ->set('first_name', '')  // Empty required field
            ->call('save')
            ->assertHasErrors('first_name');  // Should have validation error
    }

    /**
     * Example: Test assign plan component in tenant context
     */
    public function test_livewire_assign_plan_isolated_by_tenant(): void
    {
        $tenantA = $this->actingAsTenant();

        $studentA = Student::factory()->create();
        $planA = TrainingPlan::factory()->create(['name' => 'Plan A']);

        // Tenant A assigns plan
        $response = Livewire::test(AssignPlanComponent::class, ['student' => $studentA])
            ->set('training_plan_id', $planA->id)
            ->set('starts_at', now()->format('Y-m-d'))
            ->set('ends_at', now()->addWeeks(4)->format('Y-m-d'))
            ->call('assign');

        $this->assertDatabaseHas('student_plan_assignments', [
            'student_id' => $studentA->id,
            'training_plan_id' => $planA->id,
        ]);

        // Switch to Tenant B
        $tenantB = $this->actingAsTenant();

        // Tenant B should NOT see Tenant A's assignment
        $this->assertEquals(0, StudentPlanAssignment::count());

        // Tenant B creates different data
        $studentB = Student::factory()->create();
        $planB = TrainingPlan::factory()->create(['name' => 'Plan B']);

        Livewire::test(AssignPlanComponent::class, ['student' => $studentB])
            ->set('training_plan_id', $planB->id)
            ->set('starts_at', now()->format('Y-m-d'))
            ->set('ends_at', now()->addWeeks(4)->format('Y-m-d'))
            ->call('assign');

        // Verify isolation
        $this->assertEquals(1, StudentPlanAssignment::count());

        $this->inTenant($tenantA, function() {
            $this->assertEquals(1, StudentPlanAssignment::count());
        });
    }

    /**
     * Example: Test list component shows only tenant's data
     */
    public function test_livewire_student_list_shows_only_tenant_students(): void
    {
        $tenantA = $this->actingAsTenant();
        Student::factory()->count(5)->create();
        $this->assertEquals(5, Student::count());

        // Component should show 5 students
        $response = Livewire::test(StudentIndex::class);
        // Adjust assertion based on actual component
        // $response->assertSee('expected_student_name');

        // Switch tenant
        $tenantB = $this->actingAsTenant();
        Student::factory()->count(3)->create();
        $this->assertEquals(3, Student::count());

        $this->inTenant($tenantA, function() {
            $this->assertEquals(5, Student::count());
        });

        // Component in Tenant B should show 3 students (not 5)
        $response = Livewire::test(StudentIndex::class);
        // $response should reflect only 3 students
    }
}
