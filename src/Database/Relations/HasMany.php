<?php

namespace NeoPhp\Database\Relations;

use NeoPhp\Database\Model;

/**
 * Has Many Relation
 * 
 * Represents a one-to-many relationship
 * Example: Post hasMany Comments
 */
class HasMany extends Relation
{
    /**
     * Static constraints flag
     */
    protected static bool $constraints = true;

    /**
     * Add constraints for the relation
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $this->query->where(
                $this->foreignKey,
                '=',
                $this->parent->{$this->localKey}
            );
        }
    }

    /**
     * Add constraints for eager loading
     */
    public function addEagerConstraints(array $models): void
    {
        $this->query->whereIn(
            $this->foreignKey,
            $this->getKeys($models, $this->localKey)
        );
    }

    /**
     * Initialize the relation on a set of models
     */
    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, []);
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents
     */
    public function match(array $models, array $results, string $relation): array
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $key = $model->{$this->localKey};
            
            if (isset($dictionary[$key])) {
                $model->setRelation($relation, $dictionary[$key]);
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by foreign key
     */
    protected function buildDictionary(array $results): array
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->{$this->foreignKey}][] = $result;
        }

        return $dictionary;
    }

    /**
     * Get the results of the relationship
     */
    public function getResults()
    {
        return $this->query->get();
    }

    /**
     * Get keys from models
     */
    protected function getKeys(array $models, string $key): array
    {
        return array_unique(array_values(array_map(function ($model) use ($key) {
            return $model->{$key};
        }, $models)));
    }

    /**
     * Create a new instance of the related model
     */
    public function create(array $attributes = []): Model
    {
        $attributes[$this->foreignKey] = $this->parent->{$this->localKey};
        
        return $this->related->create($attributes);
    }

    /**
     * Create multiple instances of the related model
     */
    public function createMany(array $records): array
    {
        $instances = [];

        foreach ($records as $record) {
            $instances[] = $this->create($record);
        }

        return $instances;
    }
}
