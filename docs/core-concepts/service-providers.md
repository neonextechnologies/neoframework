# Service Providers 📦

## Introduction

Service providers are the central place for configuring and bootstrapping your NeoFramework application. They are responsible for binding services into the service container, registering event listeners, middleware, routes, and any other initialization your application requires.

All service providers extend the `NeoPhp\Foundation\ServiceProvider` class and contain at least two methods: `register()` and `boot()`. Service providers are the connection point between your application and the framework's service container.

Think of service providers as the "bootstrappers" of your application. When you need to bind something into the container or perform some task before your application handles requests, you'll use a service provider.

## Basic Service Provider Structure

### Creating a Service Provider

Generate a new service provider using the Neo CLI:

```bash
php neo make:provider PaymentServiceProvider
php neo make:provider Services/NotificationServiceProvider
```

This creates a service provider in `app/Providers/`:

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services in the container
     */
    public function register(): void
    {
        // Bind services into the container
    }

    /**
     * Bootstrap services after all providers registered
     */
    public function boot(): void
    {
        // Perform actions after all services registered
    }
}
```

### Registering the Service Provider

Add your service provider to the `providers` array in `config/app.php`:

```php
return [
    'providers' => [
        // Framework providers
        NeoPhp\Foundation\Providers\CoreServiceProvider::class,
        NeoPhp\Database\DatabaseServiceProvider::class,
        
        // Application providers
        App\Providers\AppServiceProvider::class,
        App\Providers\PaymentServiceProvider::class,
    ],
];
```

## The Register Method 🔧

The `register()` method is used to bind services into the service container. You should **only bind things into the container** within this method. Never attempt to register event listeners, routes, or any other functionality within the register method.

### Basic Binding

```php
public function register(): void
{
    // Bind a singleton
    $this->app->singleton(PaymentGateway::class, function ($app) {
        return new StripeGateway(
            config('services.stripe.key'),
            config('services.stripe.secret')
        );
    });

    // Bind an interface to implementation
    $this->app->bind(
        PaymentProcessorInterface::class,
        PaymentProcessor::class
    );

    // Bind an instance
    $this->app->instance('payment.config', [
        'currency' => 'USD',
        'timeout' => 30,
    ]);
}
```

### Helper Methods

Service providers provide convenient helper methods for binding:

```php
public function register(): void
{
    // Singleton binding
    $this->singleton(CacheManager::class, function ($app) {
        return new CacheManager($app->make('config'));
    });

    // Regular binding
    $this->bind(LoggerInterface::class, FileLogger::class);

    // Bind existing instance
    $this->instance('app.version', '1.0.0');

    // Register alias
    $this->alias(CacheManager::class, 'cache');
}
```

### Context-Aware Binding

Bind different implementations based on context:

```php
public function register(): void
{
    $this->app->when(EmailService::class)
        ->needs(MailerInterface::class)
        ->give(SmtpMailer::class);

    $this->app->when(NotificationService::class)
        ->needs(MailerInterface::class)
        ->give(MailgunMailer::class);
}
```

### Binding Multiple Services

```php
public function register(): void
{
    // Register multiple related services
    $this->registerPaymentGateways();
    $this->registerPaymentProcessors();
    $this->registerPaymentSubscriptions();
}

protected function registerPaymentGateways(): void
{
    $this->singleton('payment.stripe', function ($app) {
        return new StripeGateway(config('services.stripe'));
    });

    $this->singleton('payment.paypal', function ($app) {
        return new PayPalGateway(config('services.paypal'));
    });
}

protected function registerPaymentProcessors(): void
{
    $this->singleton(PaymentProcessor::class, function ($app) {
        return new PaymentProcessor(
            $app->make('payment.stripe'),
            $app->make('payment.paypal')
        );
    });
}
```

## The Boot Method 🚀

The `boot()` method is called after all service providers have been registered. This means you have access to all services that have been registered by other providers. This is where you should place logic that depends on other services being available.

### Basic Bootstrapping

```php
public function boot(): void
{
    // Register routes
    $this->loadRoutes();

    // Register views
    $this->loadViews();

    // Register migrations
    $this->loadMigrations();

    // Publish assets
    $this->publishAssets();
}

