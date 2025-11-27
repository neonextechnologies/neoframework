<?php

namespace NeoPhp\Queue;

/**
 * Pending Batch
 * 
 * Handles batching of jobs
 */
class PendingBatch
{
    protected array $jobs;
    protected ?string $name = null;
    protected ?string $connection = null;
    protected ?string $queue = null;
    protected ?\Closure $then = null;
    protected ?\Closure $catch = null;
    protected ?\Closure $finally = null;
    protected bool $allowFailures = false;

    public function __construct(array $jobs)
    {
        $this->jobs = $jobs;
    }

    /**
     * Set the batch name
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the desired connection for the batch
     */
    public function onConnection(string $connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Set the desired queue for the batch
     */
    public function onQueue(string $queue): self
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * Set a callback to be called when all jobs succeed
     */
    public function then(\Closure $callback): self
    {
        $this->then = $callback;
        return $this;
    }

    /**
     * Set a callback to be called if any job fails
     */
    public function catch(\Closure $callback): self
    {
        $this->catch = $callback;
        return $this;
    }

    /**
     * Set a callback to be called regardless of success/failure
     */
    public function finally(\Closure $callback): self
    {
        $this->finally = $callback;
        return $this;
    }

    /**
     * Allow the batch to continue even if some jobs fail
     */
    public function allowFailures(bool $allow = true): self
    {
        $this->allowFailures = $allow;
        return $this;
    }

    /**
     * Dispatch the job batch
     */
    public function dispatch(): Batch
    {
        $batch = new Batch(
            $this->name ?? 'batch-' . uniqid(),
            $this->jobs,
            $this->allowFailures
        );

        // Set callbacks
        if ($this->then) {
            $batch->then($this->then);
        }

        if ($this->catch) {
            $batch->catch($this->catch);
        }

        if ($this->finally) {
            $batch->finally($this->finally);
        }

        // Dispatch all jobs
        foreach ($this->jobs as $job) {
            if ($this->connection) {
                $job->onConnection($this->connection);
            }

            if ($this->queue) {
                $job->onQueue($this->queue);
            }

            Bus::dispatch($job);
        }

        return $batch;
    }

    /**
     * Handle the object's destruction
     */
    public function __destruct()
    {
        $this->dispatch();
    }
}
