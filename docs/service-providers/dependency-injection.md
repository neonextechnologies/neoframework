# Dependency Injection 💉

## Introduction

Dependency Injection (DI) is a design pattern where objects receive their dependencies from external sources rather than creating them internally. It's one of the most important patterns in modern PHP development and is at the core of NeoFramework's architecture.

DI makes your code more modular, testable, and maintainable by decoupling classes from their dependencies. Instead of hard-coding dependencies, you "inject" them, making it easy to swap implementations and test in isolation.

## Understanding Dependency Injection

### The Problem: Tight Coupling

Without dependency injection, classes create their own dependencies:

```php
// ❌ Bad: Tightly coupled
class UserService
{
    private $logger;
    private $database;
    
    public function __construct()
    {
        // Hard-coded dependencies
        $this->logger = new FileLogger('/var/log/app.log');
        $this->database = new MySqlDatabase('localhost', 'mydb');
    }
    
    public function createUser(array $data)
    {
        $this->logger->info('Creating user');
        $this->database->insert('users', $data);
    }
}

// Problems:
// - Can't use different logger or database
// - Hard to test (can't mock dependencies)
// - Changes to dependencies require changing this class
// - Can't configure without modifying code
```

### The Solution: Dependency Injection

With DI, dependencies are passed to the class:

```php
// ✅ Good: Dependencies injected
class UserService
{
    public function __construct(
        private LoggerInterface $logger,
        private DatabaseInterface $database
    ) {}
    
    public function createUser(array $data)
    {
        $this->logger->info('Creating user');
        $this->database->insert('users', $data);
    }
}

// Benefits:
// - Easy to swap implementations
// - Simple to test with mocks
// - Configuration happens externally
// - Follows SOLID principles
```

## Types of Dependency Injection

### 1. Constructor Injection (Recommended)

Dependencies are passed through the constructor:

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
        // All dependencies available
        $order = $this->orders->create($data);
        $this->payment->charge($order);
        $this->email->sendConfirmation($order);
        $this->logger->info("Order {$order->id} created");
        
        return $order;
    }
}

// Container automatically resolves and injects all dependencies
$service = app(OrderService::class);
```

**Advantages:**
- Dependencies clearly visible
- Immutable after construction
- Forces proper initialization
- IDE-friendly with type hints

### 2. Method Injection

Dependencies are passed to specific methods:

```php
class ReportController
{
    // Method injection for route handlers
    public function generate(
        Request $request,
        ReportGenerator $generator,
        Logger $logger
    ) {
        $logger->info('Generating report', [
            'type' => $request->input('type')
        ]);
        
        $report = $generator->generate(
            $request->input('type'),
            $request->input('filters', [])
        );
        
        return response()->download($report->path());
    }
    
    // Another action with different dependencies
    public function email(
        Request $request,
        ReportGenerator $generator,
        EmailService $mailer
    ) {
        $report = $generator->generate($request->input('type'));
        $mailer->send($request->user()->email, $report);
        
        return redirect()->back()->with('success', 'Report emailed');
    }
}

// Router automatically injects dependencies for each method
Route::post('/reports/generate', [ReportController::class, 'generate']);
Route::post('/reports/email', [ReportController::class, 'email']);
```

**When to use:**
- Controller actions
- Command handlers
- Event listeners
- Dependencies only needed for specific operations

### 3. Setter Injection (Use Sparingly)

Dependencies set through setter methods:

```php
class NewsletterService
{
    private ?LoggerInterface $logger = null;
    private ?CacheInterface $cache = null;
    
    // Optional dependency with setter
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
    
    public function setCache(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }
    
    public function send(string $content, array $recipients): void
    {
        // Use if available
        if ($this->logger) {
            $this->logger->info('Sending newsletter to ' . count($recipients) . ' recipients');
        }
        
        // Send logic...
    }
}

// Set optional dependencies
$service = new NewsletterService();
$service->setLogger(app(Logger::class));
$service->send($content, $recipients);
```

**When to use:**
- Optional dependencies
- Configuration after construction
- Legacy code refactoring

**Prefer constructor injection when possible.**

## Dependency Injection Patterns 🎯

### Interface Segregation

Depend on interfaces, not implementations:

```php
// Define interfaces
interface UserRepositoryInterface
{
    public function find(int $id): ?User;
    public function save(User $user): void;
}

interface CacheInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl): void;
}

interface LoggerInterface
{
    public function info(string $message, array $context = []): void;
    public function error(string $message, array $context = []): void;
}

