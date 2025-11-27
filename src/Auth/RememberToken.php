<?php

namespace NeoPhp\Auth;

use NeoPhp\Database\Database;

/**
 * Remember Me Token Manager
 * 
 * Manages "remember me" tokens for persistent authentication
 */
class RememberToken
{
    protected Database $db;
    protected string $table = 'remember_tokens';
    protected int $expires = 2592000; // 30 days

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Create remember token for user
     */
    public function create(int $userId): string
    {
        $token = $this->generateToken();
        $hashedToken = hash('sha256', $token);

        // Store token
        $this->db->insert($this->table, [
            'user_id' => $userId,
            'token' => $hashedToken,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', time() + $this->expires)
        ]);

        // Return userId:token format
        return $userId . ':' . $token;
    }

    /**
     * Verify remember token
     */
    public function verify(string $rememberToken): ?int
    {
        // Parse token format: userId:token
        $parts = explode(':', $rememberToken, 2);

        if (count($parts) !== 2) {
            return null;
        }

        [$userId, $token] = $parts;
        $hashedToken = hash('sha256', $token);

        // Get token from database
        $record = $this->getToken((int)$userId, $hashedToken);

        if (!$record) {
            return null;
        }

        // Check expiration
        if ($this->tokenExpired($record['expires_at'])) {
            $this->delete((int)$userId, $hashedToken);
            return null;
        }

        return (int)$userId;
    }

    /**
     * Get token record
     */
    protected function getToken(int $userId, string $hashedToken): ?array
    {
        $results = $this->db->query(
            "SELECT * FROM {$this->table} WHERE user_id = ? AND token = ? LIMIT 1",
            [$userId, $hashedToken]
        );

        return $results[0] ?? null;
    }

    /**
     * Check if token expired
     */
    protected function tokenExpired(string $expiresAt): bool
    {
        return strtotime($expiresAt) < time();
    }

    /**
     * Delete specific token
     */
    public function delete(int $userId, string $hashedToken): bool
    {
        return $this->db->delete(
            $this->table,
            'user_id = ? AND token = ?',
            [$userId, $hashedToken]
        ) > 0;
    }

    /**
     * Delete all tokens for user
     */
    public function deleteAllForUser(int $userId): int
    {
        return $this->db->delete(
            $this->table,
            'user_id = ?',
            [$userId]
        );
    }

    /**
     * Delete expired tokens
     */
    public function deleteExpired(): int
    {
        return $this->db->delete(
            $this->table,
            'expires_at < ?',
            [date('Y-m-d H:i:s')]
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
}
