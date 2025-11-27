# Service Container

## Introduction

The NeoFramework service container is a powerful tool for managing class dependencies and performing dependency injection. At its core, dependency injection means that class dependencies are "injected" into the class via the constructor or setter methods.

## Basic Usage

### Binding

Almost all service container bindings will be registered within service providers. Let's explore how to use the container.

#### Simple Bindings

Within a service provider, you always have access to the container via the `$container` property:

```php
use NeoPhp\Container\Container;

class AppServiceProvider
{
    public function register(Container $container): void
    {
        $container->bind(TransactionInterface::class, function($app) {
            return new MySQLTransaction();
        });
    }
}
```

#### Binding a Singleton

The `singleton` method binds a class that should only be resolved once:

```php
$container->singleton(UserRepository::class, function($app) {
    return new UserRepository($app->get(Database::class));
});
```

#### Binding Instances

You can bind an existing object instance:

```php
$api = new ApiClient('api-key');
$container->instance(ApiClient::class, $api);
```

### Resolving

#### The make Method

You may use the `make` method or `get` method to resolve a class instance:

```php
$repository = $container->make(UserRepository::class);
// or
$repository = $container->get(UserRepository::class);
```

#### Automatic Injection

The container automatically resolves dependencies:

```php
class UserController extends Controller
{
    public function __construct(
        private UserRepository $users,
        private EmailService $emails
    ) {}
}

// Container automatically injects dependencies
$controller = $container->make(UserController::class);
```

## Binding Interfaces to Implementations

One of the most powerful features is binding interfaces to concrete implementations:

```php
interface CacheInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value): void;
}

class RedisCache implements CacheInterface
{
    public function get(string $key): mixed
    {
        // Redis implementation
    }
    
    public function set(string $key, mixed $value): void
    {
        // Redis implementation
    }
}

// Bind interface to implementation
$container->bind(CacheInterface::class, RedisCache::class);

// Or with dependencies
$container->bind(CacheInterface::class, function($app) {
    return new RedisCache($app->get(RedisClient::class));
});
```

Now whenever you type-hint the interface, the container will inject the concrete implementation:

```php
class UserRepository
{
    public function __construct(
        private CacheInterface $cache
    ) {}
}

// Container automatically injects RedisCache
$repository = $container->make(UserRepository::class);
```

## Contextual Binding

Sometimes you may have two classes that use the same interface, but need different implementations:

```php
$container->when(PhotoController::class)
    ->needs(FilesystemInterface::class)
    ->give(LocalFilesystem::class);

$container->when(VideoController::class)
    ->needs(FilesystemInterface::class)
    ->give(S3Filesystem::class);
```

## Method Invocation

The container can invoke any callable and automatically inject dependencies:

```php
class ReportGenerator
{
    public function generate(UserRepository $users, Request $request)
    {
        // Generate report
    }
}

$container->call([new ReportGenerator(), 'generate']);
```

## Container Events

The container fires an event each time it resolves an object:

```php
$container->resolving(UserRepository::class, function($repository, $app) {
    // Called when UserRepository is resolved
});

$container->afterResolving(UserRepository::class, function($repository, $app) {
    // Called after UserRepository is resolved
});
```

## Practical Examples

### Example 1: Database Repository Pattern

```php
// Interface
interface UserRepositoryInterface
{
    public function find(int $id): ?User;
    public function create(array $data): User;
}

// Implementation
class DatabaseUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private Database $db,
        private CacheInterface $cache
    ) {}
    
    public function find(int $id): ?User
    {
        return $this->cache->remember("user.{$id}", fn() => 
            $this->db->table('users')->find($id)
        );
    }
    
    public function create(array $data): User
    {
        return $this->db->table('users')->insert($data);
    }
}

// Service Provider
class RepositoryServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(
            UserRepositoryInterface::class,
            DatabaseUserRepository::class
        );
    }
}

// Controller
class UserController extends Controller
{
    public function __construct(
        private UserRepositoryInterface $users
    ) {}
    
    public function show(int $id)
    {
        $user = $this->users->find($id);
        return new UserResource($user);
    }
}
```

### Example 2: Multiple Implementations

