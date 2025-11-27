# Request Lifecycle

## Introduction

Understanding how NeoFramework handles HTTP requests is essential for building efficient applications. This guide walks through the complete lifecycle of a request, from the moment it hits your server to when a response is sent back to the client.

## Lifecycle Overview

```
HTTP Request
    ↓
public/index.php
    ↓
Bootstrap Application
    ↓
Load Configuration
    ↓
Register Service Providers
    ↓
Boot Service Providers
    ↓
Route Request
    ↓
Run Middleware Stack
    ↓
Call Controller/Handler
    ↓
Generate Response
    ↓
Run Response Middleware
    ↓
Send Response
```

## Step-by-Step Breakdown

### 1. Entry Point

All requests enter through `public/index.php`:

```php
<?php

// public/index.php

// Load Composer autoloader
require __DIR__.'/../vendor/autoload.php';

// Bootstrap the application
$app = require_once __DIR__.'/../bootstrap/app.php';

// Handle the request
$response = $app->handle(
    $request = Request::capture()
);

// Send the response
$response->send();
```

### 2. Application Bootstrap

The `bootstrap/app.php` file creates the application instance:

```php
<?php

// bootstrap/app.php

use NeoPhp\Foundation\Application;

// Create application instance
$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

// Bind important interfaces
$app->singleton(
    Kernel::class,
    App\Http\Kernel::class
);

// Return application
return $app;
```

### 3. Load Configuration

The application loads all configuration files:

```php
// Load .env file
$app->loadEnvironmentFrom('.env');

// Load configuration from config/*.php
$app->loadConfiguration();

// Configuration is now accessible:
$debug = config('app.debug');
$database = config('database.default');
```

### 4. Register Service Providers

Service providers are registered from `config/app.php`:

```php
// config/app.php
'providers' => [
    // Framework providers
    NeoPhp\Database\DatabaseServiceProvider::class,
    NeoPhp\Auth\AuthServiceProvider::class,
    NeoPhp\Cache\CacheServiceProvider::class,
    
    // Application providers
    App\Providers\AppServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
]
```

The `register()` method is called on each provider:

```php
class AppServiceProvider extends ServiceProvider
{
    public function register(Container $container): void
    {
        // Bind services into container
        $container->singleton(UserRepository::class);
    }
}
```

### 5. Boot Service Providers

After all providers are registered, their `boot()` methods are called:

```php
class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load routes
        require base_path('routes/web.php');
        require base_path('routes/api.php');
    }
}
```

### 6. Capture Request

The incoming HTTP request is captured:

```php
$request = Request::capture();

// Request object contains:
// - $_GET, $_POST, $_COOKIE, $_FILES
// - HTTP headers
// - Request URI
// - HTTP method
// - Client IP address
```

### 7. Route Resolution

The router matches the request to a route:

```php
// routes/web.php
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// Router finds matching route:
$route = $router->match($request);
// Returns: ['UserController', 'show', ['id' => 123]]
```

### 8. Run Middleware Pipeline

Request passes through middleware stack:

```php
// app/Http/Kernel.php
protected array $middleware = [
    \NeoPhp\Http\Middleware\TrustProxies::class,
    \NeoPhp\Http\Middleware\ValidateCsrfToken::class,
    \App\Middleware\AuthMiddleware::class,
];

// Each middleware can:
// 1. Modify the request
// 2. Pass to next middleware
// 3. Return early response
// 4. Modify the response
```

Example middleware flow:

```php
class AuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Before controller
        if (!auth()->check()) {
            return redirect('/login');
        }
        
        // Call next middleware/controller
        $response = $next($request);
        
        // After controller
        $response->header('X-User-Id', auth()->id());
        
        return $response;
    }
}
```

### 9. Controller Execution

The controller method is called with dependency injection:

```php
class UserController extends Controller
{
    public function __construct(
        private UserRepository $users,
        private CacheInterface $cache
    ) {}
    
    public function show(int $id)
    {
        // Find user
        $user = $this->users->find($id);
        
        // Return response
        return new UserResource($user);
    }
}
```

### 10. Response Generation

The controller returns a response:

```php
// JSON response
return response()->json(['user' => $user]);

// View response
return view('users.show', compact('user'));

// API resource
return new UserResource($user);

// Redirect
return redirect('/users');

// Download
return response()->download($filePath);
```

### 11. Response Middleware

Response passes back through middleware:

```php
class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Add security headers to response
        $response->header('X-Frame-Options', 'SAMEORIGIN');
        $response->header('X-Content-Type-Options', 'nosniff');
        
        return $response;
    }
}
```

### 12. Send Response

Finally, the response is sent to the client:

```php
$response->send();

// This sends:
// - HTTP status code
// - Response headers
// - Response body
```

## Detailed Example: Complete Request Flow

Let's follow a real request through the entire lifecycle:

### Request: `GET /api/users/123`

**1. Entry Point:**
```php
// public/index.php
$app = require __DIR__.'/../bootstrap/app.php';
$response = $app->handle(Request::capture());
```

