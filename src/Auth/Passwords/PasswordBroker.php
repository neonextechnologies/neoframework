<?php

namespace NeoPhp\Auth\Passwords;

use NeoPhp\Database\Database;
use NeoPhp\Mail\Mailer;

/**
 * Password Broker
 * 
 * Handles password reset workflow
 */
class PasswordBroker
{
    protected Database $db;
    protected Mailer $mailer;
    protected PasswordResetToken $tokens;
    protected string $userTable = 'users';
    protected string $emailColumn = 'email';
    protected string $passwordColumn = 'password';

    // Response codes
    const PASSWORD_RESET = 'passwords.reset';
    const INVALID_USER = 'passwords.user';
    const INVALID_TOKEN = 'passwords.token';
    const THROTTLED = 'passwords.throttled';

    public function __construct(Database $db, Mailer $mailer, PasswordResetToken $tokens)
    {
        $this->db = $db;
        $this->mailer = $mailer;
        $this->tokens = $tokens;
    }

    /**
     * Send password reset link to user
     */
    public function sendResetLink(string $email): string
    {
        // Check if user exists
        $user = $this->getUser($email);

        if (!$user) {
            return static::INVALID_USER;
        }

        // Create reset token
        $token = $this->tokens->create($email);

        // Send email
        $this->sendResetEmail($email, $token);

        return static::PASSWORD_RESET;
    }

    /**
     * Reset user password
     */
    public function reset(array $credentials): string
    {
        $email = $credentials['email'] ?? '';
        $password = $credentials['password'] ?? '';
        $token = $credentials['token'] ?? '';

        // Validate user
        $user = $this->getUser($email);

        if (!$user) {
            return static::INVALID_USER;
        }

        // Validate token
        if (!$this->tokens->exists($email, $token)) {
            return static::INVALID_TOKEN;
        }

        // Update password
        $this->updatePassword($email, $password);

        // Delete token
        $this->tokens->delete($email);

        return static::PASSWORD_RESET;
    }

    /**
     * Get user by email
     */
    protected function getUser(string $email): ?array
    {
        $results = $this->db->query(
            "SELECT * FROM {$this->userTable} WHERE {$this->emailColumn} = ? LIMIT 1",
            [$email]
        );

        return $results[0] ?? null;
    }

    /**
     * Update user password
     */
    protected function updatePassword(string $email, string $password): void
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $this->db->update(
            $this->userTable,
            [$this->passwordColumn => $hashedPassword],
            "{$this->emailColumn} = ?",
            [$email]
        );
    }

    /**
     * Send password reset email
     */
    protected function sendResetEmail(string $email, string $token): void
    {
        $resetUrl = $this->getResetUrl($token, $email);

        $subject = 'Reset Password';
        $body = $this->getEmailBody($resetUrl);

        $this->mailer->to($email)
            ->subject($subject)
            ->html($body)
            ->send();
    }

    /**
     * Get password reset URL
     */
    protected function getResetUrl(string $token, string $email): string
    {
        $baseUrl = config('app.url', 'http://localhost');
        return "{$baseUrl}/password/reset?token={$token}&email=" . urlencode($email);
    }

    /**
     * Get email body
     */
    protected function getEmailBody(string $resetUrl): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button { display: inline-block; padding: 12px 24px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <p>You are receiving this email because we received a password reset request for your account.</p>
        <p>
            <a href="{$resetUrl}" class="button">Reset Password</a>
        </p>
        <p>If you did not request a password reset, no further action is required.</p>
        <p>This password reset link will expire in 60 minutes.</p>
        <div class="footer">
            <p>If you're having trouble clicking the button, copy and paste the URL below into your web browser:</p>
            <p>{$resetUrl}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Set user table name
     */
    public function setUserTable(string $table): self
    {
        $this->userTable = $table;
        return $this;
    }

    /**
     * Set email column name
     */
    public function setEmailColumn(string $column): self
    {
        $this->emailColumn = $column;
        return $this;
    }

    /**
     * Set password column name
     */
    public function setPasswordColumn(string $column): self
    {
        $this->passwordColumn = $column;
        return $this;
    }
}
