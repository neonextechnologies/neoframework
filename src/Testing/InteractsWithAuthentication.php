<?php

namespace NeoPhp\Testing;

/**
 * Interacts With Authentication Trait
 * 
 * Provides authentication testing helpers
 */
trait InteractsWithAuthentication
{
    /**
     * Set the currently logged in user
     */
    protected function actingAs(object $user, ?string $guard = null): self
    {
        auth($guard)->setUser($user);
        return $this;
    }

    /**
     * Assert that the user is authenticated
     */
    protected function assertAuthenticated(?string $guard = null): void
    {
        $this->assertTrue(
            auth($guard)->check(),
            'The user is not authenticated'
        );
    }

    /**
     * Assert that the user is not authenticated
     */
    protected function assertGuest(?string $guard = null): void
    {
        $this->assertFalse(
            auth($guard)->check(),
            'The user is authenticated'
        );
    }

    /**
     * Assert that the user is authenticated as the given user
     */
    protected function assertAuthenticatedAs(object $user, ?string $guard = null): void
    {
        $authenticatedUser = auth($guard)->user();

        $this->assertNotNull($authenticatedUser, 'The user is not authenticated');
        
        $this->assertEquals(
            $user->id,
            $authenticatedUser->id,
            'The authenticated user is not who was expected'
        );
    }

    /**
     * Assert that the given credentials are valid
     */
    protected function assertCredentials(array $credentials, ?string $guard = null): void
    {
        $this->assertTrue(
            auth($guard)->validate($credentials),
            'The given credentials are invalid'
        );
    }

    /**
     * Assert that the given credentials are invalid
     */
    protected function assertInvalidCredentials(array $credentials, ?string $guard = null): void
    {
        $this->assertFalse(
            auth($guard)->validate($credentials),
            'The given credentials are valid'
        );
    }
}
