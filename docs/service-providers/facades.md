# Facades 🎭

## Introduction

Facades provide a static interface to classes available in the service container. They act as "static proxies" to underlying container services, providing a convenient, expressive syntax while maintaining the testability and flexibility that dependency injection provides.

Think of facades as a convenience layer that lets you access services without verbose container resolution, while still maintaining all the benefits of dependency injection under the hood.

```php
// Without facade - verbose
$cache = app()->make('cache');
$cache->put('key', 'value', 3600);

// With facade - clean and expressive
Cache::put('key', 'value', 3600);
```

## How Facades Work

Facades use PHP's `__callStatic()` magic method to proxy method calls to container-bound objects:

```php
// When you call
Cache::get('key');

// Facade does this behind the scenes:
// 1. Resolves 'cache' from container
// 2. Calls get() on the resolved instance
app('cache')->get('key');
```

### Facade Architecture

```php
<?php

namespace NeoPhp\Support;

abstract class Facade
{
    /**
     * The application instance
     */
    protected static $app;
    
    /**
     * The resolved object instances
     */
    protected static $resolvedInstance;
    
    /**
     * Get the registered name of the component
     */
    abstract protected static function getFacadeAccessor(): string;
    
    /**
     * Resolve the facade root instance
     */
    protected static function resolveFacadeInstance(string $name)
    {
        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }
        
        if (static::$app) {
            return static::$resolvedInstance[$name] = static::$app[$name];
        }
    }
    
    /**
     * Handle dynamic, static calls to the object
     */
    public static function __callStatic(string $method, array $args)
    {
        $instance = static::resolveFacadeInstance(
            static::getFacadeAccessor()
        );
        
        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }
        
        return $instance->$method(...$args);
    }
}
```

## Creating Facades

### Step 1: Create the Underlying Class

```php
<?php

namespace App\Services;

class PaymentService
{
    public function __construct(
        private PaymentGateway $gateway,
        private Logger $logger
    ) {}
    
    public function charge(float $amount, string $token): Payment
    {
        $this->logger->info("Charging ${amount}");
        
        return $this->gateway->charge($amount, $token);
    }
    
    public function refund(string $paymentId, float $amount): Refund
    {
        $this->logger->info("Refunding ${amount} for payment {$paymentId}");
        
        return $this->gateway->refund($paymentId, $amount);
    }
}
```

### Step 2: Register in Container

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use App\Services\PaymentService;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('payment', function ($app) {
            return new PaymentService(
                $app->make(PaymentGateway::class),
                $app->make(Logger::class)
            );
        });
    }
}
```

### Step 3: Create the Facade

```php
<?php

namespace App\Facades;

use NeoPhp\Support\Facade;

/**
 * @method static Payment charge(float $amount, string $token)
 * @method static Refund refund(string $paymentId, float $amount)
 * 
 * @see \App\Services\PaymentService
 */
class Payment extends Facade
{
    /**
     * Get the registered name of the component
     */
    protected static function getFacadeAccessor(): string
    {
        return 'payment';
    }
}
```

### Step 4: Use the Facade

```php
use App\Facades\Payment;

// Simple and expressive!
$payment = Payment::charge(99.99, $paymentToken);

$refund = Payment::refund($payment->id, 50.00);
```

## Built-in Facades

NeoFramework includes several built-in facades:

### Cache Facade

```php
use NeoPhp\Facades\Cache;

// Store data
Cache::put('key', 'value', 3600);

// Retrieve data
$value = Cache::get('key');
$value = Cache::get('key', 'default');

// Check existence
if (Cache::has('key')) {
    // Key exists
}

// Remember (get or set)
$users = Cache::remember('users.all', 3600, function () {
    return User::all();
});

// Forever
Cache::forever('setting', 'value');

// Forget
Cache::forget('key');
Cache::flush();

// Increment/Decrement
Cache::increment('counter');
Cache::decrement('counter', 5);
```

### DB Facade

```php
use NeoPhp\Facades\DB;

// Query builder
$users = DB::table('users')
    ->where('active', true)
    ->orderBy('name')
    ->get();

// Raw queries
$results = DB::select('SELECT * FROM users WHERE id = ?', [1]);

DB::insert('INSERT INTO users (name, email) VALUES (?, ?)', ['John', 'john@example.com']);

DB::update('UPDATE users SET active = ? WHERE id = ?', [true, 1]);

DB::delete('DELETE FROM users WHERE id = ?', [1]);

