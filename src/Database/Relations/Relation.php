<?php

namespace NeoPhp\Database\Relations;

use NeoPhp\Database\Model;
use NeoPhp\Database\QueryBuilder;

/**
 * Base Relation Class
 * 
 * Abstract base class for all relationship types
 */
abstract class Relation
{
    /**
     * The parent model instance
     */
    protected Model $parent;

    /**
     * The related model instance
     */
    protected Model $related;

    /**
     * The foreign key of the relationship
     */
    protected string $foreignKey;

    /**
     * The local key of the relationship
     */
    protected string $localKey;

    /**
     * The query builder instance
     */
    protected QueryBuilder $query;

    /**
     * Create a new relation instance
     */
    public function __construct(Model $parent, Model $related, string $foreignKey, string $localKey)
    {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        
        // Initialize query builder for related model
        $this->query = $related->newQuery();
    }

    /**
     * Get the parent model
     */
    public function getParent(): Model
    {
        return $this->parent;
    }

    /**
     * Get the related model
     */
    public function getRelated(): Model
    {
        return $this->related;
    }

    /**
     * Get the foreign key
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * Get the local key
     */
    public function getLocalKey(): string
    {
        return $this->localKey;
    }

    /**
     * Get the underlying query builder
     */
    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }

    /**
     * Add constraints for the relation
     */
    abstract public function addConstraints(): void;

    /**
     * Add constraints for eager loading
     */
    abstract public function addEagerConstraints(array $models): void;

    /**
     * Initialize the relation on a set of models
     */
    abstract public function initRelation(array $models, string $relation): array;

    /**
     * Match the eagerly loaded results to their parents
     */
    abstract public function match(array $models, array $results, string $relation): array;

    /**
     * Get the results of the relationship
     */
    abstract public function getResults();

    /**
     * Execute the query and get the first result
     */
    public function first()
    {
        return $this->query->first();
    }

    /**
     * Execute the query as a "select" statement
     */
    public function get()
    {
        return $this->query->get();
    }

    /**
     * Get a paginator for the "select" statement
     */
    public function paginate(int $perPage = 15)
    {
        return $this->query->paginate($perPage);
    }

    /**
     * Add a basic where clause to the query
     */
    public function where($column, $operator = null, $value = null)
    {
        $this->query->where($column, $operator, $value);
        return $this;
    }

    /**
     * Add an "order by" clause to the query
     */
    public function orderBy(string $column, string $direction = 'asc')
    {
        $this->query->orderBy($column, $direction);
        return $this;
    }

    /**
     * Set the "limit" value of the query
     */
    public function limit(int $value)
    {
        $this->query->limit($value);
        return $this;
    }

    /**
     * Dynamically handle method calls to the query
     */
    public function __call(string $method, array $parameters)
    {
        $result = $this->query->$method(...$parameters);

        if ($result === $this->query) {
            return $this;
        }

        return $result;
    }
}
