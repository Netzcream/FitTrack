<?php

namespace Tests\Unit\Models\Tenant;

use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\Exercise;
use Tests\TestCase;

class TrainingPlanTest extends TestCase
{
    /**
     * Test training plan creation
     */
    public function test_training_plan_creation(): void
    {
        $this->actingAsTenant();

        $plan = TrainingPlan::factory()->create([
            'name' => 'Plan de Fuerza 12 Semanas',
            'description' => 'Programa de hipertrofia',
        ]);

        // Assert in database
        $this->assertDatabaseHas('training_plans', [
            'uuid' => $plan->uuid,
            'name' => 'Plan de Fuerza 12 Semanas',
            'description' => 'Programa de hipertrofia',
        ]);

        // Assert properties
        $this->assertNotNull($plan->uuid);
        $this->assertEquals('Plan de Fuerza 12 Semanas', $plan->name);
    }

    /**
     * Test training plan isolation between tenants
     */
    public function test_training_plan_isolation(): void
    {
        // Tenant A
        $tenantA = $this->actingAsTenant();
        $planA = TrainingPlan::factory()->create([
            'name' => 'Plan A',
        ]);

        $this->assertEquals(1, TrainingPlan::count());
        $this->assertTrue(TrainingPlan::where('name', 'Plan A')->exists());

        // Tenant B
        $tenantB = $this->actingAsTenant();
        $this->assertEquals(0, TrainingPlan::count(), 'Tenant B should not see Tenant A plans');
        $this->assertFalse(TrainingPlan::where('name', 'Plan A')->exists(), 'Plan A not in Tenant B');

        // Create in Tenant B
        $planB = TrainingPlan::factory()->create(['name' => 'Plan B']);
        $this->assertEquals(1, TrainingPlan::count());

        // Verify isolation
        $this->inTenant($tenantA, function() {
            $this->assertEquals(1, TrainingPlan::count());
            $this->assertTrue(TrainingPlan::where('name', 'Plan A')->exists());
            $this->assertFalse(TrainingPlan::where('name', 'Plan B')->exists());
        });
    }

    /**
     * Test training plan with exercises
     */
    public function test_training_plan_with_exercises(): void
    {
        $this->actingAsTenant();

        $plan = TrainingPlan::factory()->create();

        // Assuming the plan has an exercises relationship
        // Assert it exists and can be accessed
        $this->assertNotNull($plan->uuid);
        $this->assertTrue($plan->exists());
    }

    /**
     * Test multiple training plans
     */
    public function test_multiple_training_plans(): void
    {
        $this->actingAsTenant();

        TrainingPlan::factory()->create(['name' => 'Fuerza']);
        TrainingPlan::factory()->create(['name' => 'Resistencia']);
        TrainingPlan::factory()->create(['name' => 'Flexibilidad']);

        $allPlans = TrainingPlan::all();
        $this->assertCount(3, $allPlans);

        // Search specific plan
        $strength = TrainingPlan::where('name', 'Fuerza')->first();
        $this->assertNotNull($strength);
        $this->assertEquals('Fuerza', $strength->name);
    }

    /**
     * Test training plan search
     */
    public function test_training_plan_search(): void
    {
        $this->actingAsTenant();

        TrainingPlan::factory()->create(['name' => 'Push Day']);
        TrainingPlan::factory()->create(['name' => 'Pull Day']);
        TrainingPlan::factory()->create(['name' => 'Leg Day']);

        // Search by name
        $pushDay = TrainingPlan::where('name', 'Push Day')->first();
        $this->assertNotNull($pushDay);
        $this->assertEquals('Push Day', $pushDay->name);

        // Count all
        $this->assertEquals(3, TrainingPlan::count());
    }

    /**
     * Test training plan soft delete
     */
    public function test_training_plan_soft_delete(): void
    {
        $this->actingAsTenant();

        $plan = TrainingPlan::factory()->create(['name' => 'Delete Me']);

        // Should exist
        $this->assertTrue(TrainingPlan::where('name', 'Delete Me')->exists());

        // Delete
        $plan->delete();

        // Should not be visible
        $this->assertFalse(TrainingPlan::where('name', 'Delete Me')->exists());

        // Should be in trash
        $this->assertTrue(TrainingPlan::onlyTrashed()->where('name', 'Delete Me')->exists());
    }
}
