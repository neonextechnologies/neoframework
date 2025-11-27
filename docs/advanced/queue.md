# Queue System

## Introduction

NeoFramework's queue service provides a unified API across various queue backends, such as database, Redis, and Beanstalkd. Queues allow you to defer time-consuming tasks, such as sending emails or processing uploads, to a later time, greatly speeding up web requests.

## Configuration

Queue configuration is stored in `config/queue.php`:

```php
return [
    'default' => env('QUEUE_CONNECTION', 'sync'),

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
        ],
    ],

    'failed' => [
        'driver' => 'database',
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],
];
```

### Database Setup

Create the jobs tables:

```bash
php neo queue:table
php neo queue:failed-table
php neo migrate
```

## Creating Jobs

### Generate Job Class

```bash
php neo make:job SendWelcomeEmail
php neo make:job ProcessUpload
```

This creates `app/Jobs/SendWelcomeEmail.php`:

```php
<?php

namespace App\Jobs;

use App\Models\User;
use NeoPhp\Queue\Job;
use NeoPhp\Queue\InteractsWithQueue;
use NeoPhp\Contracts\Queue\ShouldQueue;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Send welcome email
        Mail::to($this->user)->send(new WelcomeEmail($this->user));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Handle failure
        Log::error('Failed to send welcome email', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

## Dispatching Jobs

### Basic Dispatch

```php
use App\Jobs\SendWelcomeEmail;

SendWelcomeEmail::dispatch($user);
```

### Delayed Dispatch

```php
SendWelcomeEmail::dispatch($user)
    ->delay(now()->addMinutes(10));
```

### Dispatch to Specific Queue

```php
SendWelcomeEmail::dispatch($user)
    ->onQueue('emails');
```

### Dispatch to Specific Connection

```php
SendWelcomeEmail::dispatch($user)
    ->onConnection('redis');
```

### Conditional Dispatch

```php
SendWelcomeEmail::dispatchIf($user->wants_emails, $user);

SendWelcomeEmail::dispatchUnless($user->unsubscribed, $user);
```

### Dispatch After Response

```php
SendWelcomeEmail::dispatchAfterResponse($user);
```

### Dispatch Sync (Immediate)

```php
SendWelcomeEmail::dispatchSync($user);
```

## Job Chaining

Chain multiple jobs to run in sequence:

```php
use App\Jobs\ProcessUpload;
use App\Jobs\GenerateThumbnails;
use App\Jobs\NotifyUser;

ProcessUpload::withChain([
    new GenerateThumbnails($file),
    new NotifyUser($user),
])->dispatch($file);
```

## Job Batching

Batch multiple jobs and track their progress:

```php
use NeoPhp\Queue\Batch;

$batch = Bus::batch([
    new ProcessUpload($file1),
    new ProcessUpload($file2),
    new ProcessUpload($file3),
])->then(function (Batch $batch) {
    // All jobs completed successfully
})->catch(function (Batch $batch, \Throwable $e) {
    // First batch job failure
})->finally(function (Batch $batch) {
    // Batch has finished executing
})->dispatch();

return $batch->id;
```

Check batch progress:

```php
$batch = Bus::findBatch($batchId);

if ($batch->finished()) {
    // Batch is complete
}

// Progress percentage
$percentage = $batch->progress();
```

## Running Queue Workers

### Start Queue Worker

```bash
php neo queue:work

# Specific connection
php neo queue:work redis

# Specific queue
php neo queue:work --queue=emails,default

# Process one job
php neo queue:work --once

# Stop after processing current job
php neo queue:work --stop-when-empty
```

### Worker Options

```bash
# Maximum time per job
php neo queue:work --timeout=60

# Maximum memory
php neo queue:work --memory=128

# Number of times to attempt
php neo queue:work --tries=3

# Sleep duration between jobs
php neo queue:work --sleep=3
```

### Supervisor Configuration

Create `/etc/supervisor/conf.d/neo-worker.conf`:

```ini
[program:neo-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/neo queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/logs/worker.log
stopwaitsecs=3600
```

## Job Configuration

### Tries & Timeout

```php
class ProcessUpload implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Number of times to attempt.
     */
    public int $tries = 5;

    /**
     * Timeout in seconds.
     */
    public int $timeout = 120;

    /**
     * Maximum exceptions before failing.
     */
    public int $maxExceptions = 3;
}
```

### Backoff Strategy

```php
class ProcessUpload implements ShouldQueue
{
    /**
     * Backoff in seconds between retries.
     */
    public int $backoff = 3;

    // Or exponential backoff
    public function backoff(): array
    {
        return [1, 5, 10, 30];
    }
}
```

### Rate Limiting

```php
use NeoPhp\Cache\RateLimiter;

class ProcessUpload implements ShouldQueue
{
    public function handle(RateLimiter $limiter)
    {
        $limiter->attempt(
            'process-upload:' . $this->user->id,
            $perMinute = 5,
            function () {
                // Process upload
            }
        );
    }
}
```

## Job Middleware

Create reusable job middleware:

```php
<?php

namespace App\Jobs\Middleware;

class RateLimited
{
    public function __construct(
        private string $key,
        private int $maxAttempts = 60
    ) {}

