# Service Providers Introduction 📚

## Overview

Service providers are the backbone of NeoFramework's application architecture. They serve as the central place where you configure, register, and bootstrap all the components of your application. Every major feature of the framework, from routing to database connections, is bootstrapped through service providers.

If you want to understand how NeoFramework works under the hood, understanding service providers is essential. They are the "bootstrap" classes that tie everything together and make the framework functional.

## What Are Service Providers?

Service providers are classes responsible for:

1. **Binding services** into the service container
2. **Registering** routes, events, middleware, and other components
3. **Bootstrapping** functionality needed by your application
4. **Configuring** third-party packages and integrations

Think of service providers as the central configuration hub for your application. When NeoFramework boots, it automatically registers all configured service providers, which in turn register all the services your application needs.

## The Service Provider Lifecycle

Understanding the service provider lifecycle is crucial:

### 1. Registration Phase

During registration, **all** service providers' `register()` methods are called. This phase:
- Happens first, before any bootstrapping
- Should only bind things into the container
- Cannot use any services that haven't been registered yet
- Should not register routes, event listeners, or any other functionality

```php
public function register(): void
{
    // ✅ Bind services into container
    $this->app->singleton(PaymentGateway::class, StripeGateway::class);
    
    // ❌ Don't use other services here
    // $router = $this->app->make('router'); // Might not exist yet!
}
```

### 2. Boot Phase

After all providers are registered, the `boot()` method is called on each provider. This phase:
- Happens after all services are registered
- Can safely use any registered services
- Should register routes, events, middleware
- Can perform actions that depend on other services

```php
public function boot(): void
{
    // ✅ Now you can use other services
    $router = $this->app->make('router');
    $router->middleware('custom', CustomMiddleware::class);
    
    // ✅ Register routes
    require base_path('routes/web.php');
}
```

## Framework Service Providers

NeoFramework includes several core service providers:

### CoreServiceProvider

Registers core framework services:
- Container bindings
- Core helpers
- Foundation services

```php
<?php

namespace NeoPhp\Foundation\Providers;

use NeoPhp\Foundation\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerContainer();
        $this->registerConfig();
        $this->registerHelpers();
    }
    
    public function boot(): void
    {
        $this->loadCoreConfigurations();
    }
}
```

### DatabaseServiceProvider

Manages database connections and query builders:

```php
<?php

namespace NeoPhp\Database;

use NeoPhp\Foundation\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('db', function ($app) {
            return new DatabaseManager($app);
        });
    }
    
    public function boot(): void
    {
        // Configure connection
        // Set up event listeners
        // Register macros
    }
}
```

### RoutingServiceProvider

Handles routing and URL generation:

```php
<?php

namespace NeoPhp\Routing;

use NeoPhp\Foundation\ServiceProvider;

class RoutingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('router', function ($app) {
            return new Router($app);
        });
        
        $this->app->singleton('url', function ($app) {
            return new UrlGenerator($app);
        });
    }
    
    public function boot(): void
    {
        // Register route middlewares
        // Load route files
    }
}
```

### ViewServiceProvider

Manages the view rendering system:

```php
<?php

namespace NeoPhp\View;

use NeoPhp\Foundation\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('view', function ($app) {
            return new ViewFactory($app);
        });
    }
    
    public function boot(): void
    {
        // Register view composers
        // Share global data
    }
}
```

## Application Service Providers

Your application should include custom service providers in `app/Providers/`:

### AppServiceProvider

The default application service provider for general application bindings:

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services
     */
    public function register(): void
    {
        // Register application-wide services
        $this->app->singleton(ApplicationService::class);
    }

    /**
     * Bootstrap application services
     */
    public function boot(): void
    {
        // Bootstrap application
    }
}
```

### RouteServiceProvider

Handles route registration for your application:

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Routing\Router;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The controller namespace for the application
     */
    protected string $namespace = 'App\Controllers';

    /**
     * Register services
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services
     */
    public function boot(Router $router): void
    {
        $this->loadRoutes($router);
    }

    /**
     * Load the application routes
     */
    protected function loadRoutes(Router $router): void
    {
        // Web routes
        $router->group([
            'namespace' => $this->namespace,
            'middleware' => ['web'],
        ], function ($router) {
            require base_path('routes/web.php');
        });

        // API routes
        $router->group([
            'namespace' => $this->namespace . '\\Api',
            'middleware' => ['api'],
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }
}
```

### EventServiceProvider

