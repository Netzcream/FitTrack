<?php

namespace Tests\Unit\Models\Tenant;

use App\Models\Tenant\Exercise;
use App\Models\Tenant\TrainingPlan;
use Tests\TestCase;

class ExerciseTest extends TestCase
{
    /**
     * Test exercise creation
     */
    public function test_exercise_creation(): void
    {
        $this->actingAsTenant();

        $exercise = Exercise::factory()->create([
            'name' => 'Sentadilla',
            'description' => 'Ejercicio para piernas',
        ]);

        // Assert in database
        $this->assertDatabaseHas('exercises', [
            'uuid' => $exercise->uuid,
            'name' => 'Sentadilla',
            'description' => 'Ejercicio para piernas',
        ]);

        // Assert properties
        $this->assertNotNull($exercise->uuid);
        $this->assertEquals('Sentadilla', $exercise->name);
    }

    /**
     * Test exercise isolation between tenants
     */
    public function test_exercise_isolation_between_tenants(): void
    {
        // Tenant A
        $tenantA = $this->actingAsTenant();
        $exerciseA = Exercise::factory()->create(['name' => 'Bench Press']);

        $this->assertEquals(1, Exercise::count());
        $this->assertTrue(Exercise::where('name', 'Bench Press')->exists());

        // Tenant B
        $tenantB = $this->actingAsTenant();
        $this->assertEquals(0, Exercise::count(), 'Tenant B should not see Tenant A exercises');

        // Create in Tenant B
        $exerciseB = Exercise::factory()->create(['name' => 'Deadlift']);
        $this->assertEquals(1, Exercise::count());

        // Verify isolation
        $this->inTenant($tenantA, function() {
            $this->assertEquals(1, Exercise::count());
            $this->assertTrue(Exercise::where('name', 'Bench Press')->exists());
            $this->assertFalse(Exercise::where('name', 'Deadlift')->exists());
        });
    }

    /**
     * Test multiple exercises
     */
    public function test_multiple_exercises(): void
    {
        $this->actingAsTenant();

        Exercise::factory()->create(['name' => 'Sentadilla']);
        Exercise::factory()->create(['name' => 'Press de Banca']);
        Exercise::factory()->create(['name' => 'Peso Muerto']);

        $allExercises = Exercise::all();
        $this->assertCount(3, $allExercises);
    }

    /**
     * Test exercise search
     */
    public function test_exercise_search(): void
    {
        $this->actingAsTenant();

        Exercise::factory()->create(['name' => 'Curl de Bíceps']);
        Exercise::factory()->create(['name' => 'Extensión de Tríceps']);
        Exercise::factory()->create(['name' => 'Curl Martillo']);

        // Search by name
        $exercise = Exercise::where('name', 'Curl de Bíceps')->first();
        $this->assertNotNull($exercise);
        $this->assertEquals('Curl de Bíceps', $exercise->name);

        // Count all
        $this->assertEquals(3, Exercise::count());
    }

    /**
     * Test exercise with reps and sets
     */
    public function test_exercise_with_reps_and_sets(): void
    {
        $this->actingAsTenant();

        $exercise = Exercise::factory()->create([
            'name' => 'Sentadilla',
            'meta' => [
                'reps' => 12,
                'sets' => 5,
            ],
        ]);

        $retrieved = Exercise::where('uuid', $exercise->uuid)->first();
        $this->assertEquals(12, $retrieved->meta['reps'] ?? null);
        $this->assertEquals(5, $retrieved->meta['sets'] ?? null);
    }

    /**
     * Test exercise soft delete
     */
    public function test_exercise_soft_delete(): void
    {
        $this->actingAsTenant();

        $exercise = Exercise::factory()->create(['name' => 'Delete Me']);

        // Should exist
        $this->assertTrue(Exercise::where('name', 'Delete Me')->exists());

        // Delete
        $exercise->delete();

        // Should not be visible
        $this->assertFalse(Exercise::where('name', 'Delete Me')->exists());

        // Should be in trash
        $this->assertTrue(Exercise::onlyTrashed()->where('name', 'Delete Me')->exists());
    }

    /**
     * Test exercise update
     */
    public function test_exercise_update(): void
    {
        $this->actingAsTenant();

        $exercise = Exercise::factory()->create([
            'name' => 'Original Name',
            'meta' => [
                'reps' => 10,
            ],
        ]);

        // Update
        $exercise->update([
            'name' => 'New Name',
            'meta' => [
                'reps' => 15,
            ],
        ]);

        // Assert properties
        $this->assertEquals('New Name', $exercise->name);
        $this->assertEquals(15, $exercise->meta['reps'] ?? null);
    }
}
