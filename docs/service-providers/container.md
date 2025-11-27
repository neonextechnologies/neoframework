# Service Container 🧰

## Introduction

The NeoFramework service container (also known as IoC container or dependency injection container) is a powerful tool for managing class dependencies and performing dependency injection. It's the heart of the framework, responsible for resolving and instantiating objects throughout your application.

The container eliminates the need for manual object creation and management, allowing you to write cleaner, more testable code with automatic dependency resolution. Understanding the container is key to mastering NeoFramework.

## Basic Concepts

### What is a Container?

A service container is a sophisticated object registry that:
- **Stores** class bindings and their implementations
- **Resolves** dependencies automatically
- **Manages** object lifecycles (singleton vs transient)
- **Injects** dependencies into constructors and methods

Think of it as a smart factory that knows how to build complex objects with all their dependencies.

### Why Use a Container?

```php
// Without container - manual dependency management
$logger = new FileLogger('/var/log/app.log');
$cache = new RedisCache('localhost', 6379);
$database = new Database('mysql', 'localhost', 'dbname');
$repository = new UserRepository($database, $cache);
$service = new UserService($repository, $logger);

// With container - automatic dependency resolution
$service = app(UserService::class);
// Container resolves all dependencies automatically!
```

## Basic Usage

### Binding Services

#### Simple Binding

```php
// Bind an interface to a concrete class
app()->bind(LoggerInterface::class, FileLogger::class);

// Resolve it later
$logger = app(LoggerInterface::class); // Returns new FileLogger instance
```

#### Singleton Binding

Singletons are shared across the application - only one instance exists:

```php
// Bind as singleton
app()->singleton(DatabaseConnection::class, function ($app) {
    return new DatabaseConnection(
        config('database.host'),
        config('database.name')
    );
});

// Same instance every time
$db1 = app(DatabaseConnection::class);
$db2 = app(DatabaseConnection::class);

$db1 === $db2; // true
```

#### Binding with Closures

Use closures for complex instantiation logic:

```php
app()->singleton(PaymentGateway::class, function ($app) {
    $gateway = new StripeGateway(config('services.stripe.key'));
    
    // Configure the gateway
    $gateway->setApiVersion('2023-10-16');
    $gateway->setLogger($app->make(Logger::class));
    $gateway->enableTestMode($app->environment('local'));
    
    return $gateway;
});
```

#### Binding Instances

Bind an existing instance:

```php
$config = new Configuration([
    'app_name' => 'MyApp',
    'version' => '1.0.0',
]);

app()->instance('config', $config);

// Always returns the same instance
$retrieved = app('config');
```

### Resolving Services

#### Using app() Helper

```php
// Resolve by class name
$service = app(UserService::class);

// Resolve by abstract/interface
$logger = app(LoggerInterface::class);

// Resolve with parameters
$service = app(PaymentProcessor::class, [
    'amount' => 100.00,
    'currency' => 'USD',
]);
```

#### Using make() Method

```php
$container = app();

$service = $container->make(UserService::class);
$logger = $container->make(LoggerInterface::class);
```

#### Using Constructor Injection

The container automatically resolves constructor dependencies:

```php
class UserController
{
    public function __construct(
        private UserRepository $users,
        private Logger $logger,
        private Cache $cache
    ) {}
    
    public function index()
    {
        // All dependencies automatically injected
        $this->logger->info('Fetching users');
        
        return $this->cache->remember('users.all', 3600, function () {
            return $this->users->all();
        });
    }
}

// Container resolves all dependencies automatically
$controller = app(UserController::class);
```

## Advanced Binding Patterns 🎯

### Context-Based Binding

Bind different implementations based on context:

```php
// EmailService needs SMTP mailer
app()->when(EmailService::class)
    ->needs(MailerInterface::class)
    ->give(SmtpMailer::class);

// NotificationService needs queue mailer
app()->when(NotificationService::class)
    ->needs(MailerInterface::class)
    ->give(QueuedMailer::class);

// Each service gets the appropriate implementation
$email = app(EmailService::class);      // Gets SmtpMailer
$notif = app(NotificationService::class); // Gets QueuedMailer
```

### Binding Primitives

Bind primitive values to specific parameters:

```php
// Bind by parameter name
app()->when(ReportGenerator::class)
    ->needs('$reportPath')
    ->give('/var/reports');

app()->when(FileUploader::class)
    ->needs('$maxSize')
    ->give(5242880); // 5MB

class ReportGenerator
{
    public function __construct(
        private string $reportPath,
        private Logger $logger
    ) {}
}
```

### Tagging Services

Tag related services for batch resolution:

```php
// Register and tag services
app()->singleton(StripeGateway::class);
app()->singleton(PayPalGateway::class);
app()->singleton(BraintreeGateway::class);

app()->tag([
    StripeGateway::class,
    PayPalGateway::class,
    BraintreeGateway::class,
], 'payment.gateways');

// Resolve all tagged services
$gateways = app()->tagged('payment.gateways');

foreach ($gateways as $gateway) {
    $gateway->initialize();
}
```

### Extending Bindings

Modify services after resolution:

