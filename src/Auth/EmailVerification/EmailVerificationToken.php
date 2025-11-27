<?php

namespace NeoPhp\Auth\EmailVerification;

use NeoPhp\Database\Database;

/**
 * Email Verification Token Manager
 */
class EmailVerificationToken
{
    protected Database $db;
    protected string $table = 'email_verification_tokens';
    protected int $expires = 86400; // 24 hours

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Create verification token for user
     */
    public function create(int $userId, string $email): string
    {
        $token = $this->generateToken();
        $hashedToken = hash('sha256', $token);

        // Delete any existing tokens
        $this->deleteExisting($userId);

        // Create new token
        $this->db->insert($this->table, [
            'user_id' => $userId,
            'email' => $email,
            'token' => $hashedToken,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $token;
    }

    /**
     * Verify token
     */
    public function verify(int $userId, string $token): bool
    {
        $record = $this->getToken($userId);

        if (!$record) {
            return false;
        }

        // Check expiration
        if ($this->tokenExpired($record['created_at'])) {
            $this->delete($userId);
            return false;
        }

        $hashedToken = hash('sha256', $token);
        return hash_equals($record['token'], $hashedToken);
    }

    /**
     * Get token record
     */
    protected function getToken(int $userId): ?array
    {
        $results = $this->db->query(
            "SELECT * FROM {$this->table} WHERE user_id = ? LIMIT 1",
            [$userId]
        );

        return $results[0] ?? null;
    }

    /**
     * Check if token expired
     */
    protected function tokenExpired(string $createdAt): bool
    {
        $created = strtotime($createdAt);
        return time() - $created > $this->expires;
    }

    /**
     * Delete token for user
     */
    public function delete(int $userId): bool
    {
        return $this->db->delete(
            $this->table,
            'user_id = ?',
            [$userId]
        ) > 0;
    }

    /**
     * Delete existing tokens
     */
    protected function deleteExisting(int $userId): void
    {
        $this->delete($userId);
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
}
