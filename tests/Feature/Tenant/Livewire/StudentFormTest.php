<?php

namespace Tests\Feature\Tenant\Livewire;

use App\Models\Tenant;
use App\Models\Tenant\Student;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\StudentPlanAssignment;
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

        // Initialize Livewire component
        // Component path format: 'Tenant.Students.StudentForm'
        $response = Livewire::test('tenant.students.student-form')
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

        $response = Livewire::test('tenant.students.student-form')
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
        $response = Livewire::test('tenant.students.assign-plan')
            ->set('student_uuid', $studentA->uuid)
            ->set('training_plan_uuid', $planA->uuid)
            ->call('assignPlan');

        $this->assertDatabaseHas('student_plan_assignments', [
            'student_uuid' => $studentA->uuid,
            'training_plan_uuid' => $planA->uuid,
        ]);

        // Switch to Tenant B
        $tenantB = $this->actingAsTenant();

        // Tenant B should NOT see Tenant A's assignment
        $this->assertEquals(0, StudentPlanAssignment::count());

        // Tenant B creates different data
        $studentB = Student::factory()->create();
        $planB = TrainingPlan::factory()->create(['name' => 'Plan B']);

        Livewire::test('tenant.students.assign-plan')
            ->set('student_uuid', $studentB->uuid)
            ->set('training_plan_uuid', $planB->uuid)
            ->call('assignPlan');

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

        // Component should show 5 students
        $response = Livewire::test('tenant.students.student-list');
        // Adjust assertion based on actual component
        // $response->assertSee('expected_student_name');

        // Switch tenant
        $tenantB = $this->actingAsTenant();
        Student::factory()->count(3)->create();

        // Component in Tenant B should show 3 students (not 5)
        $response = Livewire::test('tenant.students.student-list');
        // $response should reflect only 3 students
    }
}
