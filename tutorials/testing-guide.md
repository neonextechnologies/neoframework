# Testing Guide

## Overview

NeoFramework provides a comprehensive testing framework built on PHPUnit with Laravel-inspired testing helpers.

## Setup

### 1. Install PHPUnit

```bash
composer require --dev phpunit/phpunit
```

### 2. Configuration

The `phpunit.xml` file is already configured:

```xml
<phpunit bootstrap="tests/bootstrap.php">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### 3. Run Tests

```bash
php neo test
# or
vendor/bin/phpunit
```

---

## Writing Tests

### Basic Test Structure

```php
<?php

use NeoPhp\Testing\TestCase;

class ExampleTest extends TestCase
{
    public function test_example(): void
    {
        $this->assertTrue(true);
    }
}
```

---

## Database Testing

### Database Assertions

```php
public function test_user_creation()
{
    User::create(['email' => 'test@example.com']);
    
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com'
    ]);
    
    $this->assertDatabaseMissing('users', [
        'email' => 'fake@example.com'
    ]);
    
    $this->assertDatabaseCount('users', 1);
}
```

### Database Transactions

Tests automatically run in transactions and rollback:

```php
protected function setUp(): void
{
    parent::setUp();
    $this->beginDatabaseTransaction();
}

protected function tearDown(): void
{
    $this->rollbackDatabaseTransaction();
    parent::tearDown();
}
```

### Running Seeders

```php
public function test_with_seeded_data()
{
    $this->seed(UserSeeder::class);
    
    $this->assertDatabaseCount('users', 10);
}
```

---

## HTTP Testing

### Making Requests

```php
public function test_home_page()
{
    $response = $this->get('/');
    
    $this->assertResponseOk($response);
}

public function test_api_endpoint()
{
    $response = $this->json('POST', '/api/posts', [
        'title' => 'Test Post',
        'body' => 'Content'
    ]);
    
    $this->assertOk($response);
}
```

### Available Request Methods

```php
$response = $this->get('/url');
$response = $this->post('/url', $data);
$response = $this->put('/url', $data);
$response = $this->patch('/url', $data);
$response = $this->delete('/url');
$response = $this->json('GET', '/api/url');
```

### Response Assertions

```php
$response = $this->get('/');

// Status
$this->assertResponseOk($response);
$this->assertOk($response);
$this->assertNotFound($response);
$this->assertForbidden($response);
$this->assertUnauthorized($response);
$this->assertStatus($response, 201);

// Content
$this->assertSee($response, 'Welcome');
$this->assertDontSee($response, 'Error');

// JSON
$this->assertJson($response);
$this->assertJsonFragment($response, ['name' => 'John']);
$this->assertJsonStructure($response, ['data' => ['id', 'name']]);

// Array Structure
$this->assertArrayStructure([
    'data' => ['id', 'name'],
    'meta' => ['total']
], $response->json());
```

---

## Authentication Testing

### Acting as User

```php
public function test_authenticated_access()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $this->assertResponseOk($response);
}
```

### Authentication Assertions

```php
$this->assertAuthenticated();
$this->assertGuest();
$this->assertAuthenticatedAs($user);
$this->assertAuthenticatedAs($user, 'api');
```

### Credential Assertions

```php
$this->assertCredentials([
    'email' => 'test@example.com',
    'password' => 'password'
]);

$this->assertInvalidCredentials([
    'email' => 'test@example.com',
    'password' => 'wrong'
]);
```

---

## Model Factories

### Creating a Factory

```bash
php neo make:factory UserFactory --model=User
```

### Defining Factory

```php
namespace Database\Factories;

use App\Models\User;
use NeoPhp\Testing\Factory;

class UserFactory extends Factory
{
    protected string $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->randomString(10),
            'email' => $this->randomEmail(),
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'created_at' => $this->randomDate(),
        ];
    }
    
    public function admin(): static
    {
        return $this->state(['is_admin' => true]);
    }
}
```

### Using Factories

```php
// Single model
$user = User::factory()->create();

// Multiple models
$users = User::factory()->count(10)->create();

// With attributes
$user = User::factory()->create([
    'email' => 'test@example.com'
]);

// Using states
$admin = User::factory()->admin()->create();

// Make without saving
$user = User::factory()->make();

// With relationships
$post = Post::factory()
    ->for($user)
    ->create();
```

### Random Data Generators

```php
$this->randomString(10);           // Random string
$this->randomEmail();              // email@example.com
$this->randomNumber(1, 100);       // Random number
$this->randomDate('-1 year', 'now'); // Random date
$this->randomBoolean();            // true/false
$this->randomElement(['a', 'b', 'c']); // Random from array
```

---

## Test Organization

### Unit Tests

Place in `tests/Unit/` - Test individual classes/methods:

```php
class HelperTest extends TestCase
{
    public function test_config_helper()
    {
        $this->assertEquals('production', config('app.env'));
    }
}
```

### Feature Tests

Place in `tests/Feature/` - Test full features:

```php
class PostTest extends TestCase
{
    public function test_user_can_create_post()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->json('POST', '/api/posts', [
                'title' => 'Test Post',
                'body' => 'Content'
            ]);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post'
        ]);
    }
}
```

---

## Mocking

### Creating Mocks

```php
public function test_with_mock()
{
    $mock = $this->mock(UserRepository::class);
    
    $mock->shouldReceive('find')
         ->once()
         ->with(1)
         ->andReturn(new User(['id' => 1]));
    
    $user = $mock->find(1);
    $this->assertEquals(1, $user->id);
}
```

### Creating Spies

```php
public function test_with_spy()
{
    $spy = $this->spy(EmailService::class);
    
    // Use the service
    $service = app(EmailService::class);
    $service->send('test@example.com');
    
    // Assert it was called
    $spy->shouldHaveReceived('send')
        ->once()
        ->with('test@example.com');
}
```

---

## Best Practices

### 1. Test Naming

Use descriptive test names:

```php
// Good
public function test_user_can_update_own_post()

// Bad
public function testUpdate()
```

### 2. Arrange, Act, Assert

```php
public function test_example()
{
    // Arrange
    $user = User::factory()->create();
    
    // Act
    $response = $this->actingAs($user)->get('/profile');
    
    // Assert
    $this->assertResponseOk($response);
}
```

### 3. One Assertion Per Test

Test one thing at a time:

```php
// Good
public function test_user_can_login() { ... }
public function test_invalid_credentials_rejected() { ... }

// Bad
public function test_authentication() {
    // Tests multiple scenarios
}
```

### 4. Use Factories

Don't manually create test data:

```php
// Good
$user = User::factory()->create();

// Bad
$user = User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    // ... many fields
]);
```

### 5. Test Edge Cases

```php
public function test_handles_empty_input() { ... }
public function test_handles_large_input() { ... }
public function test_handles_special_characters() { ... }
```

---

## Running Specific Tests

```bash
# Run all tests
php neo test

# Run specific test file
vendor/bin/phpunit tests/Unit/ExampleTest.php

# Run specific test method
vendor/bin/phpunit --filter test_example

# Run test suite
vendor/bin/phpunit --testsuite Unit
vendor/bin/phpunit --testsuite Feature
```

---

## Continuous Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.0
    
    - name: Install Dependencies
      run: composer install
    
    - name: Run Tests
      run: vendor/bin/phpunit
```

---

## Coverage Reports

Generate code coverage:

```bash
vendor/bin/phpunit --coverage-html coverage/
```

View in browser: `coverage/index.html`

---

## See Also

- [PHPUnit Documentation](https://phpunit.de/)
- [Factory Pattern](../database/factories/)
- [Testing Examples](../tests/)