// Depend on interfaces
class UserService
{
    public function __construct(
        private UserRepositoryInterface $users,
        private CacheInterface $cache,
        private LoggerInterface $logger
    ) {}
    
    public function getUser(int $id): ?User
    {
        return $this->cache->remember("user.{$id}", 3600, function () use ($id) {
            $this->logger->info("Fetching user {$id} from database");
            return $this->users->find($id);
        });
    }
}

// Bind implementations in service provider
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(CacheInterface::class, RedisCache::class);
        $this->app->bind(LoggerInterface::class, FileLogger::class);
    }
}
```

### Factory Pattern with DI

Combine factory pattern with dependency injection:

```php
interface PaymentGatewayFactoryInterface
{
    public function create(string $type): PaymentGatewayInterface;
}

class PaymentGatewayFactory implements PaymentGatewayFactoryInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ConfigInterface $config
    ) {}
    
    public function create(string $type): PaymentGatewayInterface
    {
        return match($type) {
            'stripe' => new StripeGateway(
                $this->config->get('services.stripe.key'),
                $this->logger
            ),
            'paypal' => new PayPalGateway(
                $this->config->get('services.paypal.client_id'),
                $this->config->get('services.paypal.secret'),
                $this->logger
            ),
            'square' => new SquareGateway(
                $this->config->get('services.square.token'),
                $this->logger
            ),
            default => throw new \InvalidArgumentException("Unknown gateway: {$type}")
        };
    }
}

// Usage in service
class PaymentService
{
    public function __construct(
        private PaymentGatewayFactoryInterface $gatewayFactory
    ) {}
    
    public function charge(Order $order, string $gateway = 'stripe'): Payment
    {
        $gateway = $this->gatewayFactory->create($gateway);
        return $gateway->charge($order->total, $order->paymentMethod);
    }
}
```

### Strategy Pattern with DI

Inject different strategies:

```php
interface PricingStrategyInterface
{
    public function calculate(Product $product, Customer $customer): float;
}

class RegularPricingStrategy implements PricingStrategyInterface
{
    public function calculate(Product $product, Customer $customer): float
    {
        return $product->price;
    }
}

class WholesalePricingStrategy implements PricingStrategyInterface
{
    public function __construct(
        private float $discountPercent = 20
    ) {}
    
    public function calculate(Product $product, Customer $customer): float
    {
        return $product->price * (1 - $this->discountPercent / 100);
    }
}

class PremiumPricingStrategy implements PricingStrategyInterface
{
    public function __construct(
        private CacheInterface $cache
    ) {}
    
    public function calculate(Product $product, Customer $customer): float
    {
        if ($customer->loyaltyPoints > 1000) {
            return $product->price * 0.85; // 15% off
        }
        
        return $product->price * 0.90; // 10% off
    }
}

class PricingService
{
    public function __construct(
        private PricingStrategyInterface $strategy
    ) {}
    
    public function getPrice(Product $product, Customer $customer): float
    {
        return $this->strategy->calculate($product, $customer);
    }
}

// Bind based on customer type
app()->when(PricingService::class)
    ->needs(PricingStrategyInterface::class)
    ->give(function ($app) {
        $customer = auth()->user();
        
        return match($customer->type) {
            'wholesale' => new WholesalePricingStrategy(),
            'premium' => $app->make(PremiumPricingStrategy::class),
            default => new RegularPricingStrategy()
        };
    });
```

### Repository Pattern with DI

```php
// Repository interface
interface PostRepositoryInterface
{
    public function find(int $id): ?Post;
    public function all(): Collection;
    public function create(array $data): Post;
    public function update(int $id, array $data): Post;
    public function delete(int $id): bool;
}

// Implementation
class EloquentPostRepository implements PostRepositoryInterface
{
    public function __construct(
        private DatabaseInterface $db,
        private CacheInterface $cache
    ) {}
    
    public function find(int $id): ?Post
    {
        return $this->cache->remember("post.{$id}", 3600, function () use ($id) {
            return $this->db->table('posts')->find($id);
        });
    }
    
    public function all(): Collection
    {
        return $this->cache->remember('posts.all', 3600, function () {
            return $this->db->table('posts')->orderBy('created_at', 'desc')->get();
        });
    }
    
    public function create(array $data): Post
    {
        $post = $this->db->table('posts')->insert($data);
        $this->cache->forget('posts.all');
        return $post;
    }
    
