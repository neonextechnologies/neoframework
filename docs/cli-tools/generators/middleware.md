# 🛡️ Middleware Generator

Generate middleware classes for filtering HTTP requests in your NeoFramework application. Middleware provides a convenient mechanism for filtering requests entering your application.

## 📋 Table of Contents

- [Basic Usage](#basic-usage)
- [Generated Code](#generated-code)
- [Middleware Types](#middleware-types)
- [Registration](#registration)
- [Advanced Examples](#advanced-examples)
- [Best Practices](#best-practices)

## 🚀 Basic Usage

### Generate Basic Middleware

```bash
php neo make:middleware CheckUserRole
```

**Generated:** `app/Middleware/CheckUserRole.php`

```php
<?php

namespace App\Middleware;

use Closure;
use Neo\Http\Request;
use Neo\Http\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Neo\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Perform action before the request is handled

        $response = $next($request);

        // Perform action after the request is handled

        return $response;
    }
}
```

## 📝 Generated Code Examples

### Authentication Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Neo\Http\Request;
use Neo\Http\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Neo\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect('/login')
                ->with('error', 'Please login to continue');
        }

        return $next($request);
    }
}
```

### Role-Based Authorization Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Neo\Http\Request;
use Neo\Http\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Neo\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Check if user has required role
        if (!auth()->user()->hasRole($role)) {
            abort(403, 'Unauthorized action');
        }

        return $next($request);
    }
}
```

### Request Throttling Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Neo\Http\Request;
use Neo\Http\Response;
use Neo\Support\Facades\RateLimiter;

class ThrottleRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Neo\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return mixed
     */
    public function handle(
        Request $request, 
        Closure $next, 
        int $maxAttempts = 60, 
        int $decayMinutes = 1
    ): Response
    {
        $key = $this->resolveRequestSignature($request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            
            return response()->json([
                'message' => 'Too many requests',
                'retry_after' => $retryAfter,
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            RateLimiter::retriesLeft($key, $maxAttempts)
        );
    }

    /**
     * Resolve the request signature.
     *
     * @param  \Neo\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request): string
    {
        if ($user = $request->user()) {
            return sha1($user->id);
        }

        return sha1($request->ip());
    }

    /**
     * Add rate limit headers to response.
     *
     * @param  \Neo\Http\Response  $response
     * @param  int  $maxAttempts
     * @param  int  $remainingAttempts
     * @return \Neo\Http\Response
     */
    protected function addHeaders(
        Response $response, 
        int $maxAttempts, 
        int $remainingAttempts
    ): Response
    {
        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ]);
    }
}
```

### Input Sanitization Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Neo\Http\Request;
use Neo\Http\Response;

class SanitizeInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Neo\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sanitize all input
        $input = $this->sanitize($request->all());
        
        // Replace request input
        $request->replace($input);

        return $next($request);
    }

    /**
     * Sanitize the input data.
     *
     * @param  array  $data
     * @return array
     */
    protected function sanitize(array $data): array
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return $this->sanitize($value);
            }

            if (is_string($value)) {
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }

            return $value;
        }, $data);
    }
}
```

### CORS Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Neo\Http\Request;
use Neo\Http\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Neo\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight request
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->withHeaders($this->getCorsHeaders());
        }

        $response = $next($request);

        return $response->withHeaders($this->getCorsHeaders());
    }

    /**
     * Get CORS headers.
     *
     * @return array
     */
    protected function getCorsHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin' => config('cors.allowed_origins', '*'),
            'Access-Control-Allow-Methods' => config('cors.allowed_methods', 'GET, POST, PUT, DELETE, OPTIONS'),
            'Access-Control-Allow-Headers' => config('cors.allowed_headers', 'Content-Type, Authorization, X-Requested-With'),
            'Access-Control-Allow-Credentials' => config('cors.allow_credentials', 'true'),
            'Access-Control-Max-Age' => config('cors.max_age', '3600'),
        ];
    }
}
```

### Logging Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Neo\Http\Request;
use Neo\Http\Response;
use Neo\Support\Facades\Log;

class LogRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Neo\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Log request
        Log::info('Request started', [
            'method' => $request->method(),
            'uri' => $request->path(),
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
        ]);

        $response = $next($request);

        // Log response
        $executionTime = microtime(true) - $startTime;
        
        Log::info('Request completed', [
            'method' => $request->method(),
            'uri' => $request->path(),
            'status' => $response->getStatusCode(),
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
        ]);

        return $response;
    }
}
```