protected function loadRoutes(): void
{
    require base_path('routes/payment.php');
}

protected function loadViews(): void
{
    $this->app->make('view')->addNamespace('payment', __DIR__ . '/../../resources/views');
}
```

### Dependency Injection in Boot

The boot method supports dependency injection:

```php
public function boot(Router $router, View $view, EventDispatcher $events): void
{
    // Use injected dependencies
    $router->middleware('payment', PaymentMiddleware::class);

    $view->composer('payment.*', PaymentComposer::class);

    $events->listen(OrderCreated::class, ProcessPayment::class);
}
```

### Conditional Booting

Boot services only when needed:

```php
public function boot(): void
{
    if ($this->app->environment('local')) {
        $this->bootForLocal();
    }

    if ($this->app->runningInConsole()) {
        $this->bootForConsole();
    }

    if ($this->app->runningUnitTests()) {
        $this->bootForTesting();
    }
}

protected function bootForLocal(): void
{
    // Register debug tools
    $this->app->singleton(DebugBar::class);
}

protected function bootForConsole(): void
{
    // Register commands
    $this->commands([
        PaymentProcessCommand::class,
        RefundCommand::class,
    ]);
}
```

## Deferred Providers ⏱️

Deferred providers are only loaded when one of the services they provide is actually needed. This can greatly improve the performance of your application by not loading unnecessary service providers on every request.

### Creating a Deferred Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use App\Services\AnalyticsService;
use App\Services\ReportGenerator;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred
     */
    protected bool $defer = true;

    /**
     * Services provided by this provider
     */
    protected array $provides = [
        AnalyticsService::class,
        ReportGenerator::class,
        'analytics',
    ];

    /**
     * Register the service provider
     */
    public function register(): void
    {
        $this->singleton(AnalyticsService::class, function ($app) {
            return new AnalyticsService(
                $app->make('config'),
                $app->make('cache')
            );
        });

        $this->singleton(ReportGenerator::class, function ($app) {
            return new ReportGenerator(
                $app->make(AnalyticsService::class)
            );
        });

        $this->alias(AnalyticsService::class, 'analytics');
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return $this->provides;
    }
}
```

### When to Use Deferred Providers

Deferred providers are ideal for:
- Services not used on every request
- Heavy initialization processes
- Optional features
- Third-party integrations
- Reporting and analytics services

**Don't defer:**
- Middleware providers
- Authentication providers
- Session providers
- Core routing providers

## Provider Dependencies 🔗

Declare dependencies between service providers to ensure proper loading order:

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Provider dependencies
     */
    protected array $dependencies = [
        MailServiceProvider::class,
        QueueServiceProvider::class,
    ];

    /**
     * Register the service provider
     */
    public function register(): void
    {
        $this->singleton(NotificationManager::class, function ($app) {
            // Dependencies are guaranteed to be loaded
            return new NotificationManager(
                $app->make('mail'),
                $app->make('queue')
            );
        });
    }

    /**
     * Get provider dependencies
     */
    public function dependencies(): array
    {
        return $this->dependencies;
    }
}
```

## Real-World Examples 🌍

### Database Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Database\DatabaseManager;
use NeoPhp\Database\Connectors\MySqlConnector;
use NeoPhp\Database\Connectors\PostgresConnector;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register database services
     */
    public function register(): void
    {
        $this->registerDatabaseManager();
        $this->registerConnectors();
    }

    /**
     * Bootstrap database services
     */
    public function boot(): void
    {
        $this->configureDatabaseLogging();
        $this->registerMacros();
    }

    protected function registerDatabaseManager(): void
    {
        $this->singleton('db', function ($app) {
            return new DatabaseManager($app, $app->make('db.factory'));
        });

        $this->alias('db', DatabaseManager::class);
    }

    protected function registerConnectors(): void
    {
        $this->singleton('db.factory', function ($app) {
            return new ConnectionFactory([
                'mysql' => new MySqlConnector(),
                'pgsql' => new PostgresConnector(),
            ]);
        });
    }

    protected function configureDatabaseLogging(): void
    {
        if ($this->app->config('database.log_queries')) {
            $this->app->make('db')->listen(function ($query) {
                logger()->debug('Query executed', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ]);
            });
        }
    }
}
```

