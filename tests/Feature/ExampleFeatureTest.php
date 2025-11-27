<?php

use NeoPhp\Testing\TestCase;
use App\Models\User;

class ExampleFeatureTest extends TestCase
{
    /**
     * Test home page loads successfully.
     */
    public function test_home_page_loads(): void
    {
        $response = $this->get('/');
        
        $this->assertResponseOk($response);
        $this->assertSee($response, 'Welcome');
    }

    /**
     * Test API endpoint returns JSON.
     */
    public function test_api_returns_json(): void
    {
        $response = $this->json('GET', '/api/users');
        
        $this->assertOk($response);
        $this->assertJson($response);
    }

    /**
     * Test authenticated user can access protected route.
     */
    public function test_authenticated_user_access(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $this->assertResponseOk($response);
        $this->assertAuthenticated();
    }

    /**
     * Test guest cannot access protected route.
     */
    public function test_guest_cannot_access_protected_route(): void
    {
        $response = $this->get('/dashboard');
        
        $this->assertStatus($response, 302); // Redirect
        $this->assertGuest();
    }

    /**
     * Test database operations.
     */
    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }
}
