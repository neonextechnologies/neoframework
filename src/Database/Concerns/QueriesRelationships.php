<?php

namespace NeoPhp\Database\Concerns;

/**
 * Queries Relationships Trait
 * 
 * Provides eager loading functionality
 */
trait QueriesRelationships
{
    /**
     * The relationships that should be eager loaded
     */
    protected array $eagerLoad = [];

    /**
     * Begin querying a model with eager loading
     */
    public static function with(...$relations)
    {
        $instance = new static();
        
        if (count($relations) === 1 && is_array($relations[0])) {
            $relations = $relations[0];
        }

        $instance->eagerLoad = array_merge($instance->eagerLoad, $relations);

        return $instance->newQuery()->with($relations);
    }

    /**
     * Set the relationships that should be eager loaded
     */
    public function setEagerLoads(array $eagerLoad)
    {
        $this->eagerLoad = $eagerLoad;

        return $this;
    }

    /**
     * Get the relationships being eagerly loaded
     */
    public function getEagerLoads(): array
    {
        return $this->eagerLoad;
    }

    /**
     * Eager load the relationships for the models
     */
    public function eagerLoadRelations(array $models): array
    {
        foreach ($this->eagerLoad as $name) {
            $models = $this->eagerLoadRelation($models, $name);
        }

        return $models;
    }

    /**
     * Eagerly load the relationship on a set of models
     */
    protected function eagerLoadRelation(array $models, string $name): array
    {
        // First we will "back up" the existing where conditions on the query so we can
        // add our eager constraints. Then we will merge the wheres that were on the
        // query back to it in order that any where conditions might be specified.
        $relation = $this->getRelation($name);

        $relation->addEagerConstraints($models);

        return $relation->match(
            $relation->initRelation($models, $name),
            $relation->getEagerLoadResults(),
            $name
        );
    }

    /**
     * Get the relation instance for the given relation name
     */
    public function getRelation(string $name)
    {
        $relation = $this->$name();

        if (!$relation instanceof \NeoPhp\Database\Relations\Relation) {
            throw new \LogicException(
                sprintf('%s::%s must return a relationship instance.', static::class, $name)
            );
        }

        return $relation;
    }

    /**
     * Get the results for eager loading
     */
    protected function getEagerLoadResults()
    {
        return $this->get();
    }
}