    public function handle($job, $next)
    {
        if (RateLimiter::tooManyAttempts($this->key, $this->maxAttempts)) {
            return $job->release(60);
        }

        RateLimiter::hit($this->key);

        $next($job);
    }
}
```

Use in job:

```php
use App\Jobs\Middleware\RateLimited;

class ProcessUpload implements ShouldQueue
{
    public function middleware(): array
    {
        return [new RateLimited('uploads', 10)];
    }
}
```

## Practical Examples

### Example 1: Email Queue

```php
<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\WelcomeEmail;
use NeoPhp\Queue\Job;
use NeoPhp\Contracts\Queue\ShouldQueue;
use NeoPhp\Queue\InteractsWithQueue;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public User $user
    ) {}

    public function handle(): void
    {
        if ($this->user->email_verified_at === null) {
            $this->release(60); // Retry in 60 seconds
            return;
        }

        Mail::to($this->user)->send(new WelcomeEmail($this->user));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send welcome email', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);

        // Notify admin
        Notification::send(
            User::admins()->get(),
            new EmailFailedNotification($this->user, $exception)
        );
    }
}
```

### Example 2: File Processing

```php
<?php

namespace App\Jobs;

use App\Models\Upload;
use NeoPhp\Queue\Job;
use NeoPhp\Contracts\Queue\ShouldQueue;
use NeoPhp\Queue\InteractsWithQueue;

class ProcessUpload implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public Upload $upload
    ) {}

    public function handle(): void
    {
        $this->upload->update(['status' => 'processing']);

        try {
            // Process file
            $path = Storage::disk('s3')->put(
                'uploads',
                $this->upload->temp_path
            );

            // Generate thumbnails
            $this->generateThumbnails($path);

            // Extract metadata
            $metadata = $this->extractMetadata($path);

            $this->upload->update([
                'path' => $path,
                'metadata' => $metadata,
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            // Delete temp file
            Storage::delete($this->upload->temp_path);

        } catch (\Exception $e) {
            $this->upload->update(['status' => 'failed']);
            throw $e;
        }
    }

    protected function generateThumbnails(string $path): void
    {
        $sizes = [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600],
        ];

        foreach ($sizes as $name => $dimensions) {
            GenerateThumbnail::dispatch(
                $this->upload,
                $path,
                $name,
                $dimensions
            );
        }
    }

    protected function extractMetadata(string $path): array
    {
        // Extract EXIF data, dimensions, etc.
        return [];
    }
}
```

### Example 3: Batch Import

```php
<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use NeoPhp\Queue\Job;
use NeoPhp\Contracts\Queue\ShouldQueue;
use NeoPhp\Queue\InteractsWithQueue;

class ImportUsers implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        public ImportBatch $batch,
        public array $rows
    ) {}

    public function handle(): void
    {
        foreach ($this->rows as $row) {
            try {
                User::updateOrCreate(
                    ['email' => $row['email']],
                    [
                        'name' => $row['name'],
                        'phone' => $row['phone'] ?? null,
                    ]
                );

                $this->batch->increment('processed');
                $this->batch->increment('successful');

            } catch (\Exception $e) {
                $this->batch->increment('processed');
                $this->batch->increment('failed');

                Log::error('Import user failed', [
                    'email' => $row['email'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Check if batch is complete
        if ($this->batch->processed >= $this->batch->total) {
            $this->batch->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Notify user
            ImportCompleted::dispatch($this->batch);
        }
    }
}
```

## Failed Jobs

### Retrying Failed Jobs

```bash
# Retry all failed jobs
php neo queue:retry all

# Retry specific job
php neo queue:retry 5

# Retry jobs from last hour
php neo queue:retry --range=1-100
```

### Deleting Failed Jobs

```bash
# Delete all failed jobs
php neo queue:flush

# Delete specific job
php neo queue:forget 5
```

### Pruning Failed Jobs

```bash
# Delete failed jobs older than 48 hours
php neo queue:prune-failed --hours=48
```

## Monitoring

### Job Events

```php
Queue::before(function (JobProcessing $event) {
    // Job is about to process
});

Queue::after(function (JobProcessed $event) {
    // Job has processed
});

Queue::failing(function (JobFailed $event) {
    // Job has failed
});
```

## Best Practices

### 1. Make Jobs Idempotent

Jobs should be safe to run multiple times:

```php
public function handle(): void
{
    User::updateOrCreate(
        ['email' => $this->email],
        ['name' => $this->name]
    );
}
```

### 2. Use Job Chaining

```php
ProcessUpload::withChain([
    new GenerateThumbnails($file),
    new NotifyUser($user),
])->dispatch($file);
```

### 3. Handle Failures Gracefully

```php
public function failed(\Throwable $exception): void
{
    // Log error, notify admin, etc.
}
```

### 4. Set Appropriate Timeouts

```php
public int $timeout = 120; // 2 minutes
```

### 5. Use Queues for Slow Operations

```php
// Slow operation - use queue
SendEmail::dispatch($user);

// Fast operation - run immediately
$user->update(['name' => 'John']);
```

## Next Steps

- [Mail](mail.md) - Sending emails
- [Events](events.md) - Event system
- [Broadcasting](broadcasting.md) - Real-time events