// Transactions
DB::transaction(function () {
    DB::table('users')->update(['active' => false]);
    DB::table('posts')->delete();
});

// Connection management
$mysqlUsers = DB::connection('mysql')->table('users')->get();
$pgsqlUsers = DB::connection('pgsql')->table('users')->get();
```

### Log Facade

```php
use NeoPhp\Facades\Log;

// Logging levels
Log::emergency('System is down!');
Log::alert('Database connection lost');
Log::critical('Application error');
Log::error('Failed to process payment', ['order_id' => 123]);
Log::warning('Cache miss for key: users.all');
Log::notice('User logged in', ['user_id' => 1]);
Log::info('Order created', ['order_id' => 456]);
Log::debug('Query executed', ['sql' => 'SELECT * FROM users']);

// Context
Log::info('User action', [
    'user_id' => auth()->id(),
    'action' => 'profile_updated',
    'ip' => request()->ip(),
]);
```

### Event Facade

```php
use NeoPhp\Facades\Event;

// Dispatch events
Event::dispatch(new UserRegistered($user));
Event::dispatch(new OrderPlaced($order));

// Listen to events
Event::listen(UserRegistered::class, function ($event) {
    Mail::to($event->user)->send(new WelcomeEmail());
});

// Listen with class
Event::listen(OrderPlaced::class, SendOrderConfirmation::class);

// Wildcard listeners
Event::listen('user.*', function ($eventName, array $data) {
    Log::info("Event: {$eventName}");
});
```

### Config Facade

```php
use NeoPhp\Facades\Config;

// Get config
$appName = Config::get('app.name');
$debug = Config::get('app.debug', false);

// Set config at runtime
Config::set('app.locale', 'es');

// Check if config exists
if (Config::has('services.stripe')) {
    // Stripe configured
}

// Get all config
$allConfig = Config::all();
```

### Route Facade

```php
use NeoPhp\Facades\Route;

// Define routes
Route::get('/', [HomeController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);

// Route groups
Route::group(['prefix' => 'api', 'middleware' => 'api'], function () {
    Route::get('/users', [ApiUserController::class, 'index']);
    Route::post('/users', [ApiUserController::class, 'store']);
});

// Named routes
Route::get('/profile', [ProfileController::class, 'show'])->name('profile');

// Generate URLs
$url = Route::url('profile');
$url = Route::url('users.show', ['id' => 1]);
```

### View Facade

```php
use NeoPhp\Facades\View;

// Render views
return View::make('welcome', ['name' => 'John']);

// Check if view exists
if (View::exists('emails.welcome')) {
    return View::make('emails.welcome');
}

// Share data with all views
View::share('appName', config('app.name'));

// View composers
View::composer('profile', ProfileComposer::class);

View::composer('*', function ($view) {
    $view->with('currentUser', auth()->user());
});
```

## Real-World Examples 🌍

### Building a Blog API

```php
<?php

namespace App\Controllers\Api;

use App\Facades\Cache;
use App\Facades\DB;
use App\Facades\Log;
use NeoPhp\Http\Request;

class PostController
{
    public function index()
    {
        // Cache posts list
        $posts = Cache::remember('api.posts.index', 3600, function () {
            return DB::table('posts')
                ->where('published', true)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        });
        
        Log::info('API: Posts index requested', [
            'count' => $posts->count(),
            'page' => request()->query('page', 1),
        ]);
        
        return response()->json($posts);
    }
    
    public function show(int $id)
    {
        $post = Cache::remember("api.post.{$id}", 3600, function () use ($id) {
            $post = DB::table('posts')
                ->where('id', $id)
                ->where('published', true)
                ->first();
            
            if (!$post) {
                return null;
            }
            
            // Increment view count
            DB::table('posts')
                ->where('id', $id)
                ->increment('views');
            
            return $post;
        });
        
        if (!$post) {
            Log::warning("API: Post not found", ['id' => $id]);
            return response()->json(['error' => 'Post not found'], 404);
        }
        
        Log::info("API: Post viewed", ['id' => $id]);
        
        return response()->json($post);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'tags' => 'array',
        ]);
        
        $post = DB::transaction(function () use ($validated) {
            $post = DB::table('posts')->insert([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'author_id' => auth()->id(),
                'published' => false,
                'created_at' => now(),
            ]);
            
            if (!empty($validated['tags'])) {
                foreach ($validated['tags'] as $tag) {
                    DB::table('post_tags')->insert([
                        'post_id' => $post->id,
                        'tag' => $tag,
                    ]);
                }
            }
            
            return $post;
        });
        
        // Clear caches
        Cache::forget('api.posts.index');
        Cache::tags('posts')->flush();
        
        Log::info("API: Post created", [
            'id' => $post->id,
            'author_id' => auth()->id(),
        ]);
        
        Event::dispatch(new PostCreated($post));
        
        return response()->json($post, 201);
    }
}
```

### File Upload Service

```php
<?php

