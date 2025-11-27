# Contracts & Interfaces

## Introduction

Contracts in NeoFramework are a set of interfaces that define the core services provided by the framework. These contracts provide a stable API that you can depend on, and make it easy to swap implementations without breaking your application.

## Why Contracts?

### 1. Loose Coupling

Contracts allow you to write loosely coupled code:

```php
// Without Contracts (Tightly Coupled)
class UserController
{
    public function __construct(
        private EloquentUserRepository $users  // Coupled to Eloquent
    ) {}
}

// With Contracts (Loosely Coupled)
class UserController
{
    public function __construct(
        private UserRepositoryInterface $users  // Depends on interface
    ) {}
}
```

### 2. Easy Testing

Mock interfaces instead of concrete implementations:

```php
public function test_user_creation()
{
    $mock = $this->mock(UserRepositoryInterface::class);
    $mock->shouldReceive('create')
         ->once()
         ->andReturn(new User(['id' => 1]));
    
    $controller = new UserController($mock);
    // Test logic...
}
```

### 3. Swappable Implementations

Change implementations without modifying dependent code:

```php
// config/app.php
'providers' => [
    // Use Eloquent in production
    App\Providers\EloquentUserRepositoryProvider::class,
    
    // Switch to MongoDB in development
    // App\Providers\MongoUserRepositoryProvider::class,
]
```

## Core Contracts

### Application Contract

The main application container:

```php
namespace Neo\Foundation\Contracts;

interface Application
{
    /**
     * Resolve a service from the container
     */
    public function make(string $abstract, array $parameters = []): mixed;
    
    /**
     * Bind a service to the container
     */
    public function bind(string $abstract, $concrete): void;
    
    /**
     * Register a singleton
     */
    public function singleton(string $abstract, $concrete): void;
    
    /**
     * Register an existing instance
     */
    public function instance(string $abstract, $instance): void;
    
    /**
     * Check if a service is bound
     */
    public function bound(string $abstract): bool;
}
```

**Usage:**

```php
use Neo\Foundation\Contracts\Application;

class MyService
{
    public function __construct(
        private Application $app
    ) {}
    
    public function doSomething()
    {
        $cache = $this->app->make('cache');
        $config = $this->app->make('config');
    }
}
```

### Cache Contract

```php
namespace Neo\Foundation\Contracts\Cache;

interface CacheInterface
{
    public function get(string $key, mixed $default = null): mixed;
    public function put(string $key, mixed $value, int $ttl = null): bool;
    public function has(string $key): bool;
    public function forget(string $key): bool;
    public function flush(): bool;
    public function remember(string $key, int $ttl, callable $callback): mixed;
}
```

**Usage:**

```php
use Neo\Foundation\Contracts\Cache\CacheInterface;

class ProductService
{
    public function __construct(
        private CacheInterface $cache
    ) {}
    
    public function getFeaturedProducts()
    {
        return $this->cache->remember('featured_products', 3600, function () {
            return Product::where('featured', true)->get();
        });
    }
}
```

### Database Contract

```php
namespace Neo\Foundation\Contracts\Database;

interface ConnectionInterface
{
    public function select(string $query, array $bindings = []): array;
    public function insert(string $query, array $bindings = []): bool;
    public function update(string $query, array $bindings = []): int;
    public function delete(string $query, array $bindings = []): int;
    public function transaction(callable $callback): mixed;
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
}
```

**Usage:**

```php
use Neo\Foundation\Contracts\Database\ConnectionInterface;

class ReportService
{
    public function __construct(
        private ConnectionInterface $db
    ) {}
    
    public function generateReport()
    {
        return $this->db->transaction(function () {
            $data = $this->db->select('SELECT * FROM reports WHERE status = ?', ['pending']);
            
            foreach ($data as $report) {
                $this->db->update('UPDATE reports SET status = ? WHERE id = ?', ['processed', $report->id]);
            }
            
            return $data;
        });
    }
}
```

### Event Dispatcher Contract

```php
namespace Neo\Foundation\Contracts\Events;

interface DispatcherInterface
{
    public function listen(string $event, $listener): void;
    public function dispatch(string $event, array $payload = []): void;
    public function forget(string $event): void;
    public function hasListeners(string $event): bool;
}
```

**Usage:**

```php
use Neo\Foundation\Contracts\Events\DispatcherInterface;

class OrderService
{
    public function __construct(
        private DispatcherInterface $events
    ) {}
    
    public function createOrder($data)
    {
        $order = Order::create($data);
        
        $this->events->dispatch('order.created', [$order]);
        
        return $order;
    }
}
```

