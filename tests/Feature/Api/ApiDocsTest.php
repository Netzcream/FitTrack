<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class ApiDocsTest extends TestCase
{
    public function test_api_docs_is_public_and_returns_openapi_structure(): void
    {
        $response = $this->getJson('/api/docs');

        $response->assertOk();
        $response->assertJsonPath('openapi', '3.0.3');
        $response->assertJsonPath('paths./docs.get.security', []);
        $response->assertJsonPath('paths./auth/login.post.security', []);

        $this->assertNotEmpty($response->json('paths./home.get.parameters'));
        $this->assertNotNull($response->json('paths./workouts/{id}.get'));
        $this->assertNotNull($response->json('paths./profile/preferences.patch'));

        $this->assertSame(
            ['email', 'password'],
            $response->json('paths./auth/login.post.requestBody.content.application/json.schema.required')
        );

        $this->assertSame(
            'integer',
            $response->json('paths./workouts/{id}/complete.post.requestBody.content.application/json.schema.properties.duration_minutes.type')
        );

        $this->assertSame(
            'object',
            $response->json('paths./profile.patch.requestBody.content.application/json.schema.properties.notifications.type')
        );

        $this->assertSame(
            'boolean',
            $response->json('paths./messages/mute.post.requestBody.content.application/json.schema.properties.mute.type')
        );

        $this->assertSame(
            '#/components/schemas/StudentProfile',
            $response->json('paths./profile.get.responses.200.content.application/json.schema.properties.data.$ref')
        );

        $this->assertSame(
            ['number', 'null'],
            $response->json('components.schemas.StudentProfile.properties.imc.type')
        );

        $this->assertNotNull(
            $response->json('paths./home.get.responses.200.content.application/json.schema.properties.data.properties.student')
        );

        $this->assertSame(
            'integer',
            $response->json('paths./weight/change.get.responses.200.content.application/json.schema.properties.data.oneOf.0.properties.period_days.type')
        );

        $this->assertSame(
            [
                'awarded',
                'already_awarded_in_session',
                'exercise_identifier_missing',
                'exercise_not_found',
            ],
            $response->json('components.schemas.GamificationEvent.properties.reason.enum')
        );

        $this->assertSame(
            'integer',
            $response->json('components.schemas.GamificationEvent.properties.awarded_xp.type')
        );

        $this->assertSame(
            'integer',
            $response->json('components.schemas.GamificationEvent.properties.xp.type')
        );

        $this->assertSame(
            'integer',
            $response->json('components.schemas.GamificationEvent.properties.xp_gained.type')
        );

        $this->assertSame(
            'integer',
            $response->json('components.schemas.GamificationPatchProfile.properties.current_xp.type')
        );

        $this->assertSame(
            'integer',
            $response->json('components.schemas.GamificationPatchProfile.properties.total_xp.type')
        );

        $this->assertSame(
            '#/components/schemas/WorkoutExercise',
            $response->json('components.schemas.Workout.properties.exercises.items.$ref')
        );

        $this->assertSame(
            'array',
            $response->json('components.schemas.WorkoutExercise.properties.images.type')
        );

        $this->assertSame(
            '#/components/schemas/WorkoutExerciseImage',
            $response->json('components.schemas.WorkoutExercise.properties.images.items.$ref')
        );

        $this->assertSame(
            'Si viene null en exercises_data, la API intenta hidratarla desde la entidad Exercise.',
            $response->json('components.schemas.WorkoutExercise.properties.description.description')
        );

        $this->assertSame(
            'awarded',
            $response->json('paths./workouts/{id}.patch.responses.200.content.application/json.examples.awarded_xp.value.gamification.events.0.reason')
        );

        $this->assertSame(
            15,
            $response->json('paths./workouts/{id}.patch.responses.200.content.application/json.examples.awarded_xp.value.gamification.events.0.awarded_xp')
        );

        $this->assertSame(
            'already_awarded_in_session',
            $response->json('paths./workouts/{id}.patch.responses.200.content.application/json.examples.already_awarded_in_session.value.gamification.events.0.reason')
        );

        $this->assertSame(
            0,
            $response->json('paths./workouts/{id}.patch.responses.200.content.application/json.examples.already_awarded_in_session.value.gamification.events.0.xp_gained')
        );

        $this->assertSame(
            '#/components/schemas/Message',
            $response->json('paths./messages/send.post.responses.200.content.application/json.schema.$ref')
        );

        $this->assertSame(
            '#/components/schemas/Message',
            $response->json('paths./messages/send.post.responses.201.content.application/json.schema.$ref')
        );

        $this->assertSame(
            ['expo_push_token', 'platform'],
            $response->json('paths./devices/register.post.requestBody.content.application/json.schema.required')
        );

        $this->assertSame(
            ['ios', 'android', 'web'],
            $response->json('paths./devices/register.post.requestBody.content.application/json.schema.properties.platform.enum')
        );

        $this->assertSame(
            '#/components/schemas/DeviceRegistration',
            $response->json('paths./devices/register.post.responses.200.content.application/json.schema.$ref')
        );

        $this->assertSame(
            '#/components/schemas/DeviceRegistration',
            $response->json('paths./devices/register.post.responses.201.content.application/json.schema.$ref')
        );

        $this->assertSame(
            '#/components/schemas/WeightEntry',
            $response->json('paths./weight.post.responses.200.content.application/json.schema.properties.data.$ref')
        );

        $this->assertSame(
            '#/components/schemas/WeightEntry',
            $response->json('paths./weight.post.responses.201.content.application/json.schema.properties.data.$ref')
        );

        $response->assertJsonMissingPath('branding');
        $response->assertJsonMissingPath('trainer');
    }
}