### API Version Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Neo\Http\Request;
use Neo\Http\Response;

class ApiVersion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Neo\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $version
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $version): Response
    {
        // Set API version in request
        $request->merge(['api_version' => $version]);

        $response = $next($request);

        // Add version header to response
        return $response->withHeaders([
            'X-API-Version' => $version,
        ]);
    }
}
```

## 🔧 Registration

### Global Middleware

Register middleware that runs on every request.

**In `bootstrap/app.php`:**

```php
<?php

use Neo\Foundation\Application;

$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

// Register global middleware
$app->middleware([
    \App\Middleware\CorsMiddleware::class,
    \App\Middleware\LogRequest::class,
    \App\Middleware\SanitizeInput::class,
]);

return $app;
```

### Route Middleware

Register middleware for specific routes.

**In `bootstrap/app.php`:**

```php
<?php

use Neo\Foundation\Application;

$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

// Register route middleware
$app->routeMiddleware([
    'auth' => \App\Middleware\AuthMiddleware::class,
    'role' => \App\Middleware\CheckRole::class,
    'throttle' => \App\Middleware\ThrottleRequests::class,
    'verified' => \App\Middleware\EnsureEmailIsVerified::class,
    'api.version' => \App\Middleware\ApiVersion::class,
]);

return $app;
```

### Middleware Groups

Group middleware for common use cases.

**In `bootstrap/app.php`:**

```php
<?php

use Neo\Foundation\Application;

$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

// Register middleware groups
$app->middlewareGroups([
    'web' => [
        \App\Middleware\EncryptCookies::class,
        \App\Middleware\AddQueuedCookiesToResponse::class,
        \App\Middleware\StartSession::class,
        \App\Middleware\VerifyCsrfToken::class,
    ],
    'api' => [
        \App\Middleware\ThrottleRequests::class . ':60,1',
        \App\Middleware\ApiVersion::class . ':v1',
    ],
]);

return $app;
```

### Using Middleware in Routes

```php
// routes/web.php

// Single middleware
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth');

// Multiple middleware
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware(['auth', 'role:admin']);

// Middleware with parameters
Route::post('/api/posts', [PostController::class, 'store'])
    ->middleware('throttle:10,1');

// Middleware group
Route::middleware(['web'])->group(function () {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/about', [HomeController::class, 'about']);
});

// Multiple groups
Route::middleware(['web', 'auth'])->group(function () {
    Route::resource('posts', PostController::class);
});
```

### Using Middleware in Controllers

```php
<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Neo\Http\Request;
use Neo\Http\Response;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Apply to all methods
        $this->middleware('auth');

        // Apply to specific methods
        $this->middleware('role:admin')->only(['destroy', 'edit']);

        // Apply except specific methods
        $this->middleware('throttle:60,1')->except(['index', 'show']);

        // Apply with parameters
        $this->middleware('role:admin,moderator')->only(['approve']);
    }

    public function index(Request $request): Response
    {
        // Method logic
    }
}
```

## 🎯 Advanced Examples

### Permission Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Neo\Http\Request;
use Neo\Http\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Neo\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this resource');
    }
}
```

**Usage:**

```php
Route::get('/posts/create', [PostController::class, 'create'])
    ->middleware('permission:create-post');

Route::delete('/users/{id}', [UserController::class, 'destroy'])
    ->middleware('permission:delete-user,manage-users');
```

### Maintenance Mode Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Neo\Http\Request;
use Neo\Http\Response;

