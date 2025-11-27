<?php

namespace NeoPhp\Database\Relations;

use NeoPhp\Database\Model;

/**
 * Belongs To Many Relation
 * 
 * Represents a many-to-many relationship
 * Example: Post belongsToMany Tags (through post_tag pivot table)
 */
class BelongsToMany extends Relation
{
    /**
     * The pivot table name
     */
    protected string $table;

    /**
     * The pivot key on the related model
     */
    protected string $relatedPivotKey;

    /**
     * The pivot key on the parent model
     */
    protected string $parentPivotKey;

    /**
     * Static constraints flag
     */
    protected static bool $constraints = true;

    /**
     * Create a new belongs to many relationship instance
     */
    public function __construct(
        Model $parent,
        Model $related,
        string $table,
        string $foreignKey,
        string $relatedKey,
        string $parentKey = 'id',
        string $relatedKey2 = 'id'
    ) {
        $this->table = $table;
        $this->parentPivotKey = $foreignKey;
        $this->relatedPivotKey = $relatedKey;
        
        parent::__construct($parent, $related, $foreignKey, $parentKey);
    }

    /**
     * Add constraints for the relation
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $this->performJoin();
            
            $this->query->where(
                $this->table . '.' . $this->parentPivotKey,
                '=',
                $this->parent->{$this->localKey}
            );
        }
    }

    /**
     * Perform the join for the relation query
     */
    protected function performJoin(): void
    {
        $relatedTable = $this->related->getTable();
        
        $this->query->join(
            $this->table,
            $relatedTable . '.' . $this->related->getKeyName(),
            '=',
            $this->table . '.' . $this->relatedPivotKey
        );
    }

    /**
     * Add constraints for eager loading
     */
    public function addEagerConstraints(array $models): void
    {
        $this->performJoin();
        
        $this->query->whereIn(
            $this->table . '.' . $this->parentPivotKey,
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
     * Build model dictionary
     */
    protected function buildDictionary(array $results): array
    {
        $dictionary = [];

        foreach ($results as $result) {
            $pivotKey = $result->pivot->{$this->parentPivotKey} ?? null;
            
            if ($pivotKey) {
                $dictionary[$pivotKey][] = $result;
            }
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
     * Attach a model to the parent
     */
    public function attach($id, array $attributes = []): void
    {
        $record = array_merge([
            $this->parentPivotKey => $this->parent->{$this->localKey},
            $this->relatedPivotKey => $id,
        ], $attributes);

        // Insert into pivot table
        db()->table($this->table)->insert($record);
    }

    /**
     * Detach models from the relationship
     */
    public function detach($ids = null): int
    {
        $query = db()->table($this->table)
            ->where($this->parentPivotKey, $this->parent->{$this->localKey});

        if ($ids !== null) {
            $ids = is_array($ids) ? $ids : [$ids];
            $query->whereIn($this->relatedPivotKey, $ids);
        }

        return $query->delete();
    }

    /**
     * Sync the intermediate tables with a list of IDs
     */
    public function sync($ids): array
    {
        $changes = [
            'attached' => [],
            'detached' => [],
            'updated' => [],
        ];

        // Get current IDs
        $current = $this->getCurrentIds();

        // Determine what to detach
        $detach = array_diff($current, $ids);
        if (count($detach) > 0) {
            $this->detach($detach);
            $changes['detached'] = $detach;
        }

        // Determine what to attach
        $attach = array_diff($ids, $current);
        if (count($attach) > 0) {
            foreach ($attach as $id) {
                $this->attach($id);
            }
            $changes['attached'] = $attach;
        }

        return $changes;
    }

    /**
     * Get the current IDs from the pivot table
     */
    protected function getCurrentIds(): array
    {
        $results = db()->table($this->table)
            ->where($this->parentPivotKey, $this->parent->{$this->localKey})
            ->get();

        return array_map(function ($result) {
            return $result->{$this->relatedPivotKey};
        }, $results);
    }
}
