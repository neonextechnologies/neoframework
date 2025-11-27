<?php

namespace NeoPhp\Database\Concerns;

/**
 * Has Events Trait
 * 
 * Provides model event functionality
 */
trait HasEvents
{
    /**
     * The event dispatcher instance
     */
    protected static $dispatcher;

    /**
     * The event map for the model
     */
    protected $dispatchesEvents = [];

    /**
     * User exposed observable events
     */
    protected $observables = [];

    /**
     * Register an observer with the Model
     */
    public static function observe($class): void
    {
        $instance = new static;

        foreach ($instance->getObservableEvents() as $event) {
            if (method_exists($class, $event)) {
                static::registerModelEvent($event, [$class, $event]);
            }
        }
    }

    /**
     * Get the observable event names
     */
    public function getObservableEvents(): array
    {
        return array_merge(
            [
                'retrieved', 'creating', 'created', 'updating', 'updated',
                'saving', 'saved', 'deleting', 'deleted', 'restoring',
                'restored', 'replicating',
            ],
            $this->observables
        );
    }

    /**
     * Set the observable event names
     */
    public function setObservableEvents(array $observables): self
    {
        $this->observables = $observables;

        return $this;
    }

    /**
     * Add an observable event name
     */
    public function addObservableEvents($observables): void
    {
        $this->observables = array_unique(array_merge(
            $this->observables,
            is_array($observables) ? $observables : func_get_args()
        ));
    }

    /**
     * Remove an observable event name
     */
    public function removeObservableEvents($observables): void
    {
        $this->observables = array_diff(
            $this->observables,
            is_array($observables) ? $observables : func_get_args()
        );
    }

    /**
     * Register a model event with the dispatcher
     */
    protected static function registerModelEvent(string $event, $callback): void
    {
        if (isset(static::$dispatcher)) {
            $name = static::class;

            static::$dispatcher->listen("eloquent.{$event}: {$name}", $callback);
        }
    }

    /**
     * Fire the given event for the model
     */
    protected function fireModelEvent(string $event, bool $halt = true)
    {
        if (!isset(static::$dispatcher)) {
            return true;
        }

        // First, we will get the proper method to call on the event dispatcher, and then we
        // will attempt to fire a custom, object based event for the given event. If that
        // returns a result we can return that result, or we'll call the string events.
        $method = $halt ? 'until' : 'dispatch';

        $result = $this->filterModelEventResults(
            $this->fireCustomModelEvent($event, $method)
        );

        if ($result === false) {
            return false;
        }

        return !empty($result) ? $result : static::$dispatcher->{$method}(
            "eloquent.{$event}: ".static::class, $this
        );
    }

    /**
     * Fire a custom model event for the given event
     */
    protected function fireCustomModelEvent(string $event, string $method)
    {
        if (!isset($this->dispatchesEvents[$event])) {
            return null;
        }

        $result = static::$dispatcher->$method(new $this->dispatchesEvents[$event]($this));

        if (!is_null($result)) {
            return $result;
        }
    }

    /**
     * Filter the model event results
     */
    protected function filterModelEventResults($result)
    {
        if (is_array($result)) {
            $result = array_filter($result, function ($response) {
                return !is_null($response);
            });
        }

        return $result;
    }

    /**
     * Register a retrieved model event with the dispatcher
     */
    public static function retrieved($callback): void
    {
        static::registerModelEvent('retrieved', $callback);
    }

    /**
     * Register a saving model event with the dispatcher
     */
    public static function saving($callback): void
    {
        static::registerModelEvent('saving', $callback);
    }

    /**
     * Register a saved model event with the dispatcher
     */
    public static function saved($callback): void
    {
        static::registerModelEvent('saved', $callback);
    }

    /**
     * Register an updating model event with the dispatcher
     */
    public static function updating($callback): void
    {
        static::registerModelEvent('updating', $callback);
    }

    /**
     * Register an updated model event with the dispatcher
     */
    public static function updated($callback): void
    {
        static::registerModelEvent('updated', $callback);
    }

    /**
     * Register a creating model event with the dispatcher
     */
    public static function creating($callback): void
    {
        static::registerModelEvent('creating', $callback);
    }

    /**
     * Register a created model event with the dispatcher
     */
    public static function created($callback): void
    {
        static::registerModelEvent('created', $callback);
    }

    /**
     * Register a deleting model event with the dispatcher
     */
    public static function deleting($callback): void
    {
        static::registerModelEvent('deleting', $callback);
    }

    /**
     * Register a deleted model event with the dispatcher
     */
    public static function deleted($callback): void
    {
        static::registerModelEvent('deleted', $callback);
    }

    /**
     * Remove all of the event listeners for the model
     */
    public static function flushEventListeners(): void
    {
        if (!isset(static::$dispatcher)) {
            return;
        }

        $instance = new static;

        foreach ($instance->getObservableEvents() as $event) {
            static::$dispatcher->forget("eloquent.{$event}: ".static::class);
        }
    }

    /**
     * Get the event dispatcher instance
     */
    public static function getEventDispatcher()
    {
        return static::$dispatcher;
    }

    /**
     * Set the event dispatcher instance
     */
    public static function setEventDispatcher($dispatcher): void
    {
        static::$dispatcher = $dispatcher;
    }

    /**
     * Unset the event dispatcher for models
     */
    public static function unsetEventDispatcher(): void
    {
        static::$dispatcher = null;
    }
}
