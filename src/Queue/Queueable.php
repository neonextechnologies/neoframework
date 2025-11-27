<?php

namespace NeoPhp\Queue;

use DateTime;

/**
 * Queueable Trait
 * 
 * Provides queue configuration methods
 */
trait Queueable
{
    /**
     * The name of the connection the job should be sent to
     */
    public ?string $connection = null;

    /**
     * The name of the queue the job should be sent to
     */
    public ?string $queue = null;

    /**
     * The number of seconds before the job should be made available
     */
    public ?int $delay = null;

    /**
     * Indicates whether the job should be dispatched after all database transactions have committed
     */
    public bool $afterCommit = false;

    /**
     * The middleware the job should pass through
     */
    public array $middleware = [];

    /**
     * The jobs that should run if this job is successful
     */
    public array $chained = [];

    /**
     * Set the desired connection for the job
     */
    public function onConnection(string $connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Set the desired queue for the job
     */
    public function onQueue(string $queue): self
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * Set the desired delay for the job
     */
    public function delay(int|DateTime $delay): self
    {
        $this->delay = $delay instanceof DateTime 
            ? $delay->getTimestamp() - time()
            : $delay;

        return $this;
    }

    /**
     * Set the jobs that should run if this job is successful
     */
    public function chain(array $chain): self
    {
        $this->chained = $chain;
        return $this;
    }

    /**
     * Indicate that the job should be dispatched after all database transactions have committed
     */
    public function afterCommit(): self
    {
        $this->afterCommit = true;
        return $this;
    }

    /**
     * Indicate that the job should not wait for database transactions to commit
     */
    public function beforeCommit(): self
    {
        $this->afterCommit = false;
        return $this;
    }

    /**
     * Specify the middleware the job should be dispatched through
     */
    public function through(array $middleware): self
    {
        $this->middleware = $middleware;
        return $this;
    }
}