    public function update(int $id, array $data): Post
    {
        $post = $this->db->table('posts')->where('id', $id)->update($data);
        $this->cache->forget("post.{$id}");
        $this->cache->forget('posts.all');
        return $post;
    }
    
    public function delete(int $id): bool
    {
        $result = $this->db->table('posts')->where('id', $id)->delete();
        $this->cache->forget("post.{$id}");
        $this->cache->forget('posts.all');
        return $result;
    }
}

// Service using repository
class PostService
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private EventDispatcher $events,
        private LoggerInterface $logger
    ) {}
    
    public function publishPost(int $id): Post
    {
        $post = $this->posts->find($id);
        
        if (!$post) {
            throw new PostNotFoundException("Post {$id} not found");
        }
        
        $this->logger->info("Publishing post {$id}");
        
        $post = $this->posts->update($id, [
            'status' => 'published',
            'published_at' => now(),
        ]);
        
        $this->events->dispatch(new PostPublished($post));
        
        return $post;
    }
}
```

## Real-World Examples 🌍

### E-Commerce Order Processing

```php
<?php

// Interfaces
interface OrderRepositoryInterface
{
    public function create(array $data): Order;
    public function update(Order $order): void;
}

interface PaymentGatewayInterface
{
    public function charge(float $amount, PaymentMethod $method): Payment;
}

interface InventoryServiceInterface
{
    public function reserve(array $items): bool;
    public function release(array $items): void;
}

interface NotificationServiceInterface
{
    public function sendOrderConfirmation(Order $order): void;
    public function sendPaymentReceipt(Payment $payment): void;
}

// Service with all dependencies injected
class OrderProcessingService
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private PaymentGatewayInterface $payment,
        private InventoryServiceInterface $inventory,
        private NotificationServiceInterface $notifications,
        private EventDispatcher $events,
        private LoggerInterface $logger
    ) {}
    
    public function processOrder(array $orderData, PaymentMethod $paymentMethod): Order
    {
        $this->logger->info('Processing new order');
        
        try {
            // Reserve inventory
            if (!$this->inventory->reserve($orderData['items'])) {
                throw new InsufficientInventoryException();
            }
            
            // Create order
            $order = $this->orders->create($orderData);
            
            // Process payment
            $payment = $this->payment->charge($order->total, $paymentMethod);
            
            // Update order
            $order->payment_id = $payment->id;
            $order->status = 'completed';
            $this->orders->update($order);
            
            // Send notifications
            $this->notifications->sendOrderConfirmation($order);
            $this->notifications->sendPaymentReceipt($payment);
            
            // Dispatch events
            $this->events->dispatch(new OrderCompleted($order));
            
            $this->logger->info("Order {$order->id} completed successfully");
            
            return $order;
            
        } catch (\Exception $e) {
            $this->logger->error("Order processing failed: {$e->getMessage()}");
            
            // Rollback inventory
            if (isset($order)) {
                $this->inventory->release($orderData['items']);
            }
            
            throw $e;
        }
    }
}

// Controller with automatic dependency injection
class CheckoutController
{
    public function __construct(
        private OrderProcessingService $orderService
    ) {}
    
    public function process(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);
        
        $paymentMethod = PaymentMethod::findOrFail($validated['payment_method_id']);
        
        try {
            $order = $this->orderService->processOrder(
                $validated,
                $paymentMethod
            );
            
            return response()->json([
                'success' => true,
                'order_id' => $order->id,
            ]);
        } catch (InsufficientInventoryException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Some items are out of stock',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Payment processing failed',
            ], 500);
        }
    }
}
```

### Multi-Step Form Wizard

```php
<?php

interface FormStepInterface
{
    public function validate(array $data): array;
    public function process(array $data, FormState $state): void;
    public function canProceed(FormState $state): bool;
}

class PersonalInfoStep implements FormStepInterface
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}
    
    public function validate(array $data): array
    {
        return $this->validator->validate($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|phone',
        ]);
    }
    
    public function process(array $data, FormState $state): void
    {
        $state->set('personal_info', $data);
    }
    
    public function canProceed(FormState $state): bool
    {
        return $state->has('personal_info');
    }
}

class AddressStep implements FormStepInterface
{
    public function __construct(
        private ValidatorInterface $validator,
        private GeolocationService $geo
    ) {}
    
    public function validate(array $data): array
    {
        return $this->validator->validate($data, [
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip' => 'required|postal_code',
        ]);
    }
    