namespace App\Services;

class FileUploadService
{
    public function upload($file, string $disk = 'public'): string
    {
        Log::info('File upload started', [
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);
        
        // Validate
        if ($file->getSize() > 5242880) { // 5MB
            Log::warning('File too large', ['size' => $file->getSize()]);
            throw new ValidationException('File size exceeds 5MB limit');
        }
        
        // Generate unique filename
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = date('Y/m/d') . '/' . $filename;
        
        // Store file
        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));
        
        // Cache file metadata
        Cache::put("file.{$path}", [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_at' => now(),
        ], 86400);
        
        Log::info('File uploaded successfully', [
            'path' => $path,
            'disk' => $disk,
        ]);
        
        Event::dispatch(new FileUploaded($path, $disk));
        
        return $path;
    }
    
    public function delete(string $path, string $disk = 'public'): bool
    {
        if (!Storage::disk($disk)->exists($path)) {
            Log::warning('File not found for deletion', ['path' => $path]);
            return false;
        }
        
        Storage::disk($disk)->delete($path);
        Cache::forget("file.{$path}");
        
        Log::info('File deleted', ['path' => $path, 'disk' => $disk]);
        
        return true;
    }
}
```

### Analytics Service

```php
<?php

namespace App\Services;

class AnalyticsService
{
    public function trackPageView(string $url, ?int $userId = null): void
    {
        $data = [
            'url' => $url,
            'user_id' => $userId,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referer' => request()->header('referer'),
            'viewed_at' => now(),
        ];
        
        // Store in database
        DB::table('analytics_pageviews')->insert($data);
        
        // Increment cached counter
        Cache::increment("pageviews.{$url}");
        Cache::increment('pageviews.total');
        
        Log::debug('Page view tracked', $data);
    }
    
    public function trackEvent(string $event, array $properties = []): void
    {
        $data = array_merge([
            'event' => $event,
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'tracked_at' => now(),
        ], $properties);
        
        DB::table('analytics_events')->insert($data);
        
        Cache::increment("events.{$event}");
        
        Log::debug("Event tracked: {$event}", $properties);
        
        Event::dispatch(new AnalyticsEventTracked($event, $properties));
    }
    
