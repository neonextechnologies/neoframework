<?php

namespace NeoPhp\Queue;

/**
 * Pending Chain
 * 
 * Handles chaining of jobs
 */
class PendingChain
{
    protected array $jobs;
    protected ?string $connection = null;
    protected ?string $queue = null;
    protected ?int $delay = null;
    protected ?\Closure $catch = null;

    public function __construct(array $jobs)
    {
        $this->jobs = $jobs;
    }

    /**
     * Set the desired connection for the chain
     */
    public function onConnection(string $connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Set the desired queue for the chain
     */
    public function onQueue(string $queue): self
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * Set the desired delay for the chain
     */
    public function delay(int $delay): self
    {
        $this->delay = $delay;
        return $this;
    }

    /**
     * Set a callback to be called if the chain fails
     */
    public function catch(\Closure $callback): self
    {
        $this->catch = $callback;
        return $this;
    }

    /**
     * Dispatch the job chain
     */
    public function dispatch(): void
    {
        if (empty($this->jobs)) {
            return;
        }

        $firstJob = array_shift($this->jobs);

        // Configure first job with remaining jobs as chain
        if (!empty($this->jobs)) {
            $firstJob->chain($this->jobs);
        }

        if ($this->connection) {
            $firstJob->onConnection($this->connection);
        }

        if ($this->queue) {
            $firstJob->onQueue($this->queue);
        }

        if ($this->delay) {
            $firstJob->delay($this->delay);
        }

        // Store catch callback if provided
        if ($this->catch) {
            $firstJob->catch = $this->catch;
        }

        Bus::dispatch($firstJob);
    }

    /**
     * Handle the object's destruction
     */
    public function __destruct()
    {
        $this->dispatch();
    }
}
