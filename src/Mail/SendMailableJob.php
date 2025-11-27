<?php

namespace NeoPhp\Mail;

use NeoPhp\Queue\Job;

/**
 * Send Mailable Job
 * 
 * Queued job to send mailable emails
 */
class SendMailableJob extends Job
{
    protected Mailable $mailable;

    public function __construct(Mailable $mailable)
    {
        $this->mailable = $mailable;
    }

    /**
     * Execute the job
     */
    public function handle(): void
    {
        $this->mailable->send();
    }

    /**
     * Handle a job failure
     */
    public function failed(\Throwable $exception): void
    {
        // Log the failure
        if (function_exists('logger')) {
            logger()->error('Failed to send email: ' . $exception->getMessage());
        }
    }
}
