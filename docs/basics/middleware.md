# Middleware

## Introduction

Middleware provide a convenient mechanism for filtering HTTP requests entering your application. For example, NeoFramework includes middleware that verifies your application's user is authenticated. If the user is not authenticated, the middleware will redirect them to the login screen. However, if the user is authenticated, the middleware will allow the request to proceed further into the application.

Middleware can be written to perform a variety of tasks besides authentication. For example: a CORS middleware might be responsible for adding proper headers to all responses leaving your application, a logging middleware might log all incoming requests, and a rate limiting middleware might prevent certain users from making too many requests.

## Defining Middleware

### Creating Middleware

Create middleware using the CLI:

```bash
php neo make:middleware CheckAge
```

This creates `app/Middleware/CheckAge.php`:

```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Http\Request;

class CheckAge
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->age <= 18) {
            return redirect('home');
        }

        return $next($request);
    }
}
```

### Before & After Middleware

Whether middleware runs before or after a request depends on the middleware itself:

**Before Middleware:**
```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Http\Request;

class BeforeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Perform action before request
        
        return $next($request);
    }
}
```

**After Middleware:**
```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Http\Request;

class AfterMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Perform action after request
        
        return $response;
    }
}
```

## Registering Middleware

### Global Middleware

If you want middleware to run during every HTTP request, list it in `app/Http/Kernel.php`:

```php
<?php

namespace App\Http;

use NeoPhp\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Global HTTP middleware stack.
     */
    protected array $middleware = [
        \NeoPhp\Http\Middleware\TrustProxies::class,
        \NeoPhp\Http\Middleware\CheckForMaintenanceMode::class,
        \NeoPhp\Http\Middleware\ValidatePostSize::class,
        \NeoPhp\Http\Middleware\TrimStrings::class,
        \NeoPhp\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];
}
```

### Middleware Groups

Middleware groups allow you to group several middleware under a single key:

```php
protected array $middlewareGroups = [
    'web' => [
        \NeoPhp\Http\Middleware\EncryptCookies::class,
        \NeoPhp\Http\Middleware\AddQueuedCookiesToResponse::class,
        \NeoPhp\Session\Middleware\StartSession::class,
        \NeoPhp\View\Middleware\ShareErrorsFromSession::class,
        \NeoPhp\Http\Middleware\VerifyCsrfToken::class,
    ],

    'api' => [
        'throttle:60,1',
        \NeoPhp\Http\Middleware\ParseJsonBody::class,
    ],
];
```

### Route Middleware

Assign middleware to specific routes:

```php
protected array $routeMiddleware = [
    'auth' => \App\Middleware\Authenticate::class,
    'auth.basic' => \NeoPhp\Auth\Middleware\AuthenticateWithBasicAuth::class,
    'guest' => \App\Middleware\RedirectIfAuthenticated::class,
    'verified' => \NeoPhp\Auth\Middleware\EnsureEmailIsVerified::class,
    'throttle' => \NeoPhp\Http\Middleware\ThrottleRequests::class,
    'role' => \App\Middleware\CheckRole::class,
];
```

## Assigning Middleware to Routes

### Single Middleware

```php
Route::get('/profile', [ProfileController::class, 'show'])
    ->middleware('auth');
```

### Multiple Middleware

```php
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware(['auth', 'verified']);
```

### Middleware Groups

```php
Route::middleware(['web'])->group(function () {
    Route::get('/', [HomeController::class, 'index']);
});
```

### Excluding Middleware

```php
Route::get('/api/user', function () {
    //
})->withoutMiddleware([VerifyCsrfToken::class]);
```

## Middleware Parameters

Middleware can receive additional parameters:

```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!$request->user()->hasRole($role)) {
            abort(403);
        }

        return $next($request);
    }
}
```

Use in routes:

```php
Route::get('/admin/users', [UserController::class, 'index'])
    ->middleware('role:admin');

// Multiple parameters
Route::get('/posts/{id}', [PostController::class, 'show'])
    ->middleware('check:editor,admin');
```

## Terminable Middleware

Sometimes middleware may need to do work after the response has been sent to the browser:

```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Http\Request;
use NeoPhp\Http\Response;

class LogRequest
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response)
    {
        // Log request after response is sent
        \Log::info('Request processed', [
            'url' => $request->url(),
            'method' => $request->method(),
            'status' => $response->status(),
        ]);
    }
}
```