Registers event listeners and subscribers:

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use App\Events\UserRegistered;
use App\Listeners\SendWelcomeEmail;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Event listener mappings
     */
    protected array $listen = [
        UserRegistered::class => [
            SendWelcomeEmail::class,
        ],
    ];

    /**
     * Register services
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        $events = $this->app->make('events');

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }
    }
}
```

## Registering Service Providers

Service providers must be registered in `config/app.php`:

```php
<?php

return [
    // ...

    'providers' => [
        /*
         * Framework Service Providers
         */
        NeoPhp\Foundation\Providers\CoreServiceProvider::class,
        NeoPhp\Database\DatabaseServiceProvider::class,
        NeoPhp\Routing\RoutingServiceProvider::class,
        NeoPhp\View\ViewServiceProvider::class,
        NeoPhp\Cache\CacheServiceProvider::class,
        NeoPhp\Queue\QueueServiceProvider::class,

        /*
         * Application Service Providers
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\EventServiceProvider::class,
    ],

    // ...
];
```

## When to Create a Service Provider

Create a new service provider when you need to:

### 1. Register Related Services

Group related service bindings together:

```php
class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerStripeGateway();
        $this->registerPayPalGateway();
        $this->registerPaymentProcessor();
    }
}
```

### 2. Bootstrap Packages

Initialize third-party packages:

```php
class MonologServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Logger::class, function ($app) {
            return new Logger('app', [
                new StreamHandler($app->storagePath('logs/app.log')),
            ]);
        });
    }
}
```

### 3. Register Middleware

Add middleware to your application:

```php
class MiddlewareServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('auth', AuthMiddleware::class);
        $router->middleware('admin', AdminMiddleware::class);
        $router->middleware('throttle', ThrottleMiddleware::class);
    }
}
```

### 4. Share View Data

Provide data to all views:

```php
class ViewComposerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        view()->share('appName', config('app.name'));
        
        view()->composer('*', function ($view) {
            $view->with('currentUser', auth()->user());
        });
    }
}
```

## Service Provider Best Practices 📋

### 1. Keep Providers Focused

Each provider should have a single, clear responsibility:

```php
// ✅ Good: Focused on caching
class CacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerCacheManager();
        $this->registerCacheDrivers();
    }
}

// ❌ Bad: Too many responsibilities
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerCache();
        $this->registerDatabase();
        $this->registerMail();
        $this->registerQueue();
        // Too much!
    }
}
```

### 2. Use Deferred Providers for Heavy Services

Defer loading of services not needed on every request:

```php
class AnalyticsServiceProvider extends ServiceProvider
{
    protected bool $defer = true;
    
    protected array $provides = [
        AnalyticsService::class,
        'analytics',
    ];
    
    public function register(): void
    {
        $this->app->singleton(AnalyticsService::class);
    }
    
    public function provides(): array
    {
        return $this->provides;
    }
}
```

### 3. Order Matters

Register providers in the correct order:

```php
'providers' => [
    // Core framework providers first
    CoreServiceProvider::class,
    DatabaseServiceProvider::class,
    
    // Then third-party providers
    MonologServiceProvider::class,
    
    // Finally application providers
    AppServiceProvider::class,
    RouteServiceProvider::class,
],
```

### 4. Use Constructor Injection in Boot

Take advantage of automatic dependency resolution:

```php
public function boot(Router $router, View $view, Cache $cache): void
{
    // All dependencies automatically resolved
    $router->middleware('cache', CacheMiddleware::class);
    $view->share('cache', $cache);
}
```

### 5. Document Your Providers

Add clear documentation to your providers:

```php
/**
 * Payment Service Provider
 * 
 * Registers payment gateways and processors for the application.
 * Supports Stripe, PayPal, and custom payment methods.
 * 
 * @package App\Providers
 */
class PaymentServiceProvider extends ServiceProvider
{
    // ...
}
```

## Common Patterns 🎯

### Configuration-Based Registration

Register services based on configuration:

```php
public function register(): void
{
    $driver = config('cache.default');
    
    $this->app->singleton('cache', function ($app) use ($driver) {
        return match($driver) {
            'redis' => new RedisCache(),
            'memcached' => new MemcachedCache(),
            default => new FileCache(),
        };
    });
}
```

### Environment-Specific Services

Register different services based on environment:

```php
public function register(): void
{
    if ($this->app->environment('local')) {
        $this->app->singleton(Debugger::class, DetailedDebugger::class);
    } else {
        $this->app->singleton(Debugger::class, ProductionDebugger::class);
    }
}
```

### Package Service Providers

Create service providers for reusable packages:

```php
<?php

namespace YourVendor\YourPackage;

