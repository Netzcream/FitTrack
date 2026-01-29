<?php

namespace Tests\Unit\Models\Tenant;

use App\Models\Tenant;
use App\Models\Tenant\Student;
use Tests\TestCase;

class StudentTest extends TestCase
{
    /**
     * Test student creation within tenant context
     */
    public function test_student_creation_in_tenant(): void
    {
        $this->actingAsTenant();

        $student = Student::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan@example.com',
            'phone' => '1234567890',
        ]);

        // Assert student exists in DB
        $this->assertDatabaseHas('students', [
            'uuid' => $student->uuid,
            'email' => 'juan@example.com',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);

        // Assert properties
        $this->assertNotNull($student->uuid);
        $this->assertEquals('Juan', $student->first_name);
        $this->assertEquals('Pérez', $student->last_name);
        $this->assertEquals('juan@example.com', $student->email);
        $this->assertEquals('Juan Pérez', $student->full_name);
    }

    /**
     * Test student isolation between tenants
     *
     * Students in tenant A should NOT be visible in tenant B
     */
    public function test_students_isolated_between_tenants(): void
    {
        // Tenant A: Create student
        $tenantA = $this->actingAsTenant();
        $studentA = Student::factory()->create(['email' => 'student.a@example.com']);

        // Assert data exists in Tenant A
        $this->assertEquals(1, Student::count());
        $this->assertTrue(Student::where('email', 'student.a@example.com')->exists());

        // Switch to Tenant B: Student A should not be visible
        $tenantB = $this->actingAsTenant();
        $this->assertEquals(0, Student::count(), 'Tenant B should have 0 students');
        $this->assertFalse(Student::where('email', 'student.a@example.com')->exists(), 'Student A not visible in Tenant B');

        // Create student in Tenant B
        $studentB = Student::factory()->create(['email' => 'student.b@example.com']);
        $this->assertEquals(1, Student::count());
        $this->assertDatabaseHas('students', ['email' => 'student.b@example.com']);

        // Back to Tenant A: Only Student A should be visible
        $this->inTenant($tenantA, function() {
            $this->assertEquals(1, Student::count());
            $this->assertTrue(Student::where('email', 'student.a@example.com')->exists());
            $this->assertFalse(Student::where('email', 'student.b@example.com')->exists());
        });
    }

    /**
     * Test student can be searched
     */
    public function test_student_search(): void
    {
        $this->actingAsTenant();

        Student::factory()->create(['first_name' => 'Carlos', 'email' => 'carlos@example.com']);
        Student::factory()->create(['first_name' => 'Maria', 'email' => 'maria@example.com']);
        Student::factory()->create(['first_name' => 'Juan', 'email' => 'juan@example.com']);

        // Search by name
        $carlos = Student::where('first_name', 'Carlos')->first();
        $this->assertNotNull($carlos);
        $this->assertEquals('Carlos', $carlos->first_name);
        $this->assertEquals('carlos@example.com', $carlos->email);

        // Search by email
        $maria = Student::where('email', 'maria@example.com')->first();
        $this->assertNotNull($maria);
        $this->assertEquals('Maria', $maria->first_name);

        // Count all students
        $allStudents = Student::all();
        $this->assertCount(3, $allStudents);
    }

    /**
     * Test student soft delete
     */
    public function test_student_soft_delete(): void
    {
        $this->actingAsTenant();

        $student = Student::factory()->create(['email' => 'delete.me@example.com']);
        $this->assertTrue(Student::where('email', 'delete.me@example.com')->exists());

        // Delete student
        $student->delete();

        // Should not be visible in normal queries
        $this->assertFalse(Student::where('email', 'delete.me@example.com')->exists());

        // But should exist in trashed
        $this->assertTrue(Student::onlyTrashed()->where('email', 'delete.me@example.com')->exists());
    }

    /**
     * Test full name accessor
     */
    public function test_full_name_accessor(): void
    {
        $this->actingAsTenant();

        $student = Student::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez García',
        ]);

        $this->assertEquals('Juan Pérez García', $student->full_name);
    }

    /**
     * Test student with only first name
     */
    public function test_student_partial_name(): void
    {
        $this->actingAsTenant();

        $student = Student::factory()->create([
            'first_name' => 'Carlos',
            'last_name' => null,
        ]);

        $this->assertEquals('Carlos', $student->full_name);
    }

    /**
     * Test student current plan assignment
     */
    public function test_student_current_plan_assignment(): void
    {
        $this->actingAsTenant();

        $student = Student::factory()->create();

        // Initially no assignment
        $this->assertNull($student->currentPlanAssignment());

        // After creating assignment, it should be accessible
        // (Assuming Student has planAssignments() relationship and currentPlanAssignment() scope)
    }
}
