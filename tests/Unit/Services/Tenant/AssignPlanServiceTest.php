<?php

namespace Tests\Unit\Services\Tenant;

use App\Models\Tenant;
use App\Models\Tenant\Student;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\StudentPlanAssignment;
use App\Services\Tenant\AssignPlanService;
use Tests\TestCase;
use Carbon\Carbon;

class AssignPlanServiceTest extends TestCase
{
    private AssignPlanService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AssignPlanService();
    }

    /**
     * Test assigning a training plan to a student
     */
    public function test_assign_plan_to_student(): void
    {
        $tenant = $this->actingAsTenant();

        $student = Student::factory()->create();
        $plan = TrainingPlan::factory()->create();
        $startsAt = Carbon::now();
        $endsAt = $startsAt->copy()->addWeeks(4);

        // Assign plan
        $assignment = $this->service->assign($plan, $student, $startsAt, $endsAt);

        // Verify assignment created
        $this->assertNotNull($assignment);
        $this->assertEquals($student->id, $assignment->student_id);
        $this->assertEquals($plan->id, $assignment->training_plan_id);
        $this->assertTrue($assignment->is_current);

        // Verify in database
        $this->assertDatabaseHas('student_plan_assignments', [
            'student_id' => $student->id,
            'training_plan_id' => $plan->id,
        ]);
    }

    /**
     * Test only one active assignment per student
     */
    public function test_only_one_active_assignment_per_student(): void
    {
        $this->actingAsTenant();

        $student = Student::factory()->create();
        $plan1 = TrainingPlan::factory()->create();
        $plan2 = TrainingPlan::factory()->create();

        $startsAt1 = Carbon::now();
        $endsAt1 = $startsAt1->copy()->addWeeks(4);

        $startsAt2 = $endsAt1->copy()->addDay();
        $endsAt2 = $startsAt2->copy()->addWeeks(4);

        // Assign first plan
        $assignment1 = $this->service->assign($plan1, $student, $startsAt1, $endsAt1);
        $this->assertTrue($assignment1->is_current);

        // Assign second plan (should replace first)
        $assignment2 = $this->service->assign($plan2, $student, $startsAt2, $endsAt2, true);

        // Verify second is current
        $this->assertTrue($assignment2->is_current);

        // Verify first is no longer current
        $assignment1->refresh();
        $this->assertFalse($assignment1->is_current);
    }

    /**
     * Test assignment snapshots plan exercises
     */
    public function test_assignment_snapshots_exercises(): void
    {
        $this->actingAsTenant();

        $student = Student::factory()->create();
        $plan = TrainingPlan::factory()->create();

        $startsAt = Carbon::now();
        $endsAt = $startsAt->copy()->addWeeks(4);

        $assignment = $this->service->assign($plan, $student, $startsAt, $endsAt);

        // Verify exercises_data is stored as JSON
        $this->assertNotNull($assignment->exercises_snapshot);
        $this->assertIsArray($assignment->exercises_snapshot);

        // Snapshot should be captured at assignment time
        // Changes to plan later should not affect assignment
    }

    /**
     * Test assigning plan in different tenant isolation
     */
    public function test_assignment_isolated_between_tenants(): void
    {
        $tenantA = $this->actingAsTenant();
        $studentA = Student::factory()->create();
        $planA = TrainingPlan::factory()->create();

        $startsAt = Carbon::now();
        $endsAt = $startsAt->copy()->addWeeks(4);

        $this->service->assign($planA, $studentA, $startsAt, $endsAt);
        $this->assertEquals(1, StudentPlanAssignment::count());

        // Switch tenant
        $tenantB = $this->actingAsTenant();
        $this->assertEquals(0, StudentPlanAssignment::count());

        $studentB = Student::factory()->create();
        $planB = TrainingPlan::factory()->create();
        $this->service->assign($planB, $studentB, $startsAt, $endsAt);

        // Verify isolation
        $this->assertEquals(1, StudentPlanAssignment::count());

        $this->inTenant($tenantA, function() {
            $this->assertEquals(1, StudentPlanAssignment::count());
        });
    }
}