use NeoPhp\Foundation\ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge package config
        $this->mergeConfigFrom(
            __DIR__.'/../config/package.php',
            'package'
        );
        
        // Register package services
        $this->app->singleton(PackageService::class);
    }
    
    public function boot(): void
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/package.php' => config_path('package.php'),
            ], 'config');
            
            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'migrations');
        }
        
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'package');
        
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}
```

## Troubleshooting 🔍

### Provider Not Loading

**Problem:** Service provider isn't being loaded.

**Solution:** Ensure it's registered in `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\YourServiceProvider::class,
],
```

### Service Not Found

**Problem:** Service bound in `boot()` instead of `register()`.

**Solution:** Move bindings to `register()`:

```php
// ❌ Wrong
public function boot(): void
{
    $this->app->singleton(Service::class);
}

// ✅ Correct
public function register(): void
{
    $this->app->singleton(Service::class);
}
```

### Circular Dependency

**Problem:** Provider A depends on Provider B which depends on Provider A.

**Solution:** Use lazy loading or restructure dependencies:

```php
// Use closure for lazy loading
$this->app->singleton(ServiceA::class, function ($app) {
    return new ServiceA($app->make(ServiceB::class));
});
```

## Testing Service Providers 🧪

```php
<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use App\Providers\PaymentServiceProvider;
use App\Services\PaymentGateway;

class PaymentServiceProviderTest extends TestCase
{
    public function test_registers_payment_gateway(): void
    {
        $provider = new PaymentServiceProvider($this->app);
        $provider->register();

        $this->assertTrue($this->app->bound(PaymentGateway::class));
    }

    public function test_payment_gateway_is_singleton(): void
    {
        $provider = new PaymentServiceProvider($this->app);
        $provider->register();

        $instance1 = $this->app->make(PaymentGateway::class);
        $instance2 = $this->app->make(PaymentGateway::class);

        $this->assertSame($instance1, $instance2);
    }
}
```

## Real-World Example 🌍

Complete service provider for a blogging system:

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use App\Services\BlogService;
use App\Services\CommentService;
use App\Services\TagService;
use App\View\Composers\SidebarComposer;
use NeoPhp\Routing\Router;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register blog services
     */
    public function register(): void
    {
        // Register blog service
        $this->app->singleton(BlogService::class, function ($app) {
            return new BlogService(
                $app->make('db'),
                $app->make('cache'),
                $app->make('events')
            );
        });

        // Register comment service
        $this->app->singleton(CommentService::class, function ($app) {
            return new CommentService(
                $app->make('db'),
                $app->make(BlogService::class)
            );
        });

        // Register tag service
        $this->app->singleton(TagService::class);
    }

    /**
     * Bootstrap blog services
     */
    public function boot(Router $router): void
    {
        // Register routes
        $this->registerRoutes($router);

        // Register middleware
        $this->registerMiddleware($router);

        // Register view composers
        $this->registerViewComposers();

        // Register event listeners
        $this->registerEventListeners();

        // Schedule tasks
        $this->scheduleTasks();
    }

    /**
     * Register blog routes
     */
    protected function registerRoutes(Router $router): void
    {
        $router->group([
            'prefix' => 'blog',
            'namespace' => 'App\Controllers\Blog',
        ], function ($router) {
            require base_path('routes/blog.php');
        });
    }

    /**
     * Register blog middleware
     */
    protected function registerMiddleware(Router $router): void
    {
        $router->middleware('blog.auth', BlogAuthMiddleware::class);
        $router->middleware('blog.cache', BlogCacheMiddleware::class);
    }

    /**
     * Register view composers
     */
    protected function registerViewComposers(): void
    {
        view()->composer('blog.*', SidebarComposer::class);
        
        view()->composer('blog.sidebar', function ($view) {
            $view->with('popularPosts', cache()->remember(
                'blog.popular',
                3600,
                fn() => $this->app->make(BlogService::class)->getPopular(5)
            ));
        });
    }

    /**
     * Register event listeners
     */
    protected function registerEventListeners(): void
    {
        $events = $this->app->make('events');
        
        $events->listen(PostPublished::class, UpdateSitemap::class);
        $events->listen(PostPublished::class, ClearCache::class);
        $events->listen(CommentAdded::class, NotifyAuthor::class);
    }

    /**
     * Schedule periodic tasks
     */
    protected function scheduleTasks(): void
    {
        if ($this->app->runningInConsole()) {
            schedule()->daily()->call(function () {
                $this->app->make(BlogService::class)->cleanupDrafts();
            });
        }
    }
}
```

## Next Steps 📖

Now that you understand service providers, dive deeper into:

1. **[Service Container](container.md)** - Master the IoC container
2. **[Dependency Injection](dependency-injection.md)** - Learn DI patterns
3. **[Facades](facades.md)** - Static proxy pattern
4. **[Service Providers Deep Dive](../core-concepts/service-providers.md)** - Advanced patterns

## Related Documentation

- [Application Lifecycle](../architecture/lifecycle.md) - How providers fit into the request lifecycle
- [Configuration](../getting-started/configuration.md) - Managing configuration
- [Container](container.md) - The service container