```php
// Original binding
app()->singleton(PaymentProcessor::class);

// Extend the binding
app()->extend(PaymentProcessor::class, function ($processor, $app) {
    $processor->setLogger($app->make(Logger::class));
    $processor->setCache($app->make(Cache::class));
    $processor->enableFraudDetection();
    
    return $processor;
});
```

### Binding Interfaces to Implementations

```php
// Bind interfaces
app()->bind(
    UserRepositoryInterface::class,
    EloquentUserRepository::class
);

app()->bind(
    CacheInterface::class,
    RedisCache::class
);

app()->bind(
    QueueInterface::class,
    SqsQueue::class
);

// Code depends on interfaces, not concrete classes
class UserService
{
    public function __construct(
        private UserRepositoryInterface $users,
        private CacheInterface $cache
    ) {}
}
```

## Automatic Dependency Resolution 🔄

The container automatically resolves dependencies through reflection:

### Constructor Injection

```php
class OrderService
{
    public function __construct(
        private OrderRepository $orders,
        private PaymentGateway $payment,
        private EmailService $email,
        private Logger $logger
    ) {}
    
    public function createOrder(array $data): Order
    {
        $this->logger->info('Creating order');
        
        $order = $this->orders->create($data);
        
        $this->payment->charge($order->total);
        $this->email->sendOrderConfirmation($order);
        
        return $order;
    }
}

// Container resolves all dependencies
$service = app(OrderService::class);
$order = $service->createOrder($orderData);
```

### Method Injection

The container can inject dependencies into methods:

```php
class ReportController
{
    public function generate(
        Request $request,
        ReportGenerator $generator,
        Logger $logger
    ) {
        $logger->info('Generating report');
        
        $report = $generator->generate(
            $request->input('type'),
            $request->input('filters')
        );
        
        return response()->download($report->path());
    }
}

// Router automatically injects dependencies
Route::get('/reports/generate', [ReportController::class, 'generate']);
```

### Property Injection (Not Recommended)

While possible, property injection is not recommended:

```php
// Avoid this pattern
class Service
{
    public Logger $logger;
    
    public function __construct()
    {
        $this->logger = app(Logger::class);
    }
}

// Prefer constructor injection instead
class Service
{
    public function __construct(
        private Logger $logger
    ) {}
}
```

## Real-World Examples 🌍

### Repository Pattern with Container

```php
<?php

// Define interface
interface UserRepositoryInterface
{
    public function find(int $id): ?User;
    public function all(): Collection;
    public function create(array $data): User;
}

// Implement interface
class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private Database $db,
        private Cache $cache
    ) {}
    
    public function find(int $id): ?User
    {
        return $this->cache->remember("user.{$id}", 3600, function () use ($id) {
            return $this->db->table('users')->find($id);
        });
    }
    
    public function all(): Collection
    {
        return $this->db->table('users')->get();
    }
    
    public function create(array $data): User
    {
        $user = $this->db->table('users')->insert($data);
        $this->cache->forget('users.all');
        return $user;
    }
}

// Bind in service provider
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );
    }
}

// Use in controller
class UserController
{
    public function __construct(
        private UserRepositoryInterface $users
    ) {}
    
    public function index()
    {
        return $this->users->all();
    }
    
    public function show(int $id)
    {
        return $this->users->find($id) ?? abort(404);
    }
}
```

### Service Layer Pattern

```php
<?php

// Payment service with multiple dependencies
class PaymentService
{
    public function __construct(
        private PaymentGatewayInterface $gateway,
        private OrderRepository $orders,
        private TransactionLogger $logger,
        private EventDispatcher $events,
        private Cache $cache
    ) {}
    
    public function processPayment(Order $order, PaymentMethod $method): Payment
    {
        try {
            // Log attempt
            $this->logger->logPaymentAttempt($order, $method);
            
            // Process payment
            $payment = $this->gateway->charge(
                $order->total,
                $method,
                $order->customer
            );
            
            // Update order
            $order->markAsPaid($payment);
            $this->orders->save($order);
            
            // Clear cache
            $this->cache->forget("order.{$order->id}");
            
            // Dispatch event
            $this->events->dispatch(new PaymentProcessed($payment, $order));
            
            // Log success
            $this->logger->logPaymentSuccess($payment);
            
            return $payment;
            
        } catch (PaymentException $e) {
            $this->logger->logPaymentFailure($order, $e);
            throw $e;
        }
    }
}

// Bind in service provider
class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentService::class);
        
        $this->app->bind(
            PaymentGatewayInterface::class,
            StripePaymentGateway::class
        );
    }
}

// Use in controller
class CheckoutController
{
    public function __construct(
        private PaymentService $payments
    ) {}
    
    public function process(Request $request)
    {
        $order = Order::findOrFail($request->input('order_id'));
        $method = PaymentMethod::findOrFail($request->input('payment_method_id'));
        
        try {
            $payment = $this->payments->processPayment($order, $method);
            
            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
            ]);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
```

### Multi-Tenant Application