    public function process(array $data, FormState $state): void
    {
        // Enhance with geolocation
        $coordinates = $this->geo->geocode($data);
        $data['lat'] = $coordinates->lat;
        $data['lng'] = $coordinates->lng;
        
        $state->set('address', $data);
    }
    
    public function canProceed(FormState $state): bool
    {
        return $state->has('address');
    }
}

class PaymentStep implements FormStepInterface
{
    public function __construct(
        private ValidatorInterface $validator,
        private PaymentValidator $paymentValidator
    ) {}
    
    public function validate(array $data): array
    {
        $errors = $this->validator->validate($data, [
            'card_number' => 'required|credit_card',
            'expiry' => 'required|date_format:m/Y',
            'cvv' => 'required|digits:3',
        ]);
        
        // Additional payment validation
        if (!$this->paymentValidator->validateCard($data['card_number'])) {
            $errors['card_number'] = 'Invalid card number';
        }
        
        return $errors;
    }
    
    public function process(array $data, FormState $state): void
    {
        $state->set('payment', $data);
    }
    
    public function canProceed(FormState $state): bool
    {
        return $state->has('payment');
    }
}

class FormWizardService
{
    private array $steps = [];
    
    public function __construct(
        private FormStateRepository $stateRepo,
        private LoggerInterface $logger
    ) {}
    
    public function addStep(string $name, FormStepInterface $step): void
    {
        $this->steps[$name] = $step;
    }
    
    public function processStep(string $stepName, array $data, string $sessionId): array
    {
        if (!isset($this->steps[$stepName])) {
            throw new \InvalidArgumentException("Unknown step: {$stepName}");
        }
        
        $step = $this->steps[$stepName];
        $state = $this->stateRepo->get($sessionId);
        
        // Validate
        $errors = $step->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Process
        $step->process($data, $state);
        $this->stateRepo->save($state);
        
        $this->logger->info("Step {$stepName} completed", [
            'session_id' => $sessionId
        ]);
        
        return ['success' => true, 'state' => $state];
    }
    
    public function canComplete(string $sessionId): bool
    {
        $state = $this->stateRepo->get($sessionId);
        
        foreach ($this->steps as $step) {
            if (!$step->canProceed($state)) {
                return false;
            }
        }
        
        return true;
    }
}

// Service provider registration
class FormWizardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FormWizardService::class, function ($app) {
            $wizard = new FormWizardService(
                $app->make(FormStateRepository::class),
                $app->make(LoggerInterface::class)
            );
            
            // Register steps with their dependencies auto-injected
            $wizard->addStep('personal', $app->make(PersonalInfoStep::class));
            $wizard->addStep('address', $app->make(AddressStep::class));
            $wizard->addStep('payment', $app->make(PaymentStep::class));
            
            return $wizard;
        });
    }
}
```

## Testing with Dependency Injection 🧪

DI makes testing incredibly easy:

### Mocking Dependencies

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\UserService;
use App\Repositories\UserRepositoryInterface;
use App\Services\CacheInterface;
use App\Services\LoggerInterface;

class UserServiceTest extends TestCase
{
    public function test_get_user_returns_cached_user(): void
    {
        // Create mocks
        $userRepo = $this->createMock(UserRepositoryInterface::class);
        $cache = $this->createMock(CacheInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        
        // Setup expectations
        $cache->expects($this->once())
            ->method('has')
            ->with('user.1')
            ->willReturn(true);
        
        $cache->expects($this->once())
            ->method('get')
            ->with('user.1')
            ->willReturn(new User(['id' => 1, 'name' => 'John']));
        
        // Repository should NOT be called
        $userRepo->expects($this->never())
            ->method('find');
        
        // Inject mocks
        $service = new UserService($userRepo, $cache, $logger);
        
        // Test
        $user = $service->getUser(1);
        
        $this->assertEquals('John', $user->name);
    }
    
    public function test_get_user_fetches_from_repo_when_not_cached(): void
    {
        $userRepo = $this->createMock(UserRepositoryInterface::class);
        $cache = $this->createMock(CacheInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        
        $cache->method('has')->willReturn(false);
        
        $userRepo->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(new User(['id' => 1, 'name' => 'John']));
        
        $cache->expects($this->once())
            ->method('set')
            ->with('user.1', $this->isInstanceOf(User::class));
        
        $service = new UserService($userRepo, $cache, $logger);
        $user = $service->getUser(1);
        
        $this->assertEquals('John', $user->name);
    }
}
```

### Using Test Doubles

