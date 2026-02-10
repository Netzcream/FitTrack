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

        $response->assertJsonMissingPath('branding');
        $response->assertJsonMissingPath('trainer');
    }
}
