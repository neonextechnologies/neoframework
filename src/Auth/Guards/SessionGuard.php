<?php

namespace NeoPhp\Auth\Guards;

use NeoPhp\Session\Session;
use NeoPhp\Auth\RememberToken;

/**
 * Session Guard
 * 
 * Web-based session authentication guard
 */
class SessionGuard implements GuardInterface
{
    protected Session $session;
    protected ?RememberToken $rememberToken;
    protected ?object $user = null;
    protected string $name = 'web';
    protected object $provider;

    public function __construct(Session $session, object $provider, ?RememberToken $rememberToken = null)
    {
        $this->session = $session;
        $this->provider = $provider;
        $this->rememberToken = $rememberToken;
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

        // Check session
        $id = $this->session->get($this->getName());

        if ($id) {
            $this->user = $this->provider->retrieveById($id);
        }

        // Check remember token if no session
        if (is_null($this->user) && $this->rememberToken) {
            $this->user = $this->userFromRememberToken();
        }

        return $this->user;
    }

    /**
     * Get user from remember token
     */
    protected function userFromRememberToken(): ?object
    {
        $token = $_COOKIE['remember_token'] ?? null;

        if (!$token) {
            return null;
        }

        $userId = $this->rememberToken->verify($token);

        if (!$userId) {
            return null;
        }

        $user = $this->provider->retrieveById($userId);

        if ($user) {
            // Refresh session
            $this->login($user);
        }

        return $user;
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
        return !is_null($this->provider->retrieveByCredentials($credentials));
    }

    /**
     * Attempt to authenticate a user
     */
    public function attempt(array $credentials, bool $remember = false): bool
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if (!$user) {
            return false;
        }

        if (!$this->provider->validateCredentials($user, $credentials)) {
            return false;
        }

        $this->login($user, $remember);

        return true;
    }

    /**
     * Log a user into the application
     */
    public function login(object $user, bool $remember = false): void
    {
        $this->session->set($this->getName(), $user->id);
        $this->session->regenerate();

        if ($remember && $this->rememberToken) {
            $token = $this->rememberToken->create($user->id);
            setcookie('remember_token', $token, time() + 2592000, '/'); // 30 days
        }

        $this->user = $user;
    }

    /**
     * Log the user out of the application
     */
    public function logout(): void
    {
        $userId = $this->id();

        $this->session->forget($this->getName());
        $this->session->regenerate();

        // Clear remember token
        if ($userId && $this->rememberToken) {
            $this->rememberToken->deleteAllForUser($userId);
        }

        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        $this->user = null;
    }

    /**
     * Set the current user
     */
    public function setUser(object $user): void
    {
        $this->user = $user;
    }

    /**
     * Get the name of the guard
     */
    public function getName(): string
    {
        return 'auth_' . $this->name;
    }

    /**
     * Set the name of the guard
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