```php
<?php

class TenantResolver
{
    public function __construct(
        private Request $request,
        private Cache $cache
    ) {}
    
    public function resolve(): Tenant
    {
        $domain = $this->request->getHost();
        
        return $this->cache->remember("tenant.{$domain}", 3600, function () use ($domain) {
            return Tenant::where('domain', $domain)->firstOrFail();
        });
    }
}

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Resolve current tenant
        $this->app->singleton('tenant', function ($app) {
            return $app->make(TenantResolver::class)->resolve();
        });
        
        // Bind tenant-specific database
        $this->app->singleton('tenant.db', function ($app) {
            $tenant = $app->make('tenant');
            
            return new Database(
                $tenant->database_host,
                $tenant->database_name,
                $tenant->database_username,
                $tenant->database_password
            );
        });
    }
    
    public function boot(): void
    {
        // Share tenant with all views
        view()->share('tenant', app('tenant'));
    }
}

// Use in application
class ProductController
{
    public function __construct(
        private Database $db // Gets tenant-specific database
    ) {}
    
    public function index()
    {
        // Automatically queries tenant's database
        return $this->db->table('products')->get();
    }
}
```

## Container Events 📡

Listen to container events:

```php
// Before resolving
app()->resolving(function ($object, $app) {
    // Called before any object is resolved
    Log::debug('Resolving: ' . get_class($object));
});

// After resolving specific class
app()->resolving(UserService::class, function ($service, $app) {
    // Called after UserService is resolved
    $service->setLogger($app->make(Logger::class));
});

// After resolving
app()->afterResolving(function ($object, $app) {
    // Called after any object is resolved
    if ($object instanceof Loggable) {
        $object->setLogger($app->make(Logger::class));
    }
});
```

## Testing with the Container 🧪

### Swapping Implementations for Tests

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\PaymentGatewayInterface;
use App\Services\MockPaymentGateway;

class CheckoutTest extends TestCase
{
    public function test_checkout_processes_payment(): void
    {
        // Swap real gateway with mock
        $this->app->singleton(
            PaymentGatewayInterface::class,
            MockPaymentGateway::class
        );
        
        $response = $this->post('/checkout', [
            'order_id' => 1,
            'payment_method' => 'card',
        ]);
        
        $response->assertSuccessful();
        $response->assertJson(['success' => true]);
    }
}
```

### Binding Mocks

```php
public function test_user_service_logs_activity(): void
{
    // Create mock
    $loggerMock = $this->createMock(Logger::class);
    $loggerMock->expects($this->once())
        ->method('info')
        ->with('User created');
    
    // Bind mock to container
    $this->app->instance(Logger::class, $loggerMock);
    
    // Test
    $service = app(UserService::class);
    $service->createUser(['name' => 'John']);
}
```

## Best Practices 📋

### 1. Bind Interfaces, Not Concrete Classes

```php
// ✅ Good: Flexible, testable
app()->bind(CacheInterface::class, RedisCache::class);

class UserService
{
    public function __construct(private CacheInterface $cache) {}
}

// ❌ Bad: Tightly coupled
class UserService
{
    public function __construct(private RedisCache $cache) {}
}
```

### 2. Use Singletons for Expensive Resources

```php
// ✅ Good: Shared database connection
app()->singleton(Database::class);

// ❌ Bad: New connection every time
app()->bind(Database::class);
```

### 3. Register Bindings in Service Providers

```php
// ✅ Good: Organized, testable
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
    }
}

// ❌ Bad: Scattered bindings
app()->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
```

### 4. Avoid Service Locator Pattern

```php
// ❌ Bad: Service locator anti-pattern
class UserService
{
    public function createUser(array $data)
    {
        $logger = app(Logger::class);
        $cache = app(Cache::class);
        // ...
    }
}

// ✅ Good: Dependency injection
class UserService
{
    public function __construct(
        private Logger $logger,
        private Cache $cache
    ) {}
    
    public function createUser(array $data)
    {
        // Dependencies available
    }
}
```

### 5. Use Type Hints

```php
// ✅ Good: Type-hinted for auto-resolution
public function __construct(
    private UserRepository $users,
    private Logger $logger
) {}

// ❌ Bad: No type hints
public function __construct($users, $logger) {}
```

## Performance Considerations ⚡

### Caching Bindings

In production, consider caching container bindings:

```bash
# Cache service provider manifest
php neo optimize
```

### Avoid Over-Engineering

Don't bind everything:

```php
// ❌ Overkill: Simple value objects
app()->bind(EmailAddress::class);

// ✅ Better: Just instantiate directly
$email = new EmailAddress('user@example.com');
```

## Related Documentation

- [Service Providers](introduction.md) - Understanding service providers
- [Dependency Injection](dependency-injection.md) - DI patterns and best practices
- [Facades](facades.md) - Static proxy pattern
- [Architecture](../architecture/container.md) - Container architecture

## Next Steps

Master dependency injection with these resources:

1. **[Dependency Injection](dependency-injection.md)** - DI patterns and techniques
2. **[Service Providers](introduction.md)** - Organize your bindings
3. **[Facades](facades.md)** - Static access to container services
4. **[Testing](../testing/getting-started.md)** - Testing with the container
