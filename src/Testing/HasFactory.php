<?php

namespace NeoPhp\Testing;

/**
 * Has Factory Trait
 * 
 * Add this trait to models to enable factory support
 */
trait HasFactory
{
    /**
     * Get a new factory instance for the model
     */
    public static function factory(int $count = 1): Factory
    {
        $factoryClass = static::getFactoryClass();

        if (!class_exists($factoryClass)) {
            throw new \Exception("Factory class {$factoryClass} not found");
        }

        return (new $factoryClass())->count($count);
    }

    /**
     * Get the factory class name
     */
    protected static function getFactoryClass(): string
    {
        $modelClass = static::class;
        $modelName = class_basename($modelClass);

        return "Database\\Factories\\{$modelName}Factory";
    }
}
