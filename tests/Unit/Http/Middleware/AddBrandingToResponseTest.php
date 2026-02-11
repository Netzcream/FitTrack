<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\Api\AddBrandingToResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

class AddBrandingToResponseTest extends TestCase
{
    public function test_it_adds_branding_and_trainer_data_to_json_response(): void
    {
        $middleware = new AddBrandingToResponse();
        $request = Request::create('/api/test', 'GET');

        $response = $middleware->handle($request, function () {
            return response()->json([
                'data' => ['ok' => true],
            ]);
        });

        $payload = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('branding', $payload);
        $this->assertArrayHasKey('trainer', $payload);
        $this->assertArrayHasKey('favicon_url', $payload['branding']);
        $this->assertArrayHasKey('favicon_url', $payload['trainer']);
        $this->assertArrayHasKey('contact', $payload['trainer']);
        $this->assertArrayHasKey('primary_color', $payload['trainer']);
    }

    public function test_it_does_not_modify_non_json_response(): void
    {
        $middleware = new AddBrandingToResponse();
        $request = Request::create('/api/test', 'GET');

        $response = $middleware->handle($request, function () {
            return response('ok', 200, [
                'Content-Type' => 'text/plain',
            ]);
        });

        $this->assertSame('ok', $response->getContent());
    }

    public function test_it_skips_branding_for_api_docs_route(): void
    {
        $middleware = new AddBrandingToResponse();
        $request = Request::create('/api/docs', 'GET');

        $response = $middleware->handle($request, function () {
            return response()->json([
                'openapi' => '3.0.3',
            ]);
        });

        $payload = json_decode($response->getContent(), true);

        $this->assertSame('3.0.3', $payload['openapi'] ?? null);
        $this->assertArrayNotHasKey('branding', $payload);
        $this->assertArrayNotHasKey('trainer', $payload);
    }
}
