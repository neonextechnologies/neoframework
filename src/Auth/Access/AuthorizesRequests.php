<?php

namespace NeoPhp\Auth\Access;

/**
 * Authorizes Requests Trait
 * 
 * Add this trait to controllers to enable authorization methods
 */
trait AuthorizesRequests
{
    /**
     * Authorize a given action for the current user
     * 
     * @throws AuthorizationException
     */
    protected function authorize(string $ability, mixed $arguments = []): void
    {
        $user = $this->user();

        Gate::setUser($user);
        Gate::authorize($ability, $arguments);
    }

    /**
     * Authorize an action for a model
     * 
     * @throws AuthorizationException
     */
    protected function authorizeResource(string $ability, object $model): void
    {
        $user = $this->user();

        Gate::setUser($user);

        if (!Gate::check($model, $ability)) {
            throw new AuthorizationException("User is not authorized to {$ability} this resource");
        }
    }

    /**
     * Get the current authenticated user
     */
    protected function user(): ?object
    {
        return auth()->user();
    }

    /**
     * Authorize ability or return boolean
     */
    protected function can(string $ability, mixed $arguments = []): bool
    {
        $user = $this->user();

        Gate::setUser($user);
        return Gate::allows($ability, $arguments);
    }

    /**
     * Check if user cannot perform ability
     */
    protected function cannot(string $ability, mixed $arguments = []): bool
    {
        return !$this->can($ability, $arguments);
    }

    /**
     * Authorize ability for multiple resources
     */
    protected function authorizeForAll(string $ability, array $models): void
    {
        foreach ($models as $model) {
            $this->authorizeResource($ability, $model);
        }
    }
}
