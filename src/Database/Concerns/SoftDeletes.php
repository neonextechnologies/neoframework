<?php

namespace NeoPhp\Database\Concerns;

/**
 * Soft Deletes Trait
 * 
 * Provides soft delete functionality for models
 */
trait SoftDeletes
{
    /**
     * Indicates if the model is currently force deleting
     */
    protected bool $forceDeleting = false;

    /**
     * Boot the soft deleting trait for a model
     */
    public static function bootSoftDeletes(): void
    {
        static::addGlobalScope(new \NeoPhp\Database\Scopes\SoftDeletingScope);
    }

    /**
     * Force a hard delete on a soft deleted model
     */
    public function forceDelete(): bool
    {
        $this->forceDeleting = true;

        return tap($this->delete(), function ($deleted) {
            $this->forceDeleting = false;

            if ($deleted) {
                $this->fireModelEvent('forceDeleted', false);
            }
        });
    }

    /**
     * Perform the actual delete query on this model instance
     */
    protected function performDeleteOnModel(): void
    {
        if ($this->forceDeleting) {
            return $this->newQuery()->where($this->getKeyName(), $this->getKey())->forceDelete();
        }

        return $this->runSoftDelete();
    }

    /**
     * Perform the actual soft delete query on this model instance
     */
    protected function runSoftDelete(): void
    {
        $query = $this->newQuery()->where($this->getKeyName(), $this->getKey());

        $time = $this->freshTimestamp();

        $columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];

        $this->{$this->getDeletedAtColumn()} = $time;

        if ($this->timestamps && !is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;
            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);

        $this->fireModelEvent('trashed', false);
    }

    /**
     * Restore a soft-deleted model instance
     */
    public function restore(): bool
    {
        // If the restoring event does not return false, we will proceed with this
        // restore operation. Otherwise, we bail out so the developer will stop
        // the restore totally. We will clear the deleted timestamp and save.
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        $this->{$this->getDeletedAtColumn()} = null;

        // Once we have saved the model, we will fire the "restored" event so this
        // developer will do anything they need to after a restore operation is
        // totally finished. Then we will return the result of the save call.
        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('restored', false);

        return $result;
    }

    /**
     * Determine if the model instance has been soft-deleted
     */
    public function trashed(): bool
    {
        return !is_null($this->{$this->getDeletedAtColumn()});
    }

    /**
     * Register a "trashed" model event callback with the dispatcher
     */
    public static function trashed($callback): void
    {
        static::registerModelEvent('trashed', $callback);
    }

    /**
     * Register a "restoring" model event callback with the dispatcher
     */
    public static function restoring($callback): void
    {
        static::registerModelEvent('restoring', $callback);
    }

    /**
     * Register a "restored" model event callback with the dispatcher
     */
    public static function restored($callback): void
    {
        static::registerModelEvent('restored', $callback);
    }

    /**
     * Register a "forceDeleted" model event callback with the dispatcher
     */
    public static function forceDeleted($callback): void
    {
        static::registerModelEvent('forceDeleted', $callback);
    }

    /**
     * Determine if the model is currently force deleting
     */
    public function isForceDeleting(): bool
    {
        return $this->forceDeleting;
    }

    /**
     * Get the name of the "deleted at" column
     */
    public function getDeletedAtColumn(): string
    {
        return defined('static::DELETED_AT') ? static::DELETED_AT : 'deleted_at';
    }

    /**
     * Get the fully qualified "deleted at" column
     */
    public function getQualifiedDeletedAtColumn(): string
    {
        return $this->qualifyColumn($this->getDeletedAtColumn());
    }
}