### API Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use App\Services\ApiClient;
use App\Services\ApiRateLimiter;
use App\Services\ApiAuthenticator;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register API services
     */
    public function register(): void
    {
        $this->registerApiClient();
        $this->registerRateLimiter();
        $this->registerAuthenticator();
    }

    /**
     * Bootstrap API services
     */
    public function boot(): void
    {
        $this->registerMiddleware();
        $this->registerRoutes();
    }

    protected function registerApiClient(): void
    {
        $this->singleton(ApiClient::class, function ($app) {
            return new ApiClient(
                config('api.base_url'),
                config('api.timeout'),
                $app->make(ApiAuthenticator::class),
                $app->make(ApiRateLimiter::class)
            );
        });
    }

    protected function registerRateLimiter(): void
    {
        $this->singleton(ApiRateLimiter::class, function ($app) {
            return new ApiRateLimiter(
                $app->make('cache'),
                config('api.rate_limit')
            );
        });
    }

    protected function registerAuthenticator(): void
    {
        $this->singleton(ApiAuthenticator::class, function ($app) {
            return new ApiAuthenticator(
                config('api.key'),
                config('api.secret')
            );
        });
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app->make('router');
        $router->middleware('api.auth', ApiAuthMiddleware::class);
        $router->middleware('api.rate', ApiRateLimitMiddleware::class);
    }

    protected function registerRoutes(): void
    {
        require base_path('routes/api.php');
    }
}
```

### Event Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use App\Events\UserRegistered;
use App\Events\OrderPlaced;
use App\Listeners\SendWelcomeEmail;
use App\Listeners\ProcessPayment;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Event listener mappings
     */
    protected array $listen = [
        UserRegistered::class => [
            SendWelcomeEmail::class,
            CreateUserProfile::class,
        ],
        OrderPlaced::class => [
            ProcessPayment::class,
            SendOrderConfirmation::class,
            UpdateInventory::class,
        ],
    ];

    /**
     * Register event services
     */
    public function register(): void
    {
        $this->app->singleton('events', function ($app) {
            return new EventDispatcher($app);
        });
    }

    /**
     * Bootstrap event listeners
     */
    public function boot(): void
    {
        $events = $this->app->make('events');

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }

        $this->registerSubscribers($events);
    }

    protected function registerSubscribers($events): void
    {
        $events->subscribe(UserEventSubscriber::class);
        $events->subscribe(OrderEventSubscriber::class);
    }
}
```

## Best Practices 📋

### 1. Keep Providers Focused

Each provider should have a single responsibility:

```php
// Good: Focused provider
class MailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerMailManager();
        $this->registerMailTransports();
    }
}

// Bad: Too many responsibilities
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerMail();
        $this->registerCache();
        $this->registerQueue();
        $this->registerStorage();
        // Too much!
    }
}
```

### 2. Use Deferred Providers Wisely

```php
// Defer expensive or rarely used services
class ReportingServiceProvider extends ServiceProvider
{
    protected bool $defer = true;
    
    protected array $provides = [
        ReportGenerator::class,
        'reporting',
    ];
}
```

### 3. Register Before Boot

```php
public function register(): void
{
    // Only container bindings here
    $this->singleton(Service::class);
}

public function boot(): void
{
    // Use services here
    $service = $this->app->make(Service::class);
    $service->initialize();
}
```

### 4. Use Constructor Injection in Boot

```php
public function boot(Router $router, View $view): void
{
    // Dependencies automatically resolved
    $router->middleware('custom', CustomMiddleware::class);
    $view->share('appName', config('app.name'));
}
```

### 5. Organize Large Providers

```php
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerServices();
        $this->registerRepositories();
        $this->registerHelpers();
    }

    protected function registerServices(): void
    {
        // Service registrations
    }

    protected function registerRepositories(): void
    {
        // Repository registrations
    }

    protected function registerHelpers(): void
    {
        // Helper registrations
    }
}
```

