# Logging

## Introduction

NeoFramework provides robust logging services that allow you to log messages to files, the system error log, and even to Slack to notify your entire team. Under the hood, NeoFramework utilizes the Monolog library, which provides support for a variety of powerful log handlers.

## Configuration

All configuration options for your application's logging behavior are in the `config/logging.php` configuration file:

```php
return [
    'default' => env('LOG_CHANNEL', 'stack'),

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'slack'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/neoframework.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/neoframework.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'NeoFramework Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],
    ],
];
```

## Writing Log Messages

### Log Levels

NeoFramework supports all RFC 5424 log levels:

```php
use NeoPhp\Support\Facades\Log;

Log::emergency('System is down!');
Log::alert('Action must be taken immediately');
Log::critical('Critical condition');
Log::error('Error occurred');
Log::warning('Warning message');
Log::notice('Normal but significant');
Log::info('Informational message');
Log::debug('Debug information');
```

### Basic Logging

```php
Log::info('User logged in', ['user_id' => $user->id]);
Log::error('Payment failed', [
    'order_id' => $order->id,
    'error' => $exception->getMessage(),
]);
```

### Contextual Information

```php
Log::info('User registered', [
    'user_id' => $user->id,
    'email' => $user->email,
    'ip' => request()->ip(),
    'timestamp' => now(),
]);
```

### Log to Specific Channel

```php
Log::channel('slack')->critical('Database connection failed');
Log::channel('daily')->info('Backup completed');
```

### Stack Multiple Channels

```php
Log::stack(['single', 'slack'])->critical('Something went wrong!');
```

## Custom Channels

### Creating Custom Channel

Add to `config/logging.php`:

```php
'custom' => [
    'driver' => 'single',
    'path' => storage_path('logs/custom.log'),
    'level' => 'debug',
],
```

### Using Custom Channel

```php
Log::channel('custom')->info('Custom log message');
```

## Advanced Channel Configuration

### Daily Logs with Rotation

```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/neoframework.log'),
    'level' => 'debug',
    'days' => 14, // Keep logs for 14 days
],
```

### Multiple Log Files

```php
'api' => [
    'driver' => 'daily',
    'path' => storage_path('logs/api.log'),
    'level' => 'info',
    'days' => 30,
],

'jobs' => [
    'driver' => 'daily',
    'path' => storage_path('logs/jobs.log'),
    'level' => 'debug',
    'days' => 7,
],
```

## Structured Logging

### Log with Context

```php
Log::info('Processing order', [
    'order_id' => $order->id,
    'user_id' => $order->user_id,
    'total' => $order->total,
    'items_count' => $order->items->count(),
    'duration_ms' => $processingTime,
]);
```

### Exception Logging

```php
try {
    // Process payment
} catch (\Exception $e) {
    Log::error('Payment processing failed', [
        'order_id' => $order->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);

    throw $e;
}
```

## Practical Examples

### Example 1: Request Logging Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use NeoPhp\Support\Facades\Log;

class LogRequests
{
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000;

        Log::channel('api')->info('API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
            'status' => $response->status(),
            'duration_ms' => round($duration, 2),
            'memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ]);

        return $response;
    }
}

// Register middleware in routes/web.php
Route::middleware(['log.requests'])->group(function () {
    // Your routes
});
```

### Example 2: Database Query Logging

```php
<?php

namespace App\Providers;

use NeoPhp\Support\Facades\DB;
use NeoPhp\Support\Facades\Log;
use NeoPhp\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (config('app.debug')) {
            DB::listen(function ($query) {
                Log::channel('daily')->debug('Database Query', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time_ms' => $query->time,
                ]);
            });
        }
    }
}
```

### Example 3: Job Execution Logging

```php
<?php

namespace App\Jobs;

use NeoPhp\Queue\Job;
use NeoPhp\Support\Facades\Log;
use NeoPhp\Contracts\Queue\ShouldQueue;

