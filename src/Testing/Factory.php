<?php

namespace NeoPhp\Testing;

/**
 * Factory
 * 
 * Generate fake data for testing
 */
abstract class Factory
{
    protected string $model;
    protected int $count = 1;
    protected array $attributes = [];
    protected array $states = [];

    /**
     * Define the model's default state
     */
    abstract public function definition(): array;

    /**
     * Create a new factory instance for the model
     */
    public static function new(): static
    {
        return new static();
    }

    /**
     * Set the number of models to create
     */
    public function count(int $count): self
    {
        $this->count = $count;
        return $this;
    }

    /**
     * Set the state to be applied to the model
     */
    public function state(string $state): self
    {
        if (method_exists($this, $state)) {
            $this->attributes = array_merge(
                $this->attributes,
                $this->$state()
            );
        }

        return $this;
    }

    /**
     * Set additional attributes
     */
    public function set(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Create a model instance
     */
    public function make(array $attributes = []): object
    {
        $attributes = array_merge(
            $this->definition(),
            $this->attributes,
            $attributes
        );

        return new $this->model($attributes);
    }

    /**
     * Create and persist a model
     */
    public function create(array $attributes = []): object
    {
        if ($this->count === 1) {
            $model = $this->make($attributes);
            $model->save();
            return $model;
        }

        $models = [];
        for ($i = 0; $i < $this->count; $i++) {
            $model = $this->make($attributes);
            $model->save();
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Create models for a relation
     */
    public function for(object $parent, string $relationship = null): self
    {
        $foreignKey = $this->guessForeignKey($parent, $relationship);
        
        $this->attributes[$foreignKey] = $parent->id;

        return $this;
    }

    /**
     * Guess the foreign key for the parent model
     */
    protected function guessForeignKey(object $parent, ?string $relationship): string
    {
        if ($relationship) {
            return $relationship . '_id';
        }

        $class = get_class($parent);
        $name = class_basename($class);

        return strtolower($name) . '_id';
    }

    /**
     * Generate a random string
     */
    protected function randomString(int $length = 10): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    /**
     * Generate a random number
     */
    protected function randomNumber(int $min = 0, int $max = 100): int
    {
        return rand($min, $max);
    }

    /**
     * Generate a random email
     */
    protected function randomEmail(): string
    {
        return strtolower($this->randomString(8)) . '@example.com';
    }

    /**
     * Generate a random boolean
     */
    protected function randomBoolean(): bool
    {
        return (bool) rand(0, 1);
    }

    /**
     * Pick a random element from an array
     */
    protected function randomElement(array $array)
    {
        return $array[array_rand($array)];
    }

    /**
     * Generate a random date
     */
    protected function randomDate(string $format = 'Y-m-d H:i:s'): string
    {
        $timestamp = rand(
            strtotime('-1 year'),
            time()
        );

        return date($format, $timestamp);
    }
}