class CheckForMaintenanceMode
{
    /**
     * The URIs that should be accessible during maintenance mode.
     *
     * @var array
     */
    protected $except = [
        '/admin/*',
        '/api/status',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Neo\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isDownForMaintenance()) {
            if ($this->inExceptArray($request)) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Service Unavailable',
                ], 503);
            }

            return response()->view('errors.maintenance', [], 503);
        }

        return $next($request);
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    protected function isDownForMaintenance(): bool
    {
        return file_exists(storage_path('framework/down'));
    }

    /**
     * Determine if the request has a URI that should be accessible.
     *
     * @param  \Neo\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray(Request $request): bool
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
```

### JSON Response Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Neo\Http\Request;
use Neo\Http\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Neo\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force JSON response
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // Ensure response is JSON
        if (!$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
```

### Language Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Neo\Http\Request;
use Neo\Http\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Neo\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for locale in various places
        $locale = $this->getLocale($request);

        if ($locale && $this->isValidLocale($locale)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Get the locale from request.
     *
     * @param  \Neo\Http\Request  $request
     * @return string|null
     */
    protected function getLocale(Request $request): ?string
    {
        // Check query parameter
        if ($request->has('lang')) {
            return $request->input('lang');
        }

        // Check header
        if ($request->hasHeader('Accept-Language')) {
            return substr($request->header('Accept-Language'), 0, 2);
        }

        // Check session
        if ($request->session()->has('locale')) {
            return $request->session()->get('locale');
        }

        // Check user preference
        if ($user = $request->user()) {
            return $user->locale;
        }

        return null;
    }

    /**
     * Determine if locale is valid.
     *
     * @param  string  $locale
     * @return bool
     */
    protected function isValidLocale(string $locale): bool
    {
        return in_array($locale, config('app.available_locales', ['en']));
    }
}
```

### Cache Response Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Neo\Http\Request;
use Neo\Http\Response;
use Neo\Support\Facades\Cache;

class CacheResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Neo\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $minutes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $minutes = 60): Response
    {
        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        $key = $this->getCacheKey($request);

        // Return cached response if available
        if ($cached = Cache::get($key)) {
            return response($cached['content'])
                ->withHeaders($cached['headers'])
                ->header('X-Cache', 'HIT');
        }

        $response = $next($request);

        // Cache successful responses
        if ($response->isSuccessful()) {
            Cache::put($key, [
                'content' => $response->getContent(),
                'headers' => $response->headers->all(),
            ], now()->addMinutes($minutes));
        }

        return $response->header('X-Cache', 'MISS');
    }

    /**
     * Get cache key for request.
     *
     * @param  \Neo\Http\Request  $request
     * @return string
     */
    protected function getCacheKey(Request $request): string
    {
        return 'response:' . md5($request->fullUrl());
    }
}
```

## 🎯 Best Practices

### Keep Middleware Focused

```php
// Good: Single responsibility
class AuthMiddleware
{
    public function handle($request, $next)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }
        return $next($request);
    }
}

// Bad: Too many responsibilities
class SecurityMiddleware
{
    public function handle($request, $next)
    {
        // Check authentication
        // Check authorization
        // Validate CSRF
        // Check rate limiting
        // Log request
        // Etc...
    }
}
```

### Use Parameters for Flexibility

```php
class CheckRole
{
    public function handle($request, $next, string ...$roles)
    {
        if (!auth()->user()->hasAnyRole($roles)) {
            abort(403);
        }
        return $next($request);
    }
}

// Usage
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('role:admin,super-admin');
```

### Handle Both Before and After Logic

```php
class LogRequest
{
    public function handle($request, $next)
    {
        // Before logic
        Log::info('Request started', ['uri' => $request->path()]);
        
        $response = $next($request);
        
        // After logic
        Log::info('Request completed', ['status' => $response->status()]);
        
        return $response;
    }
}
```

### Use Dependency Injection

```php
class RateLimitMiddleware
{
    public function __construct(
        protected RateLimiter $limiter,
        protected ResponseFactory $response
    ) {}
    
    public function handle($request, $next, $max = 60)
    {
        if ($this->limiter->tooManyAttempts($key, $max)) {
            return $this->response->json(['error' => 'Too many requests'], 429);
        }
        
        return $next($request);
    }
}
```

## 📚 Related Documentation

- [Middleware](../basics/middleware.md) - Complete middleware documentation
- [Routing](../basics/routing.md) - Route registration
- [Requests](../basics/requests.md) - HTTP requests
- [Responses](../basics/responses.md) - HTTP responses

## 🔗 Quick Reference

```bash
# Generate middleware
php neo make:middleware CheckUserRole

# Force overwrite
php neo make:middleware CheckUserRole --force
```

**Common Middleware Patterns:**

```php
// Authentication
if (!auth()->check()) { return redirect('/login'); }

// Authorization
if (!$user->hasRole($role)) { abort(403); }

// Rate limiting
if (RateLimiter::tooManyAttempts($key, $max)) { abort(429); }

// CORS
return $response->withHeaders($corsHeaders);

// Logging
Log::info('Request', ['uri' => $request->path()]);
```
