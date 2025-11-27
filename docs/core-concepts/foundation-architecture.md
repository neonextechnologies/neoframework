# Foundation Architecture

## Overview

NeoFramework is built as a **Foundation Framework** - a solid architectural foundation for building full-stack applications. Unlike traditional monolithic frameworks that come with everything built-in, NeoFramework provides a clean, contract-first architecture that you can extend as needed.

## Architecture Philosophy

```
Traditional Full Framework (Laravel, Symfony):
└── Everything built-in (Database, Auth, Queue, Cache, Mailer, etc.)

NeoFramework Foundation:
├── Foundation Layer ───── Contract-first architecture with pure interfaces
├── Plugin System ──────── WordPress-style hooks for extensibility
├── Service Providers ──── Deferred loading and dependency management
└── Metadata System ────── PHP 8 Attributes for declarative development
```

## Core Principles

### 1. Contract-First Design

Everything in NeoFramework starts with a contract (interface):

```php
namespace Neo\Foundation\Contracts;

interface Application
{
    public function make(string $abstract, array $parameters = []): mixed;
    public function bind(string $abstract, $concrete): void;
    public function singleton(string $abstract, $concrete): void;
    public function instance(string $abstract, $instance): void;
}
```

**Benefits:**
- Clear API boundaries
- Easy testing with mocks
- Swap implementations without breaking code
- Type safety with PHP 8+

### 2. Service Container

The Service Container is the heart of NeoFramework:

```php
use Neo\Foundation\Application;

$app = Application::getInstance();

// Binding
$app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

// Resolving
$repository = $app->make(UserRepositoryInterface::class);

// Singleton
$app->singleton(CacheManager::class, function ($app) {
    return new CacheManager($app['config']['cache']);
});

// Instance
$app->instance('path.base', '/var/www');
```

### 3. Service Providers

Service Providers are the central place for application bootstrapping:

```php
namespace App\Providers;

use Neo\Foundation\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->app->singleton(ApiClient::class, function ($app) {
            return new ApiClient($app['config']['api']);
        });
    }
    
    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Register routes, views, translations, etc.
    }
}
```

## Foundation Layers

### 1. Application Layer

The Application class is the foundation:

```php
// bootstrap/app.php
$app = new \Neo\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

// Register core bindings
$app->singleton(
    \Neo\Foundation\Contracts\Http\Kernel::class,
    \App\Http\Kernel::class
);

// Register service providers
$app->register(\App\Providers\AppServiceProvider::class);

return $app;
```

### 2. HTTP Layer

Request/Response handling:

```php
// public/index.php
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(\Neo\Foundation\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = \Neo\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
```

### 3. Console Layer

CLI application handling:

```php
// neo (CLI entry point)
$app = require __DIR__.'/bootstrap/app.php';

$kernel = $app->make(\Neo\Foundation\Contracts\Console\Kernel::class);

$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArgvInput,
    new Symfony\Component\Console\Output\ConsoleOutput
);

$kernel->terminate($input, $status);

exit($status);
```

## Modular Architecture

### Plugin System

NeoFramework supports WordPress-style hooks for extensibility:

```php
// Register a hook
Hook::addAction('user.created', function ($user) {
    Log::info("User created: {$user->email}");
    Mail::to($user)->send(new WelcomeEmail($user));
});

// Fire the hook
Hook::doAction('user.created', $user);

// Filters
Hook::addFilter('user.name', function ($name) {
    return ucwords($name);
});

$name = Hook::applyFilters('user.name', $user->name);
```

### Module Structure

Organize code into self-contained modules:

```
app/
├── Modules/
│   ├── User/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   └── routes.php
│   └── Blog/
│       ├── Controllers/
│       ├── Models/
│       └── routes.php
```

## Dependency Injection

### Constructor Injection

```php
namespace App\Http\Controllers;

use App\Services\UserService;
use Neo\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}
    
    public function index()
    {
        return $this->userService->getAllUsers();
    }
}
```

### Method Injection

```php
public function show(Request $request, UserRepository $users, int $id)
{
    $user = $users->find($id);
    
    return view('users.show', ['user' => $user]);
}
```

## Lifecycle

### Request Lifecycle

1. **Entry Point** - `public/index.php` receives request
2. **Bootstrap** - Load application from `bootstrap/app.php`
3. **Kernel** - HTTP Kernel handles request
4. **Middleware** - Request passes through middleware stack
5. **Router** - Route matches and controller is called
6. **Response** - Response is sent back to client
7. **Termination** - Kernel performs cleanup

### Service Provider Lifecycle

1. **Register** - All providers' `register()` methods called
2. **Boot** - All providers' `boot()` methods called
3. **Ready** - Application is ready to handle requests

## Best Practices

### 1. Use Contracts

```php
// Good
public function __construct(UserRepositoryInterface $users) {}

// Bad
public function __construct(EloquentUserRepository $users) {}
```

### 2. Single Responsibility

Each service provider should have one purpose:

```php
// Good
class DatabaseServiceProvider extends ServiceProvider {}
class ViewServiceProvider extends ServiceProvider {}

// Bad
class AppServiceProvider extends ServiceProvider {
    // Registers database, views, cache, mail, etc.
}
```

### 3. Deferred Providers

Use deferred providers for services not needed on every request:

```php
class AwsServiceProvider extends ServiceProvider
{
    protected $defer = true;
    
    public function provides(): array
    {
        return [S3Client::class];
    }
}
```

## Testing

Foundation architecture makes testing easy:

```php
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    public function test_can_create_user()
    {
        // Mock repository
        $this->mock(UserRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('create')
                 ->once()
                 ->andReturn(new User(['id' => 1]));
        });
        
        $service = $this->app->make(UserService::class);
        $user = $service->createUser(['email' => 'test@example.com']);
        
        $this->assertEquals(1, $user->id);
    }
}
```

## Next Steps

- [Contracts & Interfaces](contracts.md)
- [Service Providers](service-providers.md)
- [Hook System](hooks.md)
- [Plugins](plugins.md)
