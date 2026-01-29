<?php

namespace Tests\Unit\Models\Tenant;

use App\Models\Tenant\Workout;
use App\Models\Tenant\Student;
use App\Models\Tenant\TrainingPlan;
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

        $workout = Workout::factory()->create([
            'student_uuid' => $student->uuid,
            'training_plan_uuid' => $plan->uuid,
            'completed_at' => Carbon::now(),
        ]);

        // Assert in database
        $this->assertDatabaseHas('workouts', [
            'uuid' => $workout->uuid,
            'student_uuid' => $student->uuid,
            'training_plan_uuid' => $plan->uuid,
        ]);

        // Assert properties
        $this->assertNotNull($workout->uuid);
        $this->assertEquals($student->uuid, $workout->student_uuid);
        $this->assertEquals($plan->uuid, $workout->training_plan_uuid);
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
        $workoutA = Workout::factory()->create([
            'student_uuid' => $studentA->uuid,
            'training_plan_uuid' => $planA->uuid,
        ]);

        $this->assertEquals(1, Workout::count());

        // Tenant B
        $tenantB = $this->actingAsTenant();
        $this->assertEquals(0, Workout::count(), 'Tenant B should not see Tenant A workouts');

        // Create in Tenant B
        $studentB = Student::factory()->create();
        $planB = TrainingPlan::factory()->create();
        $workoutB = Workout::factory()->create([
            'student_uuid' => $studentB->uuid,
            'training_plan_uuid' => $planB->uuid,
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

        Workout::factory()->create([
            'student_uuid' => $student->uuid,
            'training_plan_uuid' => $plan->uuid,
        ]);

        // Find workouts by student
        $workouts = Workout::where('student_uuid', $student->uuid)->get();
        $this->assertCount(1, $workouts);
        $this->assertEquals($student->uuid, $workouts->first()->student_uuid);
    }

    /**
     * Test multiple workouts per student
     */
    public function test_multiple_workouts_per_student(): void
    {
        $this->actingAsTenant();

        $student = Student::factory()->create();
        $plan = TrainingPlan::factory()->create();

        Workout::factory()->count(3)->create([
            'student_uuid' => $student->uuid,
            'training_plan_uuid' => $plan->uuid,
        ]);

        $studentWorkouts = Workout::where('student_uuid', $student->uuid)->get();
        $this->assertCount(3, $studentWorkouts);

        // All should belong to same student
        foreach ($studentWorkouts as $workout) {
            $this->assertEquals($student->uuid, $workout->student_uuid);
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

        $workout = Workout::factory()->create([
            'student_uuid' => $student->uuid,
            'training_plan_uuid' => $plan->uuid,
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

        $workout = Workout::factory()->create([
            'student_uuid' => $student->uuid,
            'training_plan_uuid' => $plan->uuid,
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

        Workout::factory()->count(2)->create(['training_plan_uuid' => $plan1->uuid]);
        Workout::factory()->count(3)->create(['training_plan_uuid' => $plan2->uuid]);

        // Find by plan
        $plan1Workouts = Workout::where('training_plan_uuid', $plan1->uuid)->get();
        $plan2Workouts = Workout::where('training_plan_uuid', $plan2->uuid)->get();

        $this->assertCount(2, $plan1Workouts);
        $this->assertCount(3, $plan2Workouts);
    }
}
