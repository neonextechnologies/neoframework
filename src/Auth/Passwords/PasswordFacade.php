<?php

namespace NeoPhp\Auth\Passwords;

/**
 * Password Facade
 * 
 * Provides convenient access to password reset functionality
 */
class PasswordFacade
{
    protected static ?PasswordBroker $broker = null;

    /**
     * Send password reset link
     */
    public static function sendResetLink(string $email): string
    {
        return static::broker()->sendResetLink($email);
    }

    /**
     * Reset password
     */
    public static function reset(array $credentials): string
    {
        return static::broker()->reset($credentials);
    }

    /**
     * Get password broker instance
     */
    public static function broker(): PasswordBroker
    {
        if (!static::$broker) {
            static::$broker = app(PasswordBroker::class);
        }

        return static::$broker;
    }

    /**
     * Set password broker instance
     */
    public static function setBroker(PasswordBroker $broker): void
    {
        static::$broker = $broker;
    }
}
