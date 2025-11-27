# 📐 Code Style Guide

This guide outlines the coding standards and style conventions for NeoFramework. Following these guidelines ensures consistency, readability, and maintainability across the codebase.

## Table of Contents

- [PHP Standards](#php-standards)
- [Naming Conventions](#naming-conventions)
- [Documentation](#documentation)
- [Formatting Rules](#formatting-rules)
- [Best Practices](#best-practices)
- [Type Declarations](#type-declarations)
- [Error Handling](#error-handling)
- [Testing Standards](#testing-standards)

## 📋 PHP Standards

NeoFramework follows **PSR-12: Extended Coding Style** as the foundation for all PHP code.

### PSR Compliance

We adhere to the following PSRs:

- **PSR-1**: Basic Coding Standard
- **PSR-12**: Extended Coding Style
- **PSR-4**: Autoloading Standard
- **PSR-7**: HTTP Message Interface (for HTTP layer)
- **PSR-11**: Container Interface (for DI container)

### PHP Version

- **Minimum**: PHP 8.1
- **Target**: PHP 8.2+
- Use modern PHP features when appropriate

### File Structure

**Basic Template**:

```php
<?php

declare(strict_types=1);

namespace Neo\Http\Controllers;

use Neo\Foundation\Controller;
use Neo\Http\Request;
use Neo\Http\Response;

/**
 * Controller for handling user-related operations.
 */
class UserController extends Controller
{
    /**
     * Display a list of users.
     */
    public function index(Request $request): Response
    {
        // Method implementation
    }
}
```

### Declare Statement

Always use strict types:

```php
<?php

declare(strict_types=1);
```

### Namespace and Imports

**Ordering**:

```php
<?php

declare(strict_types=1);

namespace Neo\Http\Middleware;

// 1. PHP built-in classes
use Exception;
use RuntimeException;

// 2. Third-party packages
use Psr\Log\LoggerInterface;

// 3. Framework classes (alphabetically)
use Neo\Foundation\Application;
use Neo\Http\Request;
use Neo\Http\Response;

// 4. Traits
use Neo\Traits\Loggable;

class ExampleMiddleware
{
    // Class content
}
```

**Import Rules**:

```php
// ✅ Good - One import per line
use Neo\Http\Request;
use Neo\Http\Response;

// ❌ Bad - Multiple imports on one line
use Neo\Http\Request, Neo\Http\Response;

// ✅ Good - Group imports for same namespace
use Neo\Database\{Connection, Query, Schema};

// ✅ Good - Alias to avoid conflicts
use Neo\Support\Collection;
use Illuminate\Support\Collection as IlluminateCollection;
```

## 🏷️ Naming Conventions

### Classes

Use **PascalCase** for all class names:

```php
// ✅ Good
class UserController { }
class HttpKernel { }
class DatabaseConnection { }
class JSONResponse { }

// ❌ Bad
class userController { }
class httpKernel { }
class database_connection { }
```

### Interfaces

Use **PascalCase** with `Interface` suffix:

```php
// ✅ Good
interface CacheInterface { }
interface RepositoryInterface { }
interface ValidationRuleInterface { }

// ❌ Bad
interface Cache { }
interface IRepository { }
interface Validation_Rule { }
```

### Traits

Use **PascalCase**:

```php
// ✅ Good
trait HasTimestamps { }
trait Cacheable { }
trait ValidatesInput { }

// ❌ Bad
trait has_timestamps { }
trait CacheableTrait { } // Avoid 'Trait' suffix
```

### Methods

Use **camelCase** for method names:

```php
class UserService
{
    // ✅ Good
    public function getUser(int $id): ?User { }
    
    public function createUser(array $data): User { }
    
    public function isActive(): bool { }
    
    public function hasPermission(string $permission): bool { }
    
    // ❌ Bad
    public function GetUser(int $id): ?User { }
    
    public function create_user(array $data): User { }
    
    public function is_active(): bool { }
}
```

**Method Naming Patterns**:

```php
// Getters - start with 'get'
public function getEmail(): string { }
public function getUserName(): string { }

// Setters - start with 'set'
public function setEmail(string $email): void { }
public function setUserName(string $name): void { }

// Boolean methods - start with 'is', 'has', 'can', 'should'
public function isValid(): bool { }
public function hasAccess(): bool { }
public function canEdit(): bool { }
public function shouldRetry(): bool { }

// Action methods - use verbs
public function createAccount(): void { }
public function sendEmail(): void { }
public function processPayment(): void { }
```

### Properties

Use **camelCase** for property names:

```php
class User
{
    // ✅ Good
    private string $firstName;
    private string $lastName;
    private ?int $userId = null;
    protected array $attributes = [];
    
    // ❌ Bad
    private string $first_name;
    private string $FirstName;
    private ?int $user_id = null;
}
```

### Constants

Use **UPPER_CASE** with underscores:

```php
class HttpStatus
{
    // ✅ Good
    public const OK = 200;
    public const NOT_FOUND = 404;
    public const INTERNAL_SERVER_ERROR = 500;
    public const MAX_RETRY_ATTEMPTS = 3;
    
    // ❌ Bad
    public const Ok = 200;
    public const notFound = 404;
    public const internalServerError = 500;
}
```

### Variables

Use **camelCase** and descriptive names:

```php
// ✅ Good
$userName = 'John Doe';
$totalAmount = 1000;
$isActive = true;
$userCollection = collect($users);

// ❌ Bad
$user_name = 'John Doe';
$n = 'John Doe';
$tmp = 1000;
$flag = true;
```

**Special Cases**:

```php
// Loop counters
for ($i = 0; $i < count($items); $i++) { }

// Exceptions
try {
    // Code
} catch (Exception $e) {
    // Handle exception
}

// Short-lived variables in closures
$users->filter(fn($u) => $u->isActive());
```

## 📝 Documentation

### Class Documentation

Every class should have a DocBlock:

```php
/**
 * Handles user authentication and authorization.
 * 
 * This class provides methods for logging in, logging out,
 * and checking user permissions.
 * 
 * @package Neo\Auth
 * @author Your Name <your.email@example.com>
 * @since 1.0.0
 */
class AuthManager
{
    // Class content
}
```

### Method Documentation

Document all public and protected methods:

```php
/**
 * Authenticate a user with the given credentials.
 * 
 * This method validates the provided credentials and creates
 * a new session if authentication is successful.
 * 
 * @param array $credentials The user credentials (email and password)
 * @param bool $remember Whether to remember the user
 * @return User|null The authenticated user or null on failure
 * @throws AuthenticationException If credentials are invalid
 * 
 * @example
 * ```php
 * $user = $auth->login([
 *     'email' => 'user@example.com',
 *     'password' => 'secret123'
 * ], true);
 * ```
 */
public function login(array $credentials, bool $remember = false): ?User
{
    // Method implementation
}
```

### Property Documentation

Document class properties:

```php
class UserRepository
{
    /**
     * The database connection instance.
     * 
     * @var Connection
     */
    private Connection $connection;
    
    /**
     * The cache driver instance.
     * 
     * @var CacheInterface
     */
    private CacheInterface $cache;
    
    /**
     * Default query options.
     * 
     * @var array<string, mixed>
     */
    protected array $defaultOptions = [
        'limit' => 10,
        'offset' => 0,
    ];
}
```

### Inline Comments

Use inline comments for complex logic:

```php
public function calculateDiscount(float $total): float
{
    // Apply base discount for orders over $100
    if ($total > 100) {
        $discount = $total * 0.1;
    }
    
    // Additional discount for premium members
    if ($this->user->isPremium()) {
        // Premium members get an extra 5% on top of base discount
        $discount += $total * 0.05;
    }
    
    return min($discount, $total * 0.5); // Cap at 50% of total
}
```

### PHPDoc Tags

Use standard PHPDoc tags:

```php
/**
 * Process a payment transaction.
 * 
 * @param float $amount The payment amount
 * @param string $currency The currency code (e.g., 'USD')
 * @param array $metadata Additional transaction metadata
 * 
 * @return Transaction The created transaction
 * 
 * @throws InvalidAmountException If amount is negative
 * @throws PaymentGatewayException If payment processing fails
 * 
 * @see Transaction
 * @link https://docs.neoframework.com/payments
 * 
 * @since 1.2.0
 * @deprecated 2.0.0 Use PaymentService::process() instead
 */
public function processPayment(
    float $amount,
    string $currency,
    array $metadata = []
): Transaction {
    // Implementation
}
```

## 🎨 Formatting Rules

### Indentation

Use **4 spaces** (no tabs):

```php
class Example
{
    public function method()
    {
        if ($condition) {
            // 4 spaces
            $result = true;
        }
    }
}
```

### Line Length

- **Preferred**: 80 characters
- **Maximum**: 120 characters
- Break long lines at logical points

```php
// ✅ Good - Break at logical points
$result = $this->repository
    ->where('status', 'active')
    ->where('created_at', '>=', $startDate)
    ->orderBy('name')
    ->get();

// ❌ Bad - One very long line
$result = $this->repository->where('status', 'active')->where('created_at', '>=', $startDate)->orderBy('name')->get();
```

### Braces

Always use braces, even for single-line statements:

```php
// ✅ Good
if ($condition) {
    return true;
}

// ❌ Bad
if ($condition) return true;

// ✅ Good - Opening brace on same line for classes/methods
class Example
{
    public function method()
    {
        // Code
    }
}
```

### Control Structures

```php
// If-else
if ($condition) {
    // Code
} elseif ($otherCondition) {
    // Code
} else {
    // Code
}

// Switch
switch ($value) {
    case 'option1':
        // Code
        break;
        
    case 'option2':
    case 'option3':
        // Code
        break;
        
    default:
        // Code
        break;
}

// Loops
foreach ($items as $key => $value) {
    // Code
}

while ($condition) {
    // Code
}

for ($i = 0; $i < $count; $i++) {
    // Code
}
```

### Arrays

```php
// ✅ Good - Short syntax
$array = ['item1', 'item2', 'item3'];

// ✅ Good - Multi-line
$array = [
    'key1' => 'value1',
    'key2' => 'value2',
    'key3' => 'value3',
];

// ✅ Good - Nested arrays
$config = [
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
    ],
    'cache' => [
        'driver' => 'redis',
        'ttl' => 3600,
    ],
];

// ❌ Bad - Old array syntax
$array = array('item1', 'item2');
```

### Strings

```php
// ✅ Good - Single quotes for simple strings
$name = 'John Doe';

// ✅ Good - Double quotes for interpolation
$greeting = "Hello, {$name}!";

// ✅ Good - Concatenation
$fullName = $firstName . ' ' . $lastName;

// ✅ Good - Multi-line strings
$html = <<<HTML
<div class="container">
    <h1>{$title}</h1>
    <p>{$content}</p>
</div>
HTML;
```

## 🎯 Best Practices

### Dependency Injection

Always use constructor injection:

```php
// ✅ Good
class UserService
{
    public function __construct(
        private UserRepository $repository,
        private CacheInterface $cache,
        private LoggerInterface $logger
    ) {}
}

// ❌ Bad - Direct instantiation
class UserService
{
    private UserRepository $repository;
    
    public function __construct()
    {
        $this->repository = new UserRepository();
    }
}
```

### Single Responsibility

Keep classes focused:

```php
// ✅ Good - Each class has one responsibility
class UserValidator
{
    public function validate(array $data): array { }
}

class UserCreator
{
    public function create(array $data): User { }
}

class UserNotifier
{
    public function sendWelcomeEmail(User $user): void { }
}

// ❌ Bad - One class doing too much
class UserManager
{
    public function validate(array $data): array { }
    public function create(array $data): User { }
    public function sendEmail(User $user): void { }
    public function generateReport(): string { }
}
```

### Early Returns

Use early returns to reduce nesting:

```php
// ✅ Good
public function process(?User $user): Response
{
    if ($user === null) {
        return $this->error('User not found');
    }
    
    if (!$user->isActive()) {
        return $this->error('User is not active');
    }
    
    return $this->success($user->process());
}

// ❌ Bad
public function process(?User $user): Response
{
    if ($user !== null) {
        if ($user->isActive()) {
            return $this->success($user->process());
        } else {
            return $this->error('User is not active');
        }
    } else {
        return $this->error('User not found');
    }
}
```

### Null Coalescing

Use null coalescing operators:

```php
// ✅ Good
$name = $user->getName() ?? 'Guest';
$config = $options['config'] ?? $this->getDefaultConfig();

// Assignment
$this->cache ??= new FileCache();

// ❌ Bad
$name = isset($user) ? $user->getName() : 'Guest';
```

## 🔤 Type Declarations

### Strict Typing

Always declare parameter and return types:

```php
// ✅ Good
public function calculateTotal(float $price, int $quantity): float
{
    return $price * $quantity;
}

// ❌ Bad
public function calculateTotal($price, $quantity)
{
    return $price * $quantity;
}
```

### Nullable Types

Use nullable types when appropriate:

```php
// ✅ Good
public function findUser(int $id): ?User
{
    return $this->repository->find($id);
}

// ✅ Good - PHP 8.0+ union types
public function getValue(): int|float|null
{
    return $this->value;
}
```

### Type Hints

```php
// Scalar types
public function setName(string $name): void { }
public function setAge(int $age): void { }
public function setPrice(float $price): void { }
public function setActive(bool $active): void { }

// Arrays
public function setOptions(array $options): void { }

// Objects
public function setUser(User $user): void { }

// Mixed
public function setValue(mixed $value): void { }

// Void
public function logMessage(string $message): void { }

// Never (PHP 8.1+)
public function fail(): never
{
    throw new Exception('Failed');
}
```

## ⚠️ Error Handling

### Exceptions

Use specific exception types:

```php
// ✅ Good
if ($amount < 0) {
    throw new InvalidArgumentException('Amount cannot be negative');
}

if (!$user) {
    throw new UserNotFoundException("User #{$id} not found");
}

// ❌ Bad
if ($amount < 0) {
    throw new Exception('Error');
}
```

### Try-Catch

```php
// ✅ Good - Specific exceptions
try {
    $result = $this->process($data);
} catch (ValidationException $e) {
    $this->logger->warning('Validation failed', ['error' => $e->getMessage()]);
    return $this->error($e->getErrors());
} catch (DatabaseException $e) {
    $this->logger->error('Database error', ['error' => $e->getMessage()]);
    return $this->error('An error occurred');
}

// ✅ Good - Finally block for cleanup
try {
    $file = fopen($path, 'r');
    $content = fread($file, filesize($path));
} finally {
    if (isset($file)) {
        fclose($file);
    }
}
```

## 🧪 Testing Standards

### Test Naming

```php
// ✅ Good - Descriptive test names
public function test_user_can_login_with_valid_credentials(): void { }

public function test_user_cannot_login_with_invalid_password(): void { }

public function test_user_is_redirected_after_login(): void { }

// ❌ Bad
public function testLogin(): void { }
public function test1(): void { }
```

### Test Structure

Follow Arrange-Act-Assert pattern:

```php
public function test_user_can_be_created(): void
{
    // Arrange
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ];
    
    // Act
    $user = User::create($data);
    
    // Assert
    $this->assertInstanceOf(User::class, $user);
    $this->assertEquals('John Doe', $user->name);
    $this->assertEquals('john@example.com', $user->email);
}
```

## 🛠️ Tools

### PHP CS Fixer

Configure `.php-cs-fixer.php`:

```php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/app')
    ->in(__DIR__ . '/tests');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
    ])
    ->setFinder($finder);
```

Run the fixer:

```bash
vendor/bin/php-cs-fixer fix
```

### PHPStan

Configure `phpstan.neon`:

```yaml
parameters:
    level: 8
    paths:
        - src
        - app
    excludePaths:
        - vendor
```

Run analysis:

```bash
vendor/bin/phpstan analyse
```

---

Following these coding standards ensures that NeoFramework maintains high code quality and consistency across all contributions. 🚀
