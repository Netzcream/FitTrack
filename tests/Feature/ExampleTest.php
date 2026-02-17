<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response(): void
    {
        $response = $this->withServerVariables([
            'HTTP_HOST' => config('tenancy.central_domains')[0] ?? 'localhost',
        ])->get('/');

        $response->assertStatus(200);
    }
}