## Practical Examples

### Example 1: Authentication Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Http\Request;
use NeoPhp\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next, string $guard = null)
    {
        if (!Auth::guard($guard)->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
```

### Example 2: CORS Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Handle preflight request
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
                ->header('Access-Control-Max-Age', '3600');
        }

        $response = $next($request);

        return $response
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
```

### Example 3: Rate Limiting Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Http\Request;
use NeoPhp\Cache\RateLimiter;

class ThrottleRequests
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many requests'
            ], 429);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->limiter->retriesLeft($key, $maxAttempts)
        );
    }

    protected function resolveRequestSignature(Request $request): string
    {
        if ($user = $request->user()) {
            return 'throttle:'.$user->id;
        }

        return 'throttle:'.$request->ip();
    }

    protected function addHeaders($response, int $maxAttempts, int $remainingAttempts)
    {
        return $response
            ->header('X-RateLimit-Limit', $maxAttempts)
            ->header('X-RateLimit-Remaining', $remainingAttempts);
    }
}
```

### Example 4: Request Logging Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Http\Request;
use NeoPhp\Http\Response;
use NeoPhp\Support\Facades\Log;

class LogRequests
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        $response = $next($request);

        return $response;
    }

    public function terminate(Request $request, Response $response)
    {
        $duration = microtime(true) - LARAVEL_START;

        Log::info('HTTP Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'status' => $response->status(),
            'duration' => round($duration * 1000, 2).'ms',
        ]);
    }
}
```

### Example 5: Force HTTPS Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Http\Request;

class ForceHttps
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->secure() && app()->environment('production')) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
```

### Example 6: Locale Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->segment(1);

        if (in_array($locale, ['en', 'th', 'ja'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
```

### Example 7: Check Subscription Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Http\Request;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user->hasActiveSubscription()) {
            return redirect()->route('subscription.expired');
        }

        if ($user->subscription->isExpiringSoon()) {
            session()->flash('warning', 'Your subscription expires soon!');
        }

        return $next($request);
    }
}
```

## Middleware Priority

Sometimes you may want to specify the order in which middleware should run:

```php
protected array $middlewarePriority = [
    \NeoPhp\Session\Middleware\StartSession::class,
    \NeoPhp\View\Middleware\ShareErrorsFromSession::class,
    \App\Middleware\Authenticate::class,
    \NeoPhp\Http\Middleware\VerifyCsrfToken::class,
];
```

## Testing Middleware

```php
<?php

namespace Tests\Middleware;

use App\Middleware\CheckAge;
use NeoPhp\Http\Request;
use Tests\TestCase;

class CheckAgeTest extends TestCase
{
    public function test_redirects_if_underage()
    {
        $request = Request::create('/test', 'GET');
        $request->age = 15;

        $middleware = new CheckAge();
        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(302, $response->status());
    }

    public function test_allows_if_of_age()
    {
        $request = Request::create('/test', 'GET');
        $request->age = 21;

        $middleware = new CheckAge();
        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }
}
```

## Best Practices

### 1. Keep Middleware Focused

Each middleware should have a single responsibility:

**Good:**
```php
class Authenticate { /* Only handles authentication */ }
class CheckRole { /* Only checks roles */ }
```

**Bad:**
```php
class AuthenticateAndCheckRole { /* Does too much */ }
```

### 2. Use Dependency Injection

```php
public function __construct(
    private UserRepository $users,
    private CacheInterface $cache
) {}
```

### 3. Return Early

```php
public function handle(Request $request, Closure $next)
{
    if (!$condition) {
        return redirect('/error');
    }

    return $next($request);
}
```

### 4. Use Terminable Middleware for Cleanup

```php
public function terminate(Request $request, Response $response)
{
    // Cleanup after response is sent
}
```

### 5. Document Middleware Parameters

```php
/**
 * Check if user has the given role.
 *
 * @param string $role The role to check (admin, editor, etc.)
 */
public function handle(Request $request, Closure $next, string $role)
```

## Next Steps

- [Requests](requests.md) - Working with HTTP requests
- [Responses](responses.md) - Returning HTTP responses
- [Routing](routing.md) - Route definition and middleware
- [CSRF Protection](../security/csrf.md) - CSRF middleware
