<?php

namespace NeoPhp\Queue;

/**
 * Pending Dispatch
 * 
 * Handles job dispatch configuration before sending to queue
 */
class PendingDispatch
{
    protected Job $job;
    protected bool $afterResponse = false;

    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    /**
     * Set the desired connection for the job
     */
    public function onConnection(string $connection): self
    {
        $this->job->onConnection($connection);
        return $this;
    }

    /**
     * Set the desired queue for the job
     */
    public function onQueue(string $queue): self
    {
        $this->job->onQueue($queue);
        return $this;
    }

    /**
     * Set the desired delay for the job
     */
    public function delay(int $delay): self
    {
        $this->job->delay($delay);
        return $this;
    }

    /**
     * Set the jobs that should run if this job is successful
     */
    public function chain(array $chain): self
    {
        $this->job->chain($chain);
        return $this;
    }

    /**
     * Dispatch the job after the response is sent
     */
    public function afterResponse(): self
    {
        $this->afterResponse = true;
        return $this;
    }

    /**
     * Handle the object's destruction - dispatches the job
     */
    public function __destruct()
    {
        if ($this->afterResponse) {
            // Queue after response
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
        }

        // Dispatch to queue
        $queue = app('queue');
        
        $jobClass = get_class($this->job);
        $jobData = serialize($this->job);

        $queue->push($jobClass, [
            'job' => $jobData,
            'data' => []
        ], $this->job->queue);
    }

    /**
     * Get the underlying job
     */
    public function getJob(): Job
    {
        return $this->job;
    }
}
