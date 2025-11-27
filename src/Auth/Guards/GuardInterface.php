<?php

namespace NeoPhp\Auth\Guards;

/**
 * Guard Interface
 * 
 * All authentication guards must implement this interface
 */
interface GuardInterface
{
    /**
     * Determine if the current user is authenticated
     */
    public function check(): bool;

    /**
     * Determine if the current user is a guest
     */
    public function guest(): bool;

    /**
     * Get the currently authenticated user
     */
    public function user(): ?object;

    /**
     * Get the ID for the currently authenticated user
     */
    public function id(): ?int;

    /**
     * Validate a user's credentials
     */
    public function validate(array $credentials = []): bool;

    /**
     * Set the current user
     */
    public function setUser(object $user): void;
}
