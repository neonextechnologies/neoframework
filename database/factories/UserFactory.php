<?php

namespace Database\Factories;

use App\Models\User;
use NeoPhp\Testing\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected string $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->randomString(10),
            'email' => $this->randomEmail(),
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'created_at' => $this->randomDate('-1 year', 'now'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state([
            'is_admin' => true,
            'role' => 'admin',
        ]);
    }

    /**
     * Indicate that the user is verified.
     */
    public function verified(): static
    {
        return $this->state([
            'email_verified_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Indicate that the user is unverified.
     */
    public function unverified(): static
    {
        return $this->state([
            'email_verified_at' => null,
        ]);
    }
}
