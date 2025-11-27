<?php

namespace NeoPhp\Auth\EmailVerification;

/**
 * Must Verify Email Interface
 * 
 * Implement this interface in User model to require email verification
 */
interface MustVerifyEmail
{
    /**
     * Determine if the user has verified their email address
     */
    public function hasVerifiedEmail(): bool;

    /**
     * Mark the user's email as verified
     */
    public function markEmailAsVerified(): bool;

    /**
     * Send the email verification notification
     */
    public function sendEmailVerificationNotification(): void;

    /**
     * Get the email address that should be verified
     */
    public function getEmailForVerification(): string;
}