```php
// Service Provider
class NotificationServiceProvider
{
    public function register(Container $container): void
    {
        // Email notifications
        $container->bind('notification.email', function($app) {
            return new EmailNotification($app->get(Mailer::class));
        });
        
        // SMS notifications
        $container->bind('notification.sms', function($app) {
            return new SmsNotification($app->get(TwilioClient::class));
        });
        
        // Push notifications
        $container->bind('notification.push', function($app) {
            return new PushNotification($app->get(FirebaseClient::class));
        });
        
        // Notification manager
        $container->singleton(NotificationManager::class, function($app) {
            $manager = new NotificationManager();
            $manager->addChannel('email', $app->get('notification.email'));
            $manager->addChannel('sms', $app->get('notification.sms'));
            $manager->addChannel('push', $app->get('notification.push'));
            return $manager;
        });
    }
}

// Usage
$manager = app(NotificationManager::class);
$manager->channel('email')->send($user, new WelcomeNotification());
```

### Example 3: Configuration-Based Binding

```php
class CacheServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(CacheInterface::class, function($app) {
            $driver = config('cache.default');
            
            return match($driver) {
                'redis' => new RedisCache(
                    $app->get(RedisClient::class)
                ),
                'memcached' => new MemcachedCache(
                    $app->get(MemcachedClient::class)
                ),
                default => new FileCache(
                    storage_path('cache')
                ),
            };
        });
    }
}
```

## Helper Functions

NeoFramework provides convenient helper functions for accessing the container:

```php
// Get the container instance
$container = app();

// Resolve from container
$users = app(UserRepository::class);

// Resolve with parameters
$service = app(ApiService::class, ['apiKey' => 'xxx']);
```

## Best Practices

### 1. Bind Interfaces, Not Implementations

**Good:**
```php
$container->bind(CacheInterface::class, RedisCache::class);
```

**Bad:**
```php
$container->bind(RedisCache::class, RedisCache::class); // Unnecessary
```

### 2. Use Singletons for Stateless Services

```php
// Singleton - shared instance
$container->singleton(DatabaseConnection::class);

// Not singleton - new instance each time
$container->bind(ReportGenerator::class);
```

### 3. Type-Hint Interfaces in Constructors

**Good:**
```php
public function __construct(CacheInterface $cache) {}
```

**Bad:**
```php
public function __construct(RedisCache $cache) {} // Too specific
```

### 4. Register Bindings in Service Providers

**Good:**
```php
class AppServiceProvider
{
    public function register(Container $container): void
    {
        $container->bind(PaymentGateway::class, StripeGateway::class);
    }
}
```

**Bad:**
```php
// In controller or model
app()->bind(...); // Don't do this
```

### 5. Use Dependency Injection Over Service Location

**Good:**
```php
class OrderService
{
    public function __construct(
        private PaymentGateway $payment,
        private EmailService $email
    ) {}
}
```

**Bad:**
```php
class OrderService
{
    public function process()
    {
        $payment = app(PaymentGateway::class); // Service location
        $email = app(EmailService::class);     // Harder to test
    }
}
```

## Testing with the Container

The container makes testing easier by allowing you to swap implementations:

```php
class UserControllerTest extends TestCase
{
    public function test_can_create_user()
    {
        // Mock the repository
        $mock = $this->mock(UserRepositoryInterface::class);
        $mock->shouldReceive('create')
             ->once()
             ->andReturn(new User(['id' => 1]));
        
        // Container automatically uses the mock
        $response = $this->post('/api/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        $response->assertStatus(201);
    }
}
```

## Advanced Features

### Tagging

Tag related bindings:

```php
$container->tag([FirstReport::class, SecondReport::class], 'reports');

// Resolve all tagged services
foreach ($container->tagged('reports') as $report) {
    $report->generate();
}
```

### Extending

Extend bindings after they're registered:

```php
$container->extend(ApiClient::class, function($client, $app) {
    $client->setLogger($app->get(Logger::class));
    return $client;
});
```

### Rebinding

Listen for when a binding is rebound:

```php
$container->rebinding('request', function($app, $request) {
    // Called when request is rebound
});
```

## Next Steps

- [Service Providers](providers.md) - Learn about service providers
- [Facades](facades.md) - Simplified container access
- [Contracts](contracts.md) - Framework interfaces
