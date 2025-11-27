<?php

namespace NeoPhp\Queue;

/**
 * Batch
 * 
 * Represents a batch of queued jobs
 */
class Batch
{
    protected string $id;
    protected string $name;
    protected array $jobs;
    protected bool $allowFailures;
    protected int $totalJobs;
    protected int $pendingJobs;
    protected int $failedJobs = 0;
    protected array $failedJobIds = [];
    protected ?\Closure $thenCallback = null;
    protected ?\Closure $catchCallback = null;
    protected ?\Closure $finallyCallback = null;

    public function __construct(string $name, array $jobs, bool $allowFailures = false)
    {
        $this->id = uniqid('batch_');
        $this->name = $name;
        $this->jobs = $jobs;
        $this->allowFailures = $allowFailures;
        $this->totalJobs = count($jobs);
        $this->pendingJobs = $this->totalJobs;
    }

    /**
     * Get the batch ID
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Get the batch name
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get total jobs in the batch
     */
    public function totalJobs(): int
    {
        return $this->totalJobs;
    }

    /**
     * Get pending jobs count
     */
    public function pendingJobs(): int
    {
        return $this->pendingJobs;
    }

    /**
     * Get failed jobs count
     */
    public function failedJobs(): int
    {
        return $this->failedJobs;
    }

    /**
     * Get progress percentage
     */
    public function progress(): int
    {
        if ($this->totalJobs === 0) {
            return 100;
        }

        $completed = $this->totalJobs - $this->pendingJobs;
        return (int) (($completed / $this->totalJobs) * 100);
    }

    /**
     * Check if batch has finished
     */
    public function finished(): bool
    {
        return $this->pendingJobs === 0;
    }

    /**
     * Check if batch has failures
     */
    public function hasFailures(): bool
    {
        return $this->failedJobs > 0;
    }

    /**
     * Check if batch was cancelled
     */
    public function cancelled(): bool
    {
        return $this->hasFailures() && !$this->allowFailures;
    }

    /**
     * Set then callback
     */
    public function then(\Closure $callback): self
    {
        $this->thenCallback = $callback;
        return $this;
    }

    /**
     * Set catch callback
     */
    public function catch(\Closure $callback): self
    {
        $this->catchCallback = $callback;
        return $this;
    }

    /**
     * Set finally callback
     */
    public function finally(\Closure $callback): self
    {
        $this->finallyCallback = $callback;
        return $this;
    }

    /**
     * Mark a job as finished
     */
    public function jobFinished(): void
    {
        $this->pendingJobs--;

        if ($this->finished()) {
            $this->executeThenCallback();
            $this->executeFinallyCallback();
        }
    }

    /**
     * Mark a job as failed
     */
    public function jobFailed(string $jobId, \Throwable $e): void
    {
        $this->failedJobs++;
        $this->failedJobIds[] = $jobId;
        $this->pendingJobs--;

        if (!$this->allowFailures) {
            $this->executeCatchCallback($e);
            $this->executeFinallyCallback();
        } elseif ($this->finished()) {
            if ($this->hasFailures()) {
                $this->executeCatchCallback($e);
            } else {
                $this->executeThenCallback();
            }
            $this->executeFinallyCallback();
        }
    }

    /**
     * Execute then callback
     */
    protected function executeThenCallback(): void
    {
        if ($this->thenCallback) {
            ($this->thenCallback)($this);
        }
    }

    /**
     * Execute catch callback
     */
    protected function executeCatchCallback(\Throwable $e): void
    {
        if ($this->catchCallback) {
            ($this->catchCallback)($this, $e);
        }
    }

    /**
     * Execute finally callback
     */
    protected function executeFinallyCallback(): void
    {
        if ($this->finallyCallback) {
            ($this->finallyCallback)($this);
        }
    }
}
