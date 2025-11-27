<?php

namespace NeoPhp\Database\Relations;

use NeoPhp\Database\Model;

/**
 * Belongs To Relation
 * 
 * Represents an inverse one-to-one or one-to-many relationship
 * Example: Comment belongsTo Post
 */
class BelongsTo extends Relation
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
                $this->localKey,
                '=',
                $this->parent->{$this->foreignKey}
            );
        }
    }

    /**
     * Add constraints for eager loading
     */
    public function addEagerConstraints(array $models): void
    {
        $this->query->whereIn(
            $this->localKey,
            $this->getKeys($models, $this->foreignKey)
        );
    }

    /**
     * Initialize the relation on a set of models
     */
    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
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
            $key = $model->{$this->foreignKey};
            
            if (isset($dictionary[$key])) {
                $model->setRelation($relation, $dictionary[$key]);
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's local key
     */
    protected function buildDictionary(array $results): array
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->{$this->localKey}] = $result;
        }

        return $dictionary;
    }

    /**
     * Get the results of the relationship
     */
    public function getResults()
    {
        return $this->query->first();
    }

    /**
     * Get keys from models
     */
    protected function getKeys(array $models, string $key): array
    {
        return array_unique(array_values(array_filter(array_map(function ($model) use ($key) {
            return $model->{$key};
        }, $models))));
    }

    /**
     * Associate the model instance to the given parent
     */
    public function associate(Model $model): Model
    {
        $this->parent->{$this->foreignKey} = $model->{$this->localKey};
        $this->parent->setRelation($this->relationName, $model);
        
        return $this->parent;
    }

    /**
     * Dissociate the model from its parent
     */
    public function dissociate(): Model
    {
        $this->parent->{$this->foreignKey} = null;
        $this->parent->setRelation($this->relationName, null);
        
        return $this->parent;
    }
}
