<?php

namespace NeoPhp\Database\Concerns;

use NeoPhp\Database\Relations\HasOne;
use NeoPhp\Database\Relations\HasMany;
use NeoPhp\Database\Relations\BelongsTo;
use NeoPhp\Database\Relations\BelongsToMany;
use NeoPhp\Database\Model;

/**
 * Has Relationships Trait
 * 
 * Provides relationship methods for models
 */
trait HasRelationships
{
    /**
     * The loaded relationships for the model
     */
    protected array $relations = [];

    /**
     * The relationships that should be eager loaded
     */
    protected array $with = [];

    /**
     * Define a one-to-one relationship
     */
    protected function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): HasOne
    {
        $instance = new $related();
        
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? $this->getKeyName();

        return new HasOne($this, $instance, $foreignKey, $localKey);
    }

    /**
     * Define a one-to-many relationship
     */
    protected function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): HasMany
    {
        $instance = new $related();
        
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? $this->getKeyName();

        return new HasMany($this, $instance, $foreignKey, $localKey);
    }

    /**
     * Define an inverse one-to-one or many relationship
     */
    protected function belongsTo(string $related, ?string $foreignKey = null, ?string $ownerKey = null): BelongsTo
    {
        $instance = new $related();
        
        $foreignKey = $foreignKey ?? $this->getRelationName($related) . '_id';
        $ownerKey = $ownerKey ?? $instance->getKeyName();

        return new BelongsTo($this, $instance, $foreignKey, $ownerKey);
    }

    /**
     * Define a many-to-many relationship
     */
    protected function belongsToMany(
        string $related,
        ?string $table = null,
        ?string $foreignPivotKey = null,
        ?string $relatedPivotKey = null,
        ?string $parentKey = null,
        ?string $relatedKey = null
    ): BelongsToMany {
        $instance = new $related();
        
        $foreignPivotKey = $foreignPivotKey ?? $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?? $instance->getForeignKey();
        
        $table = $table ?? $this->joiningTable($related);
        
        $parentKey = $parentKey ?? $this->getKeyName();
        $relatedKey = $relatedKey ?? $instance->getKeyName();

        return new BelongsToMany(
            $this,
            $instance,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey
        );
    }

    /**
     * Get the default foreign key name for the model
     */
    public function getForeignKey(): string
    {
        return strtolower(class_basename($this)) . '_' . $this->getKeyName();
    }

    /**
     * Get the joining table name for a many-to-many relation
     */
    protected function joiningTable(string $related): string
    {
        $models = [
            strtolower(class_basename($this)),
            strtolower(class_basename($related)),
        ];

        sort($models);

        return implode('_', $models);
    }

    /**
     * Get a relationship value from a method
     */
    public function getRelationValue(string $key)
    {
        // If the key already exists in the relationships array, return it
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        // Check if a mutator exists for the relationship
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }

        return null;
    }

    /**
     * Get a relationship from a method
     */
    protected function getRelationshipFromMethod(string $method)
    {
        $relation = $this->$method();

        if (!$relation instanceof \NeoPhp\Database\Relations\Relation) {
            throw new \LogicException(
                sprintf('%s::%s must return a relationship instance.', static::class, $method)
            );
        }

        $this->setRelation($method, $relation->getResults());

        return $this->relations[$method];
    }

    /**
     * Determine if the given relation is loaded
     */
    public function relationLoaded(string $key): bool
    {
        return array_key_exists($key, $this->relations);
    }

    /**
     * Set the specific relationship in the model
     */
    public function setRelation(string $relation, $value): self
    {
        $this->relations[$relation] = $value;

        return $this;
    }

    /**
     * Get all loaded relations for the model
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * Set the entire relations array on the model
     */
    public function setRelations(array $relations): self
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * Get relation name from class name
     */
    protected function getRelationName(string $related): string
    {
        return strtolower(class_basename($related));
    }

    /**
     * Eager load relations on the model
     */
    public function load(...$relations): self
    {
        if (count($relations) === 1 && is_array($relations[0])) {
            $relations = $relations[0];
        }

        foreach ($relations as $name) {
            if (!$this->relationLoaded($name)) {
                $this->getRelationValue($name);
            }
        }

        return $this;
    }
}