class ProcessLargeFile implements ShouldQueue
{
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function handle()
    {
        Log::info('Starting file processing', [
            'job' => static::class,
            'file' => $this->file->name,
            'size_mb' => round($this->file->size / 1024 / 1024, 2),
        ]);

        $startTime = microtime(true);

        try {
            // Process file
            $result = $this->processFile($this->file);

            $duration = (microtime(true) - $startTime) * 1000;

            Log::info('File processing completed', [
                'job' => static::class,
                'file' => $this->file->name,
                'duration_ms' => round($duration, 2),
                'records_processed' => $result['count'],
            ]);
        } catch (\Exception $e) {
            Log::error('File processing failed', [
                'job' => static::class,
                'file' => $this->file->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function failed(\Exception $exception)
    {
        Log::critical('Job failed permanently', [
            'job' => static::class,
            'file' => $this->file->name,
            'error' => $exception->getMessage(),
        ]);
    }

    protected function processFile($file)
    {
        // File processing logic
    }
}
```

### Example 4: User Activity Logging

```php
<?php

namespace App\Services;

use NeoPhp\Support\Facades\Log;

class ActivityLogger
{
    public function log($action, $model, $modelId, array $context = [])
    {
        Log::channel('activity')->info($action, array_merge([
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'model' => $model,
            'model_id' => $modelId,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ], $context));
    }

    public function logLogin($user)
    {
        $this->log('user.login', 'User', $user->id, [
            'login_method' => 'password',
        ]);
    }

    public function logLogout($user)
    {
        $this->log('user.logout', 'User', $user->id);
    }

    public function logCreate($model, $modelId, array $data = [])
    {
        $this->log('model.created', get_class($model), $modelId, [
            'data' => $data,
        ]);
    }

    public function logUpdate($model, $modelId, array $changes = [])
    {
        $this->log('model.updated', get_class($model), $modelId, [
            'changes' => $changes,
        ]);
    }

    public function logDelete($model, $modelId)
    {
        $this->log('model.deleted', get_class($model), $modelId);
    }
}

// Usage in controller
class PostController extends Controller
{
    protected $activityLogger;

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    public function store(Request $request)
    {
        $post = Post::create($request->all());

        $this->activityLogger->logCreate($post, $post->id, [
            'title' => $post->title,
        ]);

        return redirect()->route('posts.show', $post);
    }

    public function update(Request $request, Post $post)
    {
        $originalData = $post->toArray();
        $post->update($request->all());

        $this->activityLogger->logUpdate($post, $post->id, [
            'original' => $originalData,
            'updated' => $post->toArray(),
        ]);

        return redirect()->route('posts.show', $post);
    }
}
```

### Example 5: Performance Monitoring

```php
<?php

namespace App\Services;

use NeoPhp\Support\Facades\Log;

class PerformanceMonitor
{
    protected $startTime;
    protected $startMemory;

    public function start($operation)
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);

        Log::debug("Started: {$operation}");
    }

    public function end($operation, array $context = [])
    {
        $duration = (microtime(true) - $this->startTime) * 1000;
        $memoryUsed = (memory_get_usage(true) - $this->startMemory) / 1024 / 1024;

        $data = array_merge([
            'operation' => $operation,
            'duration_ms' => round($duration, 2),
            'memory_mb' => round($memoryUsed, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ], $context);

        if ($duration > 1000) {
            Log::warning("Slow operation detected", $data);
        } else {
            Log::info("Completed: {$operation}", $data);
        }
    }
}

// Usage
class ReportController extends Controller
{
    protected $monitor;

    public function __construct(PerformanceMonitor $monitor)
    {
        $this->monitor = $monitor;
    }

    public function generate(Request $request)
    {
        $this->monitor->start('generate_report');

        $report = $this->generateReport($request->all());

        $this->monitor->end('generate_report', [
            'records_count' => $report->count(),
            'filters' => $request->all(),
        ]);

        return view('reports.show', compact('report'));
    }
}
```

### Example 6: Security Event Logging

```php
<?php

namespace App\Services;

use NeoPhp\Support\Facades\Log;

class SecurityLogger
{
    public function logFailedLogin($email, $reason = null)
    {
        Log::channel('security')->warning('Failed login attempt', [
            'email' => $email,
            'reason' => $reason,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function logSuspiciousActivity($description, array $context = [])
    {
        Log::channel('security')->alert('Suspicious activity detected', array_merge([
            'description' => $description,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
        ], $context));
    }

    public function logPasswordChange($userId)
    {
        Log::channel('security')->info('Password changed', [
            'user_id' => $userId,
            'ip' => request()->ip(),
        ]);
    }

    public function logPrivilegeEscalation($userId, $from, $to)
    {
        Log::channel('security')->warning('User privilege changed', [
            'user_id' => $userId,
            'from_role' => $from,
            'to_role' => $to,
            'changed_by' => auth()->id(),
        ]);
    }
}

// Usage in authentication
class LoginController extends Controller
{
    protected $securityLogger;

    public function __construct(SecurityLogger $securityLogger)
    {
        $this->securityLogger = $securityLogger;
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (auth()->attempt($credentials)) {
            return redirect('/dashboard');
        }

        $this->securityLogger->logFailedLogin($request->email, 'invalid_credentials');

        return back()->withErrors(['email' => 'Invalid credentials']);
    }
}
```

## Best Practices

### 1. Use Appropriate Log Levels

```php
// Emergency - System is unusable
Log::emergency('Database server is down');

// Alert - Action must be taken immediately
Log::alert('Disk space critically low');

// Critical - Critical conditions
Log::critical('Application component unavailable');

// Error - Runtime errors
Log::error('Failed to process payment');

// Warning - Exceptional occurrences that are not errors
Log::warning('API rate limit approaching');

// Notice - Normal but significant events
Log::notice('User email changed');

// Info - Informational messages
Log::info('User logged in');

// Debug - Detailed debug information
Log::debug('Cache hit for key: users.123');
```

### 2. Include Contextual Data

```php
// Good
Log::error('Order processing failed', [
    'order_id' => $order->id,
    'user_id' => $order->user_id,
    'error' => $exception->getMessage(),
]);

// Bad
Log::error('Order failed');
```

### 3. Use Separate Channels for Different Concerns

```php
'channels' => [
    'api' => [...],
    'security' => [...],
    'jobs' => [...],
    'payments' => [...],
],
```

### 4. Don't Log Sensitive Data

```php
// Bad - logs password
Log::info('User login', ['email' => $email, 'password' => $password]);

// Good
Log::info('User login', ['email' => $email]);
```

### 5. Use Log Rotation

```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/neoframework.log'),
    'level' => 'debug',
    'days' => 14, // Keep for 14 days
],
```

### 6. Monitor Log Size

Set up a scheduled task to monitor and archive logs:

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('log:clear')->daily();
}
```

## Testing

### Log Fake

```php
use NeoPhp\Support\Facades\Log;

public function test_logs_user_login()
{
    Log::shouldReceive('info')
        ->once()
        ->with('User logged in', ['user_id' => 1]);

    $this->post('/login', ['email' => 'test@example.com', 'password' => 'password']);
}
```

## Next Steps

- [Queue](queue.md) - Queue background jobs
- [Events](events.md) - Event system
- [Cache](cache.md) - Caching
- [Monitoring](../deployment/monitoring.md) - Application monitoring
