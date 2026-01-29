<?php

namespace Tests\Feature\Tenant;

use App\Models\Tenant;
use App\Models\Tenant\Student;
use App\Models\Tenant\TrainingPlan;
use Tests\TestCase;
use Carbon\Carbon;

class StudentManagementFeatureTest extends TestCase
{
    /**
     * Test complete student workflow in tenant
     */
    public function test_complete_student_workflow(): void
    {
        $tenant = $this->actingAsTenant();

        // 1. Create student
        $student = Student::factory()->create([
            'first_name' => 'Juan',
            'email' => 'juan@example.com',
        ]);

        $this->assertDatabaseHas('students', [
            'uuid' => $student->uuid,
            'email' => 'juan@example.com',
        ]);

        // 2. Create training plan
        $plan = TrainingPlan::factory()->create([
            'name' => 'Plan de Fuerza',
        ]);

        // 3. Verify plan exists in same tenant
        $this->assertTrue(
            TrainingPlan::where('uuid', $plan->uuid)->exists(),
            'Plan should exist in current tenant'
        );

        // 4. Switch to different tenant, verify plan doesn't exist
        $newTenant = $this->actingAsTenant();
        $this->assertFalse(
            TrainingPlan::where('uuid', $plan->uuid)->exists(),
            'Plan should NOT exist in different tenant'
        );

        // 5. Return to first tenant
        $this->inTenant($tenant, function() use ($plan, $student) {
            $this->assertTrue(
                TrainingPlan::where('uuid', $plan->uuid)->exists(),
                'Plan should exist again in original tenant'
            );

            $this->assertTrue(
                Student::where('uuid', $student->uuid)->exists(),
                'Student should exist in original tenant'
            );
        });
    }

    /**
     * Test bulk student creation and filtering
     */
    public function test_bulk_student_operations(): void
    {
        $this->actingAsTenant();

        // Create multiple students
        $students = Student::factory()->count(10)->create();

        // Filter by name
        $this->assertEquals(10, Student::count());

        // Create specific student
        Student::factory()->create(['first_name' => 'Admin']);
        $adminCount = Student::where('first_name', 'Admin')->count();
        $this->assertEquals(1, $adminCount);

        // Test soft deletes (if applicable)
        if (method_exists(Student::class, 'onlyTrashed')) {
            $student = Student::first();
            $student->delete();

            $this->assertEquals(10, Student::count()); // Still 10 active
            $this->assertEquals(1, Student::onlyTrashed()->count());
        }
    }

    /**
     * Test student data persistence across operations
     */
    public function test_student_data_persistence(): void
    {
        $this->actingAsTenant();

        $original = Student::factory()->create([
            'first_name' => 'Carlos',
            'email' => 'carlos@test.com',
        ]);

        // Retrieve and verify
        $retrieved = Student::find($original->uuid);
        $this->assertEquals('Carlos', $retrieved->first_name);
        $this->assertEquals('carlos@test.com', $retrieved->email);

        // Update and verify
        $retrieved->first_name = 'Pablo';
        $retrieved->save();

        $updated = Student::find($original->uuid);
        $this->assertEquals('Pablo', $updated->first_name);
    }
}
