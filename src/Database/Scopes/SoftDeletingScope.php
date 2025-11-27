<?php

namespace NeoPhp\Database\Scopes;

use NeoPhp\Database\QueryBuilder;
use NeoPhp\Database\Model;

/**
 * Soft Deleting Scope
 * 
 * Global scope that filters out soft deleted models
 */
class SoftDeletingScope
{
    /**
     * All of the extensions to be added to the query builder
     */
    protected array $extensions = ['Restore', 'WithTrashed', 'WithoutTrashed', 'OnlyTrashed'];

    /**
     * Apply the scope to a given query builder
     */
    public function apply(QueryBuilder $builder, Model $model): void
    {
        $builder->whereNull($model->getQualifiedDeletedAtColumn());
    }

    /**
     * Extend the query builder with the needed functions
     */
    public function extend(QueryBuilder $builder): void
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Add the restore extension to the builder
     */
    protected function addRestore(QueryBuilder $builder): void
    {
        $builder->macro('restore', function (QueryBuilder $builder) {
            $builder->withTrashed();

            return $builder->update([$builder->getModel()->getDeletedAtColumn() => null]);
        });
    }

    /**
     * Add the with-trashed extension to the builder
     */
    protected function addWithTrashed(QueryBuilder $builder): void
    {
        $builder->macro('withTrashed', function (QueryBuilder $builder, bool $withTrashed = true) {
            if (!$withTrashed) {
                return $builder->withoutTrashed();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-trashed extension to the builder
     */
    protected function addWithoutTrashed(QueryBuilder $builder): void
    {
        $builder->macro('withoutTrashed', function (QueryBuilder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)
                    ->whereNull($model->getQualifiedDeletedAtColumn());

            return $builder;
        });
    }

    /**
     * Add the only-trashed extension to the builder
     */
    protected function addOnlyTrashed(QueryBuilder $builder): void
    {
        $builder->macro('onlyTrashed', function (QueryBuilder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)
                    ->whereNotNull($model->getQualifiedDeletedAtColumn());

            return $builder;
        });
    }
}
