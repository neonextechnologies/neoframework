<?php

namespace NeoPhp\Database\Concerns;

use Closure;

/**
 * Has Global Scopes Trait
 * 
 * Provides global and local scope functionality
 */
trait HasGlobalScopes
{
    /**
     * The array of global scopes on the model
     */
    protected static array $globalScopes = [];

    /**
     * Register a new global scope on the model
     */
    public static function addGlobalScope($scope, ?Closure $implementation = null): void
    {
        if (is_string($scope) && !is_null($implementation)) {
            static::$globalScopes[static::class][$scope] = $implementation;
        } elseif ($scope instanceof Closure) {
            static::$globalScopes[static::class][spl_object_hash($scope)] = $scope;
        } elseif (is_object($scope)) {
            static::$globalScopes[static::class][get_class($scope)] = $scope;
        }
    }

    /**
     * Determine if a model has a global scope
     */
    public static function hasGlobalScope($scope): bool
    {
        return !is_null(static::getGlobalScope($scope));
    }

    /**
     * Get a global scope registered with the model
     */
    public static function getGlobalScope($scope)
    {
        if (is_string($scope)) {
            return static::$globalScopes[static::class][$scope] ?? null;
        }

        return static::$globalScopes[static::class][get_class($scope)] ?? null;
    }

    /**
     * Get the global scopes for this class instance
     */
    public function getGlobalScopes(): array
    {
        return static::$globalScopes[static::class] ?? [];
    }

    /**
     * Remove a registered global scope
     */
    public static function removeGlobalScope($scope): void
    {
        if (!is_string($scope)) {
            $scope = get_class($scope);
        }

        unset(static::$globalScopes[static::class][$scope]);
    }

    /**
     * Remove all of the global scopes from an entity
     */
    public static function clearGlobalScopes(): void
    {
        static::$globalScopes[static::class] = [];
    }

    /**
     * Apply all of the global scopes to a query builder
     */
    public function applyGlobalScopes($builder)
    {
        foreach ($this->getGlobalScopes() as $identifier => $scope) {
            if ($scope instanceof Closure) {
                $scope($builder, $this);
            } else {
                $scope->apply($builder, $this);
            }
        }

        return $builder;
    }

    /**
     * Register a local query scope
     * 
     * Local scopes are defined as methods prefixed with 'scope'
     * Example: scopeActive($query) can be called as ::active()
     */
    public function callScope(string $scope, array $parameters = [])
    {
        $method = 'scope' . ucfirst($scope);

        if (method_exists($this, $method)) {
            return $this->$method(...$parameters);
        }

        throw new \BadMethodCallException(
            sprintf('Call to undefined scope [%s] on model [%s].', $scope, static::class)
        );
    }

    /**
     * Apply the given scope on the current model
     */
    public static function __callStatic(string $method, array $parameters)
    {
        return (new static)->$method(...$parameters);
    }
}
