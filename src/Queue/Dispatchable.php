<?php

namespace NeoPhp\Queue;

/**
 * Dispatchable Trait
 * 
 * Makes jobs dispatchable to the queue
 */
trait Dispatchable
{
    /**
     * Dispatch the job
     */
    public static function dispatch(...$arguments): PendingDispatch
    {
        return new PendingDispatch(new static(...$arguments));
    }

    /**
     * Dispatch the job if condition is true
     */
    public static function dispatchIf(bool $condition, ...$arguments): ?PendingDispatch
    {
        return $condition ? static::dispatch(...$arguments) : null;
    }

    /**
     * Dispatch the job unless condition is true
     */
    public static function dispatchUnless(bool $condition, ...$arguments): ?PendingDispatch
    {
        return !$condition ? static::dispatch(...$arguments) : null;
    }

    /**
     * Dispatch the job synchronously (without queue)
     */
    public static function dispatchSync(...$arguments): mixed
    {
        $job = new static(...$arguments);
        return $job->handle();
    }

    /**
     * Dispatch the job after response is sent
     */
    public static function dispatchAfterResponse(...$arguments): PendingDispatch
    {
        $dispatch = static::dispatch(...$arguments);
        $dispatch->afterResponse();
        return $dispatch;
    }
}
