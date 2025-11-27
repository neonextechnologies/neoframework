# Service Providers

## Introduction

Service providers are the central place of all NeoFramework application bootstrapping. Your own application, as well as all of NeoFramework's core services, are bootstrapped via service providers.

Service providers are responsible for binding services into the service container, registering event listeners, middleware, and even routes. They are the foundational building block of the framework.

## Writing Service Providers

All service providers extend the base `ServiceProvider` class and contain two methods: `register` and `boot`.

### The Register Method

Within the `register` method, you should only bind things into the service container. You should never attempt to register any event listeners, routes, or any other piece of functionality within the `register` method.

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Container\Container;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(Container $container): void
    {
        $container->singleton(Connection::class, function ($app) {
            return new Connection(config('database'));
        });
    }
}
```

### The Boot Method

The `boot` method is called after all other service providers have been registered. This means you have access to all services that have been registered by the framework:

```php
public function boot(): void
{
    // Access fully registered services
    $connection = $this->app->get(Connection::class);
    
    // Register routes
    require base_path('routes/api.php');
    
    // Publish configuration
    $this->publishes([
        __DIR__.'/config/mypackage.php' => config_path('mypackage.php'),
    ]);
}
```

## Registering Providers

All service providers are registered in the `config/app.php` configuration file:

```php
'providers' => [
    // Framework Service Providers
    NeoPhp\Auth\AuthServiceProvider::class,
    NeoPhp\Cache\CacheServiceProvider::class,
    NeoPhp\Database\DatabaseServiceProvider::class,
    
    // Application Service Providers
    App\Providers\AppServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
],
```

## Deferred Providers

If your provider only registers bindings in the service container, you may defer its registration until one of the bindings is actually needed:

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Container\Container;

class RiakServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     */
    protected bool $defer = true;

    /**
     * Register the service provider.
     */
    public function register(Container $container): void
    {
        $container->singleton(Connection::class, function ($app) {
            return new Connection($app->get('config')['riak']);
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [Connection::class];
    }
}
```

## Practical Examples

### Example 1: Repository Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Container\Container;
use App\Repositories\UserRepository;
use App\Repositories\PostRepository;
use App\Repositories\CommentRepository;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\PostRepositoryInterface;
use App\Contracts\CommentRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository bindings.
     */
    public function register(Container $container): void
    {
        // User Repository
        $container->singleton(UserRepositoryInterface::class, function ($app) {
            return new UserRepository(
                $app->get(Database::class),
                $app->get(CacheInterface::class)
            );
        });
        
        // Post Repository
        $container->singleton(PostRepositoryInterface::class, function ($app) {
            return new PostRepository(
                $app->get(Database::class),
                $app->get(CacheInterface::class)
            );
        });
        
        // Comment Repository
        $container->singleton(CommentRepositoryInterface::class, function ($app) {
            return new CommentRepository(
                $app->get(Database::class)
            );
        });
    }
}
```

### Example 2: Payment Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Container\Container;
use App\Services\Payment\StripeGateway;
use App\Services\Payment\PayPalGateway;
use App\Services\Payment\PaymentManager;
use App\Contracts\PaymentGatewayInterface;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register payment services.
     */
    public function register(Container $container): void
    {
        // Register individual gateways
        $container->bind('payment.stripe', function ($app) {
            return new StripeGateway(
                config('services.stripe.secret'),
                config('services.stripe.webhook_secret')
            );
        });
        
        $container->bind('payment.paypal', function ($app) {
            return new PayPalGateway(
                config('services.paypal.client_id'),
                config('services.paypal.client_secret')
            );
        });
        
        // Register payment manager
        $container->singleton(PaymentManager::class, function ($app) {
            $manager = new PaymentManager();
            $manager->extend('stripe', fn() => $app->get('payment.stripe'));
            $manager->extend('paypal', fn() => $app->get('payment.paypal'));
            return $manager;
        });
        
        // Bind default gateway
        $container->bind(PaymentGatewayInterface::class, function ($app) {
            $driver = config('payment.default', 'stripe');
            return $app->get(PaymentManager::class)->driver($driver);
        });
    }
    
    /**
     * Bootstrap payment services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/payment.php' => config_path('payment.php'),
        ], 'config');
        
        // Load routes
        require __DIR__.'/../../routes/payment.php';
    }
}
```

### Example 3: Event Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Container\Container;
use App\Events\UserRegistered;
use App\Events\OrderPlaced;
use App\Listeners\SendWelcomeEmail;
use App\Listeners\ProcessPayment;
use App\Listeners\NotifyAdmin;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     */
    protected array $listen = [
        UserRegistered::class => [
            SendWelcomeEmail::class,
            NotifyAdmin::class,
        ],
        OrderPlaced::class => [
            ProcessPayment::class,
        ],
    ];

    /**
     * Register event services.
     */
    public function register(Container $container): void
    {
        //
    }

    /**
     * Bootstrap event services.
     */
    public function boot(): void
    {
        $events = $this->app->get(EventDispatcher::class);
        
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }
    }
}
```

### Example 4: API Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Container\Container;
use App\Services\Api\GitHubClient;
use App\Services\Api\StripeClient;
use App\Services\Api\TwilioClient;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register API services.
     */
    public function register(Container $container): void
    {
        // GitHub API
        $container->singleton(GitHubClient::class, function ($app) {
            return new GitHubClient(
                token: config('services.github.token'),
                timeout: config('services.github.timeout', 30)
            );
        });
        
        // Stripe API
        $container->singleton(StripeClient::class, function ($app) {
            return new StripeClient(
                apiKey: config('services.stripe.secret'),
                apiVersion: '2023-10-16'
            );
        });
        
        // Twilio API
        $container->singleton(TwilioClient::class, function ($app) {
            return new TwilioClient(
                accountSid: config('services.twilio.account_sid'),
                authToken: config('services.twilio.auth_token'),
                from: config('services.twilio.from')
            );
        });
    }
    
    /**
     * Bootstrap API services.
     */
    public function boot(): void
    {
        // Set up HTTP client defaults
        if ($this->app->environment('testing')) {
            // Mock external APIs in testing
            $this->app->bind(GitHubClient::class, MockGitHubClient::class);
        }
    }
}
```

