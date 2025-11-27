<?php

namespace NeoPhp\Queue;

/**
 * Base Job Class
 * 
 * All queued jobs should extend this class
 */
abstract class Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of times the job may be attempted
     */
    public int $tries = 1;

    /**
     * The number of seconds the job can run before timing out
     */
    public int $timeout = 60;

    /**
     * The queue connection that should handle the job
     */
    public ?string $connection = null;

    /**
     * The name of the queue the job should be sent to
     */
    public ?string $queue = null;

    /**
     * The number of seconds to wait before retrying the job
     */
    public int $retryAfter = 0;

    /**
     * Execute the job
     */
    abstract public function handle(): void;

    /**
     * Handle a job failure
     */
    public function failed(\Throwable $exception): void
    {
        //
    }

    /**
     * Get the number of times the job has been attempted
     */
    public function attempts(): int
    {
        return $this->attempts ?? 1;
    }

    /**
     * Delete the job from the queue
     */
    public function delete(): void
    {
        if (isset($this->job)) {
            $this->job->delete();
        }
    }

    /**
     * Release the job back into the queue
     */
    public function release(int $delay = 0): void
    {
        if (isset($this->job)) {
            $this->job->release($delay);
        }
    }

    /**
     * Determine if the job has been deleted
     */
    public function isDeleted(): bool
    {
        return isset($this->deleted) && $this->deleted;
    }

    /**
     * Determine if the job has been released
     */
    public function isReleased(): bool
    {
        return isset($this->released) && $this->released;
    }

    /**
     * Determine if the job should fail on timeout
     */
    public function shouldFailOnTimeout(): bool
    {
        return true;
    }
}
