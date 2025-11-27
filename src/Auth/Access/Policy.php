<?php

namespace NeoPhp\Auth\Access;

/**
 * Base Policy Class
 * 
 * Extend this class to create model-specific authorization policies
 */
abstract class Policy
{
    /**
     * Determine if the given ability should be granted for the current user
     * This method is called before any other policy methods
     * 
     * @param object $user
     * @return bool|null
     */
    public function before(object $user): ?bool
    {
        // Check if user is admin
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return null; // Continue to specific policy methods
    }

    /**
     * Helper method to check if user owns the model
     */
    protected function owns(object $user, object $model): bool
    {
        if (!isset($model->user_id) || !isset($user->id)) {
            return false;
        }

        return $model->user_id === $user->id;
    }

    /**
     * Helper method to check if user has role
     */
    protected function hasRole(object $user, string $role): bool
    {
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole($role);
        }

        return false;
    }

    /**
     * Helper method to check if user has permission
     */
    protected function hasPermission(object $user, string $permission): bool
    {
        if (method_exists($user, 'can')) {
            return $user->can($permission);
        }

        return false;
    }
}