**2. Bootstrap:**
```php
$app = new Application(__DIR__);
$app->singleton(Kernel::class, App\Http\Kernel::class);
```

**3. Configuration:**
```php
// Load .env
// Load config/app.php, config/database.php, etc.
$app->loadConfiguration();
```

**4. Register Providers:**
```php
// DatabaseServiceProvider
$container->singleton(Database::class, fn() => new Database(config('database')));

// AuthServiceProvider
$container->singleton(AuthManager::class, fn() => new AuthManager());

// AppServiceProvider
$container->singleton(UserRepository::class);
```

**5. Boot Providers:**
```php
// RouteServiceProvider
require base_path('routes/api.php');
```

**6. Capture Request:**
```php
$request = Request::create('GET', '/api/users/123', [
    'Accept' => 'application/json',
    'Authorization' => 'Bearer token123'
]);
```

**7. Match Route:**
```php
// routes/api.php
Route::get('/api/users/{id}', [UserController::class, 'show']);

// Matched route:
$route = ['controller' => UserController::class, 'method' => 'show', 'params' => ['id' => 123]];
```

**8. Middleware (Before):**
```php
// TrustProxies - Handle proxy headers
$request->setTrustedProxies(['10.0.0.0/8']);

// CorsMiddleware - Add CORS headers
$request->header('Access-Control-Allow-Origin', '*');

// AuthMiddleware - Authenticate user
$user = auth()->guard('api')->user(); // Gets user from token
auth()->setUser($user);
```

**9. Controller:**
```php
class UserController extends Controller
{
    public function __construct(
        private UserRepository $users,
        private CacheInterface $cache
    ) {}
    
    public function show(int $id)
    {
        // Try cache first
        $user = $this->cache->remember("user.{$id}", 3600, function() use ($id) {
            return $this->users->find($id);
        });
        
        // Return API resource
        return new UserResource($user);
    }
}
```

**10. Generate Response:**
```php
return response()->json([
    'data' => [
        'id' => 123,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'created_at' => '2024-01-01T00:00:00Z'
    ]
], 200);
```

**11. Middleware (After):**
```php
// AddSecurityHeaders
$response->header('X-Frame-Options', 'SAMEORIGIN');
$response->header('X-Content-Type-Options', 'nosniff');

// CorsMiddleware
$response->header('Access-Control-Allow-Origin', '*');
$response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
```

**12. Send Response:**
```php
HTTP/1.1 200 OK
Content-Type: application/json
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
Access-Control-Allow-Origin: *

{
  "data": {
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-01-01T00:00:00Z"
  }
}
```

## Performance Considerations

### 1. Configuration Caching

In production, cache configuration to avoid loading files on every request:

```bash
php neo config:cache
```

This creates `bootstrap/cache/config.php` with all configuration merged.

### 2. Route Caching

Cache routes to skip route file parsing:

```bash
php neo route:cache
```

### 3. Optimize Autoloader

```bash
composer install --optimize-autoloader --no-dev
```

### 4. OPcache

Enable PHP OPcache in production:

```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
```

### 5. Deferred Service Providers

Mark service providers as deferred to load them only when needed:

```php
class ApiServiceProvider extends ServiceProvider
{
    protected bool $defer = true;
    
    public function provides(): array
    {
        return [GitHubClient::class, StripeClient::class];
    }
}
```

## Lifecycle Hooks

You can hook into various points of the lifecycle:

### Before Request

```php
// In service provider boot()
$app->before(function($request) {
    // Log all incoming requests
    logger()->info('Request', [
        'method' => $request->method(),
        'uri' => $request->uri(),
        'ip' => $request->ip()
    ]);
});
```

### After Request

```php
$app->after(function($request, $response) {
    // Log response time
    logger()->info('Response', [
        'status' => $response->status(),
        'duration' => microtime(true) - LARAVEL_START
    ]);
});
```

### Terminating Middleware

Some middleware need to run after the response is sent:

```php
class LogRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
    
    public function terminate(Request $request, Response $response): void
    {
        // Run after response is sent to client
        DB::table('request_logs')->insert([
            'method' => $request->method(),
            'uri' => $request->uri(),
            'status' => $response->status(),
            'duration' => microtime(true) - LARAVEL_START,
            'created_at' => now()
        ]);
    }
}
```

## Error Handling in Lifecycle

Exceptions at any point are caught and handled:

```php
try {
    $response = $app->handle($request);
} catch (NotFoundHttpException $e) {
    // 404 error
    $response = response()->json(['error' => 'Not Found'], 404);
} catch (ValidationException $e) {
    // Validation error
    $response = response()->json(['errors' => $e->errors()], 422);
} catch (Throwable $e) {
    // Any other error
    if (config('app.debug')) {
        // Show detailed error page
        $response = $app->renderException($e);
    } else {
        // Show generic error
        $response = response()->json(['error' => 'Server Error'], 500);
    }
}
```

## Next Steps

- [Service Container](container.md) - Dependency injection system
- [Service Providers](providers.md) - Application bootstrapping
- [Middleware](../basics/middleware.md) - HTTP middleware
- [Routing](../basics/routing.md) - Route registration and matching