### Mail Contract

```php
namespace Neo\Foundation\Contracts\Mail;

interface MailerInterface
{
    public function to($users): self;
    public function send(MailableInterface $mailable): void;
    public function queue(MailableInterface $mailable): void;
}
```

**Usage:**

```php
use Neo\Foundation\Contracts\Mail\MailerInterface;

class UserService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}
    
    public function registerUser($data)
    {
        $user = User::create($data);
        
        $this->mailer->to($user)->send(new WelcomeEmail($user));
        
        return $user;
    }
}
```

### Queue Contract

```php
namespace Neo\Foundation\Contracts\Queue;

interface QueueInterface
{
    public function push(JobInterface $job, $queue = null): void;
    public function later(int $delay, JobInterface $job, $queue = null): void;
    public function bulk(array $jobs, $queue = null): void;
}
```

**Usage:**

```php
use Neo\Foundation\Contracts\Queue\QueueInterface;

class VideoService
{
    public function __construct(
        private QueueInterface $queue
    ) {}
    
    public function processVideo($videoId)
    {
        $this->queue->push(new ProcessVideoJob($videoId));
        $this->queue->later(300, new GenerateThumbnailJob($videoId));
    }
}
```

## Custom Contracts

### Creating Your Own Contracts

Define interfaces for your domain:

```php
namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function charge(int $amount, string $currency): PaymentResult;
    public function refund(string $transactionId): bool;
    public function getBalance(): int;
}
```

### Implementing Contracts

```php
namespace App\Services;

use App\Contracts\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface
{
    public function charge(int $amount, string $currency): PaymentResult
    {
        // Stripe implementation
        $charge = \Stripe\Charge::create([
            'amount' => $amount,
            'currency' => $currency,
        ]);
        
        return new PaymentResult($charge);
    }
    
    public function refund(string $transactionId): bool
    {
        \Stripe\Refund::create(['charge' => $transactionId]);
        return true;
    }
    
    public function getBalance(): int
    {
        return \Stripe\Balance::retrieve()->available[0]->amount;
    }
}
```

### Binding Contracts

```php
namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Services\StripeGateway;
use Neo\Foundation\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, StripeGateway::class);
    }
}
```

### Using Contracts

```php
namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentGatewayInterface $gateway
    ) {}
    
    public function charge(Request $request)
    {
        $result = $this->gateway->charge(
            $request->input('amount'),
            $request->input('currency')
        );
        
        return response()->json($result);
    }
}
```

## Repository Pattern

### Repository Contract

```php
namespace App\Contracts;

interface RepositoryInterface
{
    public function find(int $id): ?Model;
    public function all(): Collection;
    public function create(array $data): Model;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function paginate(int $perPage = 15): Paginator;
}
```

### Implementation

```php
namespace App\Repositories;

use App\Contracts\RepositoryInterface;
use Neo\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface
{
    public function __construct(
        protected Model $model
    ) {}
    
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }
    
    public function all(): Collection
    {
        return $this->model->all();
    }
    
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }
    
    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data);
    }
    
    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }
    
    public function paginate(int $perPage = 15): Paginator
    {
        return $this->model->paginate($perPage);
    }
}
```

### Specific Repository

```php
namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }
    
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }
    
    public function getActiveUsers(): Collection
    {
        return $this->model->where('status', 'active')->get();
    }
}
```

## Best Practices

### 1. Program to Interfaces

```php
// Good
public function sendNotification(NotificationInterface $notification) {}

// Bad
public function sendNotification(EmailNotification $notification) {}
```

### 2. Inject Contracts, Not Implementations

```php
// Good
public function __construct(CacheInterface $cache) {}

// Bad  
public function __construct(RedisCache $cache) {}
```

### 3. Keep Contracts Focused

```php
// Good - Single responsibility
interface CacheInterface {
    public function get(string $key): mixed;
    public function put(string $key, mixed $value): void;
}

// Bad - Too many responsibilities
interface CacheInterface {
    public function get(string $key): mixed;
    public function put(string $key, mixed $value): void;
    public function sendEmail(string $to): void;  // Wrong!
    public function logMessage(string $message): void;  // Wrong!
}
```

### 4. Use Type Hints

```php
// Good
public function process(PaymentInterface $payment): PaymentResult {}

// Bad
public function process($payment) {}
```

## Next Steps

- [Service Providers](service-providers.md)
- [Dependency Injection](../service-providers/dependency-injection.md)
- [Testing with Mocks](../testing/getting-started.md)
