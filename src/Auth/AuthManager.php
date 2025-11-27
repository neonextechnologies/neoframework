<?php

namespace NeoPhp\Auth;

use NeoPhp\Auth\Guards\GuardInterface;
use NeoPhp\Auth\Guards\SessionGuard;
use NeoPhp\Auth\Guards\TokenGuard;

/**
 * Auth Manager
 * 
 * Manages multiple authentication guards
 */
class AuthManager
{
    protected array $guards = [];
    protected string $defaultGuard = 'web';
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get authentication guard
     */
    public function guard(?string $name = null): GuardInterface
    {
        $name = $name ?? $this->defaultGuard;

        if (!isset($this->guards[$name])) {
            $this->guards[$name] = $this->resolve($name);
        }

        return $this->guards[$name];
    }

    /**
     * Resolve guard instance
     */
    protected function resolve(string $name): GuardInterface
    {
        $config = $this->config['guards'][$name] ?? [];
        $driver = $config['driver'] ?? 'session';

        return match($driver) {
            'session' => $this->createSessionGuard($name, $config),
            'token' => $this->createTokenGuard($name, $config),
            default => throw new \Exception("Unsupported guard driver: {$driver}")
        };
    }

    /**
     * Create session guard
     */
    protected function createSessionGuard(string $name, array $config): SessionGuard
    {
        $session = app('session');
        $provider = $this->createProvider($config['provider'] ?? 'users');
        $rememberToken = app(RememberToken::class);

        $guard = new SessionGuard($session, $provider, $rememberToken);
        $guard->setName($name);

        return $guard;
    }

    /**
     * Create token guard
     */
    protected function createTokenGuard(string $name, array $config): TokenGuard
    {
        $provider = $this->createProvider($config['provider'] ?? 'users');
        return new TokenGuard($provider);
    }

    /**
     * Create user provider
     */
    protected function createProvider(string $name): object
    {
        $config = $this->config['providers'][$name] ?? [];
        $model = $config['model'] ?? \App\Models\User::class;

        return new DatabaseUserProvider($model);
    }

    /**
     * Set default guard
     */
    public function setDefaultGuard(string $guard): self
    {
        $this->defaultGuard = $guard;
        return $this;
    }

    /**
     * Get default guard name
     */
    public function getDefaultGuard(): string
    {
        return $this->defaultGuard;
    }

    /**
     * Magic method to forward calls to default guard
     */
    public function __call(string $method, array $parameters)
    {
        return $this->guard()->{$method}(...$parameters);
    }
}
