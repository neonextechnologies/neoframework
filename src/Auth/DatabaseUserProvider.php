<?php

namespace NeoPhp\Auth;

/**
 * Database User Provider
 * 
 * Provides user retrieval from database
 */
class DatabaseUserProvider
{
    protected string $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    /**
     * Retrieve a user by their unique identifier
     */
    public function retrieveById(int $id): ?object
    {
        return $this->model::find($id);
    }

    /**
     * Retrieve a user by their credentials
     */
    public function retrieveByCredentials(array $credentials): ?object
    {
        $query = $this->model::query();

        foreach ($credentials as $key => $value) {
            if ($key !== 'password') {
                $query->where($key, '=', $value);
            }
        }

        return $query->first();
    }

    /**
     * Retrieve a user by API token
     */
    public function retrieveByToken(string $token): ?object
    {
        return $this->model::where('api_token', '=', $token)->first();
    }

    /**
     * Validate a user against credentials
     */
    public function validateCredentials(object $user, array $credentials): bool
    {
        $password = $credentials['password'] ?? '';

        if (!isset($user->password)) {
            return false;
        }

        return password_verify($password, $user->password);
    }
}
