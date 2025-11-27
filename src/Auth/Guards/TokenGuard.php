<?php

namespace NeoPhp\Auth\Guards;

/**
 * Token Guard
 * 
 * Stateless API token authentication guard
 */
class TokenGuard implements GuardInterface
{
    protected ?object $user = null;
    protected object $provider;
    protected string $inputKey = 'api_token';
    protected string $storageKey = 'api_token';

    public function __construct(object $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Determine if the current user is authenticated
     */
    public function check(): bool
    {
        return !is_null($this->user());
    }

    /**
     * Determine if the current user is a guest
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user
     */
    public function user(): ?object
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $token = $this->getTokenForRequest();

        if (!$token) {
            return null;
        }

        $this->user = $this->provider->retrieveByToken($token);

        return $this->user;
    }

    /**
     * Get the token from the request
     */
    protected function getTokenForRequest(): ?string
    {
        // Check Authorization header
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        // Check query parameter
        if (isset($_GET[$this->inputKey])) {
            return $_GET[$this->inputKey];
        }

        // Check POST data
        if (isset($_POST[$this->inputKey])) {
            return $_POST[$this->inputKey];
        }

        return null;
    }

    /**
     * Get the ID for the currently authenticated user
     */
    public function id(): ?int
    {
        $user = $this->user();
        return $user ? $user->id : null;
    }

    /**
     * Validate a user's credentials
     */
    public function validate(array $credentials = []): bool
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        return !is_null($this->provider->retrieveByToken($credentials[$this->inputKey]));
    }

    /**
     * Set the current user
     */
    public function setUser(object $user): void
    {
        $this->user = $user;
    }

    /**
     * Set the input key
     */
    public function setInputKey(string $key): self
    {
        $this->inputKey = $key;
        return $this;
    }

    /**
     * Set the storage key
     */
    public function setStorageKey(string $key): self
    {
        $this->storageKey = $key;
        return $this;
    }
}