```php
// Test implementation
class FakeEmailService implements EmailServiceInterface
{
    public array $sent = [];
    
    public function send(string $to, string $subject, string $body): void
    {
        $this->sent[] = compact('to', 'subject', 'body');
    }
    
    public function assertSent(string $to): void
    {
        $found = collect($this->sent)->contains(fn($email) => $email['to'] === $to);
        Assert::assertTrue($found, "Email not sent to {$to}");
    }
}

// Test
public function test_user_receives_welcome_email(): void
{
    $fakeEmail = new FakeEmailService();
    
    $service = new UserService(
        app(UserRepository::class),
        $fakeEmail,
        app(Logger::class)
    );
    
    $user = $service->createUser(['email' => 'john@example.com']);
    
    $fakeEmail->assertSent('john@example.com');
}
```

## Best Practices 📋

### 1. Depend on Abstractions

```php
// ✅ Good: Depends on interface
public function __construct(private CacheInterface $cache) {}

// ❌ Bad: Depends on concrete class
public function __construct(private RedisCache $cache) {}
```

### 2. Constructor Injection Over Setter Injection

```php
// ✅ Good: Required dependencies in constructor
public function __construct(
    private UserRepository $users,
    private Logger $logger
) {}

// ❌ Bad: Unclear what's required
private $users;
private $logger;

public function setUsers(UserRepository $users) { /*...*/ }
public function setLogger(Logger $logger) { /*...*/ }
```

### 3. Avoid Too Many Dependencies

```php
// ⚠️ Code smell: Too many dependencies
public function __construct(
    private ServiceA $a,
    private ServiceB $b,
    private ServiceC $c,
    private ServiceD $d,
    private ServiceE $e,
    private ServiceF $f,
    private ServiceG $g
) {}

// ✅ Better: Refactor into smaller classes or use facade
public function __construct(
    private OrderManager $orderManager,
    private PaymentManager $paymentManager
) {}
```

### 4. Use Type Hints

```php
// ✅ Good: Type-hinted for auto-resolution
public function __construct(
    private Logger $logger,
    private Cache $cache
) {}

// ❌ Bad: No type hints
public function __construct($logger, $cache) {}
```

### 5. Avoid Service Locator Pattern

```php
// ❌ Bad: Service locator anti-pattern
class Service
{
    public function doSomething()
    {
        $logger = app(Logger::class);
        $cache = app(Cache::class);
        // Hidden dependencies!
    }
}

// ✅ Good: Explicit dependencies
class Service
{
    public function __construct(
        private Logger $logger,
        private Cache $cache
    ) {}
    
    public function doSomething()
    {
        // Dependencies clearly visible
    }
}
```

## Common Pitfalls ⚠️

### 1. Circular Dependencies

```php
// ❌ ServiceA depends on ServiceB
class ServiceA
{
    public function __construct(private ServiceB $b) {}
}

// ❌ ServiceB depends on ServiceA - CIRCULAR!
class ServiceB
{
    public function __construct(private ServiceA $a) {}
}

// ✅ Solution: Extract shared logic
class ServiceA
{
    public function __construct(private SharedLogic $shared) {}
}

class ServiceB
{
    public function __construct(private SharedLogic $shared) {}
}
```

### 2. God Objects

```php
// ❌ Too many responsibilities
class ApplicationService
{
    public function __construct(
        private UserService $users,
        private PostService $posts,
        private CommentService $comments,
        private PaymentService $payments,
        // Too many!
    ) {}
}

// ✅ Split into focused services
class UserManagementService
{
    public function __construct(private UserService $users) {}
}

class ContentManagementService
{
    public function __construct(
        private PostService $posts,
        private CommentService $comments
    ) {}
}
```

### 3. Leaky Abstractions

```php
// ❌ Interface exposes implementation details
interface CacheInterface
{
    public function getRedisConnection(); // Redis-specific!
}

// ✅ Interface is implementation-agnostic
interface CacheInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl): void;
    public function delete(string $key): bool;
}
```

## Related Documentation

- [Service Container](container.md) - The IoC container
- [Service Providers](introduction.md) - Registering services
- [Testing](../testing/getting-started.md) - Testing with DI
- [SOLID Principles](../architecture/solid-principles.md) - Design principles

## Next Steps

Master dependency injection:

1. **[Service Container](container.md)** - Deep dive into the container
2. **[Facades](facades.md)** - Static access pattern
3. **[Testing](../testing/getting-started.md)** - Test with dependency injection
4. **[Architecture](../architecture/overview.md)** - Application architecture