## Testing Service Providers 🧪

### Basic Provider Test

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

    public function test_boots_payment_routes(): void
    {
        $provider = new PaymentServiceProvider($this->app);
        $provider->boot($this->app->make('router'));

        $routes = $this->app->make('router')->getRoutes();
        
        $this->assertTrue($routes->hasNamedRoute('payment.process'));
    }
}
```

### Testing Deferred Providers

```php
public function test_deferred_provider_loads_on_demand(): void
{
    $provider = new AnalyticsServiceProvider($this->app);
    
    $this->assertTrue($provider->isDeferred());
    $this->assertContains(
        AnalyticsService::class,
        $provider->provides()
    );

    // Provider should load when service requested
    $analytics = $this->app->make(AnalyticsService::class);
    $this->assertInstanceOf(AnalyticsService::class, $analytics);
}
```

## Advanced Patterns 🚀

### Conditional Service Registration

```php
public function register(): void
{
    if ($this->app->environment('production')) {
        $this->registerProductionServices();
    } else {
        $this->registerDevelopmentServices();
    }
}

protected function registerProductionServices(): void
{
    $this->singleton(Logger::class, CloudLogger::class);
    $this->singleton(Cache::class, RedisCache::class);
}

protected function registerDevelopmentServices(): void
{
    $this->singleton(Logger::class, FileLogger::class);
    $this->singleton(Cache::class, ArrayCache::class);
}
```

### Dynamic Service Registration

```php
public function register(): void
{
    $services = config('app.services', []);

    foreach ($services as $abstract => $concrete) {
        if (is_array($concrete)) {
            $this->bind($abstract, $concrete['class'], $concrete['shared'] ?? false);
        } else {
            $this->bind($abstract, $concrete);
        }
    }
}
```

### Provider Composition

```php
class CompositeServiceProvider extends ServiceProvider
{
    protected array $providers = [
        CacheServiceProvider::class,
        QueueServiceProvider::class,
        MailServiceProvider::class,
    ];

    public function register(): void
    {
        foreach ($this->providers as $providerClass) {
            $provider = new $providerClass($this->app);
            $provider->register();
        }
    }

    public function boot(): void
    {
        foreach ($this->providers as $providerClass) {
            $provider = new $providerClass($this->app);
            $provider->boot();
        }
    }
}
```

## Troubleshooting 🔍

### Common Issues

**Issue: Service not found**
```php
// Make sure service is registered
public function register(): void
{
    $this->singleton(MyService::class);
}

// And provider is in config/app.php
'providers' => [
    App\Providers\MyServiceProvider::class,
],
```

**Issue: Circular dependencies**
```php
// Use lazy loading with closures
public function register(): void
{
    $this->singleton(ServiceA::class, function ($app) {
        // ServiceB resolved when needed, not immediately
        return new ServiceA($app->make(ServiceB::class));
    });
}
```

**Issue: Boot method dependencies not available**
```php
// Don't do this in register()
public function register(): void
{
    // Router may not be available yet
    $router = $this->app->make('router'); // ❌
}

// Do this in boot() instead
public function boot(Router $router): void
{
    // All services available now
    $router->middleware('custom', CustomMiddleware::class); // ✅
}
```

## Related Documentation

- [Service Container](container.md) - Learn about the IoC container
- [Dependency Injection](dependency-injection.md) - DI patterns and techniques
- [Facades](facades.md) - Static proxy pattern
- [Application Lifecycle](../architecture/lifecycle.md) - Understanding the request lifecycle
- [Configuration](../getting-started/configuration.md) - Managing configuration

## Next Steps

Now that you understand service providers, explore these related topics:

1. **[Service Container](container.md)** - Deep dive into the IoC container
2. **[Dependency Injection](dependency-injection.md)** - Master DI patterns
3. **[Facades](facades.md)** - Learn the facade pattern
4. **[Hooks System](hooks.md)** - Event-driven architecture
5. **[Plugins](plugins.md)** - Extend functionality with plugins
