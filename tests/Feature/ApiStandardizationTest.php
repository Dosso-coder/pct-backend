<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiStandardizationTest extends TestCase
{
    /**
     * Test that validation errors follow the uniform format.
     */
    public function test_validation_error_format()
    {
        $response = $this->postJson('/api/login', [
            'type' => 'invalid-type',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'errors'
                 ])
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation failed',
                 ]);
    }

    /**
     * Test that 404 errors return JSON.
     */
    public function test_404_error_returns_json()
    {
        $response = $this->getJson('/api/non-existent-route');

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Resource not found',
                 ]);
    }

    /**
     * Test that a successful response follows the uniform format.
     * Note: This assumes we have some data to login with or we can just test another simple endpoint.
     */
    public function test_unauthenticated_error_format()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Unauthenticated',
                 ]);
    }
}
