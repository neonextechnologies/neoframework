<?php

namespace NeoPhp\Queue;

/**
 * Interacts With Queue Trait
 * 
 * Provides queue interaction methods
 */
trait InteractsWithQueue
{
    /**
     * The underlying queue job instance
     */
    public $job;

    /**
     * The number of times the job has been attempted
     */
    public int $attempts = 0;

    /**
     * Get the number of times the job has been attempted
     */
    public function attempts(): int
    {
        return $this->attempts;
    }

    /**
     * Delete the job from the queue
     */
    public function delete(): void
    {
        if ($this->job) {
            $this->job->delete();
        }
    }

    /**
     * Fail the job
     */
    public function fail(\Throwable $exception = null): void
    {
        if ($this->job) {
            $this->job->fail($exception);
        }
    }

    /**
     * Release the job back onto the queue
     */
    public function release(int $delay = 0): void
    {
        if ($this->job) {
            $this->job->release($delay);
        }
    }

    /**
     * Set the base queue job instance
     */
    public function setJob($job): self
    {
        $this->job = $job;
        return $this;
    }
}
