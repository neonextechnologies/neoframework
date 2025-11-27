<?php

namespace NeoPhp\Queue;

/**
 * Bus - Job Dispatcher
 * 
 * Handles job dispatching, chaining, and batching
 */
class Bus
{
    /**
     * Dispatch a job to the queue
     */
    public static function dispatch(Job $job): PendingDispatch
    {
        return new PendingDispatch($job);
    }

    /**
     * Dispatch a job synchronously
     */
    public static function dispatchSync(Job $job): mixed
    {
        return $job->handle();
    }

    /**
     * Create a new job chain
     */
    public static function chain(array $jobs): PendingChain
    {
        return new PendingChain($jobs);
    }

    /**
     * Create a new job batch
     */
    public static function batch(array $jobs): PendingBatch
    {
        return new PendingBatch($jobs);
    }

    /**
     * Dispatch multiple jobs
     */
    public static function dispatchMany(array $jobs): void
    {
        foreach ($jobs as $job) {
            static::dispatch($job);
        }
    }

    /**
     * Dispatch a job after a delay
     */
    public static function dispatchAfter(int $delay, Job $job): PendingDispatch
    {
        return static::dispatch($job)->delay($delay);
    }

    /**
     * Dispatch a job if condition is true
     */
    public static function dispatchIf(bool $condition, Job $job): ?PendingDispatch
    {
        return $condition ? static::dispatch($job) : null;
    }

    /**
     * Dispatch a job unless condition is true
     */
    public static function dispatchUnless(bool $condition, Job $job): ?PendingDispatch
    {
        return !$condition ? static::dispatch($job) : null;
    }
}
