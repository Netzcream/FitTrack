<?php

namespace Tests\Unit\Models\Tenant;

use App\Models\Tenant\Workout;
use App\Models\Tenant\Student;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\StudentPlanAssignment;
use Tests\TestCase;
use Carbon\Carbon;

class WorkoutTest extends TestCase
{
    /**
     * Test workout creation
     */
    public function test_workout_creation(): void
    {
        $this->actingAsTenant();

        $student = Student::factory()->create();
        $plan = TrainingPlan::factory()->create();
        $assignment = StudentPlanAssignment::factory()->create([
            'student_id' => $student->id,
            'training_plan_id' => $plan->id,
        ]);

        $workout = Workout::factory()->create([
            'student_id' => $student->id,
            'student_plan_assignment_id' => $assignment->id,
            'completed_at' => Carbon::now(),
        ]);

        // Assert in database
        $this->assertDatabaseHas('workouts', [
            'uuid' => $workout->uuid,
            'student_id' => $student->id,
            'student_plan_assignment_id' => $assignment->id,
        ]);

        // Assert properties
        $this->assertNotNull($workout->uuid);
        $this->assertEquals($student->id, $workout->student_id);
        $this->assertEquals($assignment->id, $workout->student_plan_assignment_id);
    }

    /**
     * Test workout isolation between tenants
     */
    public function test_workout_isolation_between_tenants(): void
    {
        // Tenant A
        $tenantA = $this->actingAsTenant();
        $studentA = Student::factory()->create();
        $planA = TrainingPlan::factory()->create();
        $assignmentA = StudentPlanAssignment::factory()->create([
            'student_id' => $studentA->id,
            'training_plan_id' => $planA->id,
        ]);
        $workoutA = Workout::factory()->create([
            'student_id' => $studentA->id,
            'student_plan_assignment_id' => $assignmentA->id,
        ]);

        $this->assertEquals(1, Workout::count());

        // Tenant B
        $tenantB = $this->actingAsTenant();
        $this->assertEquals(0, Workout::count(), 'Tenant B should not see Tenant A workouts');

        // Create in Tenant B
        $studentB = Student::factory()->create();
        $planB = TrainingPlan::factory()->create();
        $assignmentB = StudentPlanAssignment::factory()->create([
            'student_id' => $studentB->id,
            'training_plan_id' => $planB->id,
        ]);
        $workoutB = Workout::factory()->create([
            'student_id' => $studentB->id,
            'student_plan_assignment_id' => $assignmentB->id,
        ]);

        $this->assertEquals(1, Workout::count());

        // Verify isolation
        $this->inTenant($tenantA, function() {
            $this->assertEquals(1, Workout::count());
        });
    }

    /**
     * Test workout by student
     */
    public function test_workout_by_student(): void
    {
        $this->actingAsTenant();

        $student = Student::factory()->create(['first_name' => 'Juan']);
        $plan = TrainingPlan::factory()->create();
        $assignment = StudentPlanAssignment::factory()->create([
            'student_id' => $student->id,
            'training_plan_id' => $plan->id,
        ]);

        Workout::factory()->create([
            'student_id' => $student->id,
            'student_plan_assignment_id' => $assignment->id,
        ]);

        // Find workouts by student
        $workouts = Workout::where('student_id', $student->id)->get();
        $this->assertCount(1, $workouts);
        $this->assertEquals($student->id, $workouts->first()->student_id);
    }

    /**
     * Test multiple workouts per student
     */
    public function test_multiple_workouts_per_student(): void
    {
        $this->actingAsTenant();

        $student = Student::factory()->create();
        $plan = TrainingPlan::factory()->create();
        $assignment = StudentPlanAssignment::factory()->create([
            'student_id' => $student->id,
            'training_plan_id' => $plan->id,
        ]);

        Workout::factory()->count(3)->create([
            'student_id' => $student->id,
            'student_plan_assignment_id' => $assignment->id,
        ]);

        $studentWorkouts = Workout::where('student_id', $student->id)->get();
        $this->assertCount(3, $studentWorkouts);

        // All should belong to same student
        foreach ($studentWorkouts as $workout) {
            $this->assertEquals($student->id, $workout->student_id);
        }
    }

    /**
     * Test workout timestamps
     */
    public function test_workout_timestamps(): void
    {
        $this->actingAsTenant();

        $student = Student::factory()->create();
        $plan = TrainingPlan::factory()->create();
        $assignment = StudentPlanAssignment::factory()->create([
            'student_id' => $student->id,
            'training_plan_id' => $plan->id,
        ]);

        $workout = Workout::factory()->create([
            'student_id' => $student->id,
            'student_plan_assignment_id' => $assignment->id,
        ]);

        // Assert created_at and updated_at exist
        $this->assertNotNull($workout->created_at);
        $this->assertNotNull($workout->updated_at);
        $this->assertInstanceOf(Carbon::class, $workout->created_at);
    }

    /**
     * Test workout soft delete
     */
    public function test_workout_soft_delete(): void
    {
        $this->actingAsTenant();

        $student = Student::factory()->create();
        $plan = TrainingPlan::factory()->create();
        $assignment = StudentPlanAssignment::factory()->create([
            'student_id' => $student->id,
            'training_plan_id' => $plan->id,
        ]);

        $workout = Workout::factory()->create([
            'student_id' => $student->id,
            'student_plan_assignment_id' => $assignment->id,
        ]);

        // Should exist
        $this->assertTrue(Workout::where('uuid', $workout->uuid)->exists());

        // Delete
        $workout->delete();

        // Should not be visible
        $this->assertFalse(Workout::where('uuid', $workout->uuid)->exists());

        // Should be in trash
        $this->assertTrue(Workout::onlyTrashed()->where('uuid', $workout->uuid)->exists());
    }

    /**
     * Test workout query by plan
     */
    public function test_workout_query_by_plan(): void
    {
        $this->actingAsTenant();

        $plan1 = TrainingPlan::factory()->create(['name' => 'Plan 1']);
        $plan2 = TrainingPlan::factory()->create(['name' => 'Plan 2']);

        $assignment1 = StudentPlanAssignment::factory()->create([
            'training_plan_id' => $plan1->id,
        ]);
        $assignment2 = StudentPlanAssignment::factory()->create([
            'training_plan_id' => $plan2->id,
        ]);

        Workout::factory()->count(2)->create([
            'student_plan_assignment_id' => $assignment1->id,
            'student_id' => $assignment1->student_id,
        ]);
        Workout::factory()->count(3)->create([
            'student_plan_assignment_id' => $assignment2->id,
            'student_id' => $assignment2->student_id,
        ]);

        // Find by plan assignment
        $plan1Workouts = Workout::where('student_plan_assignment_id', $assignment1->id)->get();
        $plan2Workouts = Workout::where('student_plan_assignment_id', $assignment2->id)->get();

        $this->assertCount(2, $plan1Workouts);
        $this->assertCount(3, $plan2Workouts);
    }
}