    public function getPopularPages(int $limit = 10): array
    {
        return Cache::remember('analytics.popular_pages', 3600, function () use ($limit) {
            return DB::table('analytics_pageviews')
                ->select('url', DB::raw('COUNT(*) as views'))
                ->where('viewed_at', '>', now()->subDays(7))
                ->groupBy('url')
                ->orderBy('views', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }
}
```

## Facade vs Dependency Injection 🤔

### When to Use Facades

Facades are great for:
- **Quick prototyping** - Fast development
- **Simple operations** - Single method calls
- **Static-like APIs** - Natural PHP syntax
- **Global utilities** - Logging, caching, config

```php
// Facade - concise for simple operations
Cache::put('key', 'value', 3600);
Log::info('User logged in');
Config::get('app.name');
```

### When to Use Dependency Injection

DI is better for:
- **Complex services** - Multiple method calls
- **Testability** - Easier to mock
- **Type safety** - IDE autocomplete
- **Clear dependencies** - Explicit requirements

```php
// DI - better for complex services
class OrderService
{
    public function __construct(
        private OrderRepository $orders,
        private PaymentGateway $payment,
        private EmailService $email
    ) {}
    
    public function createOrder(array $data): Order
    {
        // Multiple operations with injected services
        $order = $this->orders->create($data);
        $this->payment->charge($order);
        $this->email->sendConfirmation($order);
        return $order;
    }
}
```

### Hybrid Approach

Use both where appropriate:

```php
class ReportService
{
    // Inject complex dependencies
    public function __construct(
        private ReportGenerator $generator,
        private ReportRepository $reports
    ) {}
    
    public function generate(string $type): Report
    {
        // Use facades for simple utilities
        Log::info("Generating {$type} report");
        
        $report = $this->generator->generate($type);
        $this->reports->save($report);
        
        Cache::put("report.{$report->id}", $report, 3600);
        
        Event::dispatch(new ReportGenerated($report));
        
        return $report;
    }
}
```

## Testing with Facades 🧪

### Facade Mocking

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Facades\Cache;
use App\Facades\Log;

class UserServiceTest extends TestCase
{
    public function test_get_user_caches_result(): void
    {
        // Mock Cache facade
        Cache::shouldReceive('remember')
            ->once()
            ->with('user.1', 3600, \Closure::class)
            ->andReturn(new User(['id' => 1, 'name' => 'John']));
        
        $service = new UserService();
        $user = $service->getUser(1);
        
        $this->assertEquals('John', $user->name);
    }
    
    public function test_create_user_logs_event(): void
    {
        // Mock Log facade
        Log::shouldReceive('info')
            ->once()
            ->with('User created', ['user_id' => 1]);
        
        $service = new UserService();
        $service->createUser(['name' => 'John']);
    }
}
```

### Fake Facades

```php
public function test_file_upload_stores_file(): void
{
    // Fake Storage facade
    Storage::fake('public');
    
    $file = UploadedFile::fake()->image('avatar.jpg');
    
    $service = new FileUploadService();
    $path = $service->upload($file);
    
    // Assert file was stored
    Storage::disk('public')->assertExists($path);
}

public function test_event_is_dispatched(): void
{
    // Fake Event facade
    Event::fake();
    
    $service = new UserService();
    $service->registerUser(['email' => 'john@example.com']);
    
    // Assert event was dispatched
    Event::assertDispatched(UserRegistered::class);
}
```

## Best Practices 📋

### 1. Document Facade Methods

Use PHPDoc to enable IDE autocomplete:

```php
/**
 * @method static void put(string $key, mixed $value, int $seconds)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool has(string $key)
 * @method static bool forget(string $key)
 * 
 * @see \App\Services\CacheService
 */
class Cache extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cache';
    }
}
```

### 2. Keep Facades Simple

Facades work best for simple, stateless operations:

```php
// ✅ Good: Simple operations
Cache::get('key');
Log::info('message');
Config::get('app.name');

// ⚠️ Consider DI: Complex, stateful operations
$orderService = app(OrderService::class);
$orderService->setPaymentGateway('stripe');
$orderService->setShippingMethod('express');
$order = $orderService->process($data);
```

### 3. Don't Overuse Facades

```php
// ❌ Facade overuse
class Service
{
    public function doSomething()
    {
        Cache::put('key', 'value', 3600);
        Log::info('Doing something');
        DB::table('users')->insert([]);
        Event::dispatch(new Something());
        // Too many static calls
    }
}

// ✅ Better: Inject what you need
class Service
{
    public function __construct(
        private Cache $cache,
        private Logger $logger,
        private UserRepository $users,
        private EventDispatcher $events
    ) {}
    
    public function doSomething()
    {
        $this->cache->put('key', 'value', 3600);
        $this->logger->info('Doing something');
        $this->users->create([]);
        $this->events->dispatch(new Something());
    }
}
```

### 4. Use Real-Time Facades Sparingly

Real-time facades create facades on-the-fly:

```php
// Real-time facade (prepend Facades\ to namespace)
use Facades\App\Services\PaymentService;

PaymentService::charge(99.99, $token);

// Only use when:
// - Quick prototyping
// - One-off usage
// - Testing ideas
```

## Performance Considerations ⚡

Facades have minimal performance overhead:

```php
// Facade call
Cache::get('key');

// Equivalent to:
app('cache')->get('key');

// The facade just adds one extra method call
```

For high-performance scenarios, consider caching facade root:

```php
// Cache the resolved instance
$cache = app('cache');

// Reuse cached instance
$cache->get('key1');
$cache->get('key2');
$cache->get('key3');
```

## Related Documentation

- [Service Container](container.md) - Understanding the container
- [Dependency Injection](dependency-injection.md) - DI patterns
- [Service Providers](introduction.md) - Registering services
- [Testing](../testing/getting-started.md) - Testing with facades

## Next Steps

Continue learning:

1. **[Service Container](container.md)** - Master the IoC container
2. **[Dependency Injection](dependency-injection.md)** - DI best practices
3. **[Testing](../testing/getting-started.md)** - Write testable code
4. **[Helpers](../reference/helpers.md)** - Global helper functions