### Example 5: View Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Container\Container;
use NeoPhp\View\Factory;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register view services.
     */
    public function register(Container $container): void
    {
        $container->singleton('view', function ($app) {
            $factory = new Factory(
                $app->get('view.engine'),
                resource_path('views')
            );
            
            return $factory;
        });
    }
    
    /**
     * Bootstrap view services.
     */
    public function boot(): void
    {
        $view = $this->app->get('view');
        
        // Share data with all views
        $view->share('appName', config('app.name'));
        $view->share('appVersion', config('app.version'));
        
        // Register view composers
        $view->composer('layouts.app', function ($view) {
            $view->with('user', auth()->user());
        });
        
        $view->composer('partials.sidebar', function ($view) {
            $view->with('notifications', auth()->user()->notifications);
        });
    }
}
```

## Common Service Provider Patterns

### Pattern 1: Configuration-Based Registration

```php
public function register(Container $container): void
{
    $container->singleton(CacheInterface::class, function ($app) {
        $driver = config('cache.default');
        
        return match($driver) {
            'redis' => new RedisCache($app->get(Redis::class)),
            'memcached' => new MemcachedCache($app->get(Memcached::class)),
            default => new FileCache(storage_path('cache')),
        };
    });
}
```

### Pattern 2: Environment-Specific Registration

```php
public function register(Container $container): void
{
    if ($this->app->environment('production')) {
        $container->singleton(LoggerInterface::class, ProductionLogger::class);
    } else {
        $container->singleton(LoggerInterface::class, DevelopmentLogger::class);
    }
}
```

### Pattern 3: Tagged Services

```php
public function register(Container $container): void
{
    // Register individual validators
    $container->bind(EmailValidator::class);
    $container->bind(PhoneValidator::class);
    $container->bind(UrlValidator::class);
    
    // Tag them
    $container->tag([
        EmailValidator::class,
        PhoneValidator::class,
        UrlValidator::class,
    ], 'validators');
}

public function boot(): void
{
    $validator = $this->app->get(ValidationManager::class);
    
    // Register all tagged validators
    foreach ($this->app->tagged('validators') as $validatorClass) {
        $validator->register($validatorClass);
    }
}
```

### Pattern 4: Conditional Binding

```php
public function register(Container $container): void
{
    if (!$container->bound(SearchInterface::class)) {
        $container->singleton(SearchInterface::class, function ($app) {
            if (extension_loaded('redis')) {
                return new RedisSearch($app->get(Redis::class));
            }
            
            return new DatabaseSearch($app->get(Database::class));
        });
    }
}
```

## Best Practices

### 1. Keep Register Method Pure

The `register` method should only bind services. Don't access other services here:

**Good:**
```php
public function register(Container $container): void
{
    $container->singleton(UserRepository::class);
}
```

**Bad:**
```php
public function register(Container $container): void
{
    $users = $container->get(UserRepository::class); // Don't do this
}
```

### 2. Use Boot for Service Access

Access other services in the `boot` method:

```php
public function boot(): void
{
    $repository = $this->app->get(UserRepository::class);
    $cache = $this->app->get(CacheInterface::class);
    
    // Configure services
}
```

### 3. Group Related Bindings

Create focused providers for related services:

- `RepositoryServiceProvider` - All repositories
- `PaymentServiceProvider` - Payment services
- `NotificationServiceProvider` - Notification channels
- `ApiServiceProvider` - External API clients

### 4. Use Singletons Appropriately

```php
// Singleton for stateless services
$container->singleton(UserRepository::class);

// Not singleton for stateful services
$container->bind(ReportGenerator::class);
```

### 5. Document Provider Dependencies

```php
/**
 * PaymentServiceProvider
 * 
 * Requires:
 * - DatabaseServiceProvider
 * - CacheServiceProvider
 * - EventServiceProvider
 */
class PaymentServiceProvider extends ServiceProvider
{
    // ...
}
```

## Testing Service Providers

```php
class RepositoryServiceProviderTest extends TestCase
{
    public function test_registers_user_repository()
    {
        $container = new Container();
        $provider = new RepositoryServiceProvider($container);
        
        $provider->register($container);
        
        $this->assertTrue($container->bound(UserRepositoryInterface::class));
    }
    
    public function test_resolves_user_repository()
    {
        $repository = app(UserRepositoryInterface::class);
        
        $this->assertInstanceOf(UserRepository::class, $repository);
    }
}
```

## Creating Packages with Service Providers

When building packages, provide a service provider for easy integration:

```php
<?php

namespace Vendor\Package;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Container\Container;

class PackageServiceProvider extends ServiceProvider
{
    public function register(Container $container): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/package.php', 'package'
        );
        
        // Register bindings
        $container->singleton(PackageService::class);
    }
    
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/package.php' => config_path('package.php'),
        ], 'config');
        
        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'migrations');
        
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'package');
    }
}
```

## Next Steps

- [Service Container](container.md) - Learn about dependency injection
- [Facades](facades.md) - Simplified static access to services
- [Request Lifecycle](lifecycle.md) - How NeoFramework handles requests
