<?php

namespace NeoPhp\Auth\Access;

use Closure;

/**
 * Gate - Authorization Gate System
 * 
 * Provides a simple way to authorize user actions
 */
class Gate
{
    protected static array $abilities = [];
    protected static array $policies = [];
    protected static ?object $user = null;

    /**
     * Define a new ability
     */
    public static function define(string $ability, Closure|string $callback): void
    {
        static::$abilities[$ability] = $callback;
    }

    /**
     * Register a policy for a class
     */
    public static function policy(string $class, string $policy): void
    {
        static::$policies[$class] = $policy;
    }

    /**
     * Check if user can perform ability
     */
    public static function allows(string $ability, mixed $arguments = []): bool
    {
        // Convert single argument to array
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }

        // Check if ability defined
        if (!isset(static::$abilities[$ability])) {
            return false;
        }

        $callback = static::$abilities[$ability];

        // If callback is string (Policy method)
        if (is_string($callback)) {
            return static::callPolicyMethod($callback, $arguments);
        }

        // If callback is Closure
        if ($callback instanceof Closure) {
            return $callback(static::$user, ...$arguments);
        }

        return false;
    }

    /**
     * Check if user cannot perform ability
     */
    public static function denies(string $ability, mixed $arguments = []): bool
    {
        return !static::allows($ability, $arguments);
    }

    /**
     * Authorize an ability or throw exception
     */
    public static function authorize(string $ability, mixed $arguments = []): void
    {
        if (static::denies($ability, $arguments)) {
            throw new AuthorizationException("User is not authorized to {$ability}");
        }
    }

    /**
     * Check ability for specific model
     */
    public static function forUser(?object $user): static
    {
        static::$user = $user;
        return new static();
    }

    /**
     * Check if policy exists for model
     */
    public static function getPolicyFor(object $model): ?string
    {
        $class = get_class($model);
        return static::$policies[$class] ?? null;
    }

    /**
     * Call policy method
     */
    protected static function callPolicyMethod(string $method, array $arguments): bool
    {
        // Parse Policy@method format
        if (str_contains($method, '@')) {
            [$policyClass, $policyMethod] = explode('@', $method, 2);
            
            $policy = new $policyClass();
            
            if (method_exists($policy, $policyMethod)) {
                return $policy->$policyMethod(static::$user, ...$arguments);
            }
        }

        return false;
    }

    /**
     * Check ability through model policy
     */
    public static function check(object $model, string $ability): bool
    {
        $policyClass = static::getPolicyFor($model);

        if (!$policyClass) {
            return false;
        }

        $policy = new $policyClass();

        if (!method_exists($policy, $ability)) {
            return false;
        }

        return $policy->$ability(static::$user, $model);
    }

    /**
     * Get all defined abilities
     */
    public static function abilities(): array
    {
        return static::$abilities;
    }

    /**
     * Get all registered policies
     */
    public static function policies(): array
    {
        return static::$policies;
    }

    /**
     * Clear all gates and policies
     */
    public static function clear(): void
    {
        static::$abilities = [];
        static::$policies = [];
        static::$user = null;
    }

    /**
     * Set current user
     */
    public static function setUser(?object $user): void
    {
        static::$user = $user;
    }

    /**
     * Get current user
     */
    public static function user(): ?object
    {
        return static::$user;
    }

    /**
     * Check multiple abilities (any)
     */
    public static function any(array $abilities, mixed $arguments = []): bool
    {
        foreach ($abilities as $ability) {
            if (static::allows($ability, $arguments)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check multiple abilities (all)
     */
    public static function all(array $abilities, mixed $arguments = []): bool
    {
        foreach ($abilities as $ability) {
            if (static::denies($ability, $arguments)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Before callback - runs before all checks
     */
    protected static ?Closure $beforeCallback = null;

    public static function before(Closure $callback): void
    {
        static::$beforeCallback = $callback;
    }

    /**
     * After callback - runs after all checks
     */
    protected static ?Closure $afterCallback = null;

    public static function after(Closure $callback): void
    {
        static::$afterCallback = $callback;
    }
}
