<?php

namespace NeoPhp\Auth\Passwords;

use NeoPhp\Database\Database;

/**
 * Password Reset Token Manager
 * 
 * Manages password reset tokens for users
 */
class PasswordResetToken
{
    protected Database $db;
    protected string $table = 'password_reset_tokens';
    protected int $expires = 3600; // 1 hour in seconds

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new password reset token
     */
    public function create(string $email): string
    {
        $token = $this->generateToken();
        $hashedToken = password_hash($token, PASSWORD_BCRYPT);

        // Delete any existing tokens
        $this->deleteExisting($email);

        // Create new token
        $this->db->insert($this->table, [
            'email' => $email,
            'token' => $hashedToken,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $token;
    }

    /**
     * Check if token exists and is valid
     */
    public function exists(string $email, string $token): bool
    {
        $record = $this->getToken($email);

        if (!$record) {
            return false;
        }

        // Check if token has expired
        if ($this->tokenExpired($record['created_at'])) {
            $this->delete($email);
            return false;
        }

        return password_verify($token, $record['token']);
    }

    /**
     * Get token record for email
     */
    protected function getToken(string $email): ?array
    {
        $results = $this->db->query(
            "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1",
            [$email]
        );

        return $results[0] ?? null;
    }

    /**
     * Check if token has expired
     */
    protected function tokenExpired(string $createdAt): bool
    {
        $created = strtotime($createdAt);
        return time() - $created > $this->expires;
    }

    /**
     * Delete token for email
     */
    public function delete(string $email): bool
    {
        return $this->db->delete(
            $this->table,
            'email = ?',
            [$email]
        ) > 0;
    }

    /**
     * Delete existing tokens for email
     */
    protected function deleteExisting(string $email): void
    {
        $this->delete($email);
    }

    /**
     * Delete expired tokens
     */
    public function deleteExpired(): int
    {
        $expiredTime = date('Y-m-d H:i:s', time() - $this->expires);

        return $this->db->delete(
            $this->table,
            'created_at < ?',
            [$expiredTime]
        );
    }

    /**
     * Generate random token
     */
    protected function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Set token expiration time (in seconds)
     */
    public function setExpires(int $seconds): self
    {
        $this->expires = $seconds;
        return $this;
    }

    /**
     * Get token expiration time
     */
    public function getExpires(): int
    {
        return $this->expires;
    }
}
