# Testing

## Introduction

NeoFramework is built with testing in mind. Support for testing with PHPUnit is included out of the box, and a `phpunit.xml` file is already set up for your application. The framework also provides convenient helper methods for testing your applications.

## Environment

When running tests, NeoFramework will automatically set the environment to `testing` because of the environment variables defined in `phpunit.xml`. The framework also automatically configures the session and cache to the `array` driver while testing, meaning no session or cache data will be persisted during testing.

## Creating Tests

### Generate Test Class

```bash
php neo make:test UserTest
php neo make:test Http/Controllers/PostControllerTest
php neo make:test Models/UserTest --unit
```

This creates a test file in `tests/` directory:

```php
<?php

namespace Tests;

use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_example()
    {
        $this->assertTrue(true);
    }
}
```

## Running Tests

### Run All Tests

```bash
vendor/bin/phpunit

# Or using Neo CLI
php neo test
```

### Run Specific Test

```bash
vendor/bin/phpunit --filter test_user_can_login
vendor/bin/phpunit tests/Feature/AuthTest.php
```

### Run with Coverage

```bash
vendor/bin/phpunit --coverage-html coverage
```

## HTTP Tests

### Making Requests

```php
class PostTest extends TestCase
{
    public function test_can_create_post()
    {
        $response = $this->post('/api/posts', [
            'title' => 'Test Post',
            'content' => 'Test content',
        ]);

        $response->assertStatus(201);
    }
}
```

### Available Request Methods

```php
$response = $this->get('/posts');
$response = $this->post('/posts', $data);
$response = $this->put('/posts/1', $data);
$response = $this->patch('/posts/1', $data);
$response = $this->delete('/posts/1');
$response = $this->options('/');

// JSON requests
$response = $this->getJson('/api/posts');
$response = $this->postJson('/api/posts', $data);
```

### Authentication

```php
public function test_authenticated_user_can_view_dashboard()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
}

// API authentication
$response = $this->actingAs($user, 'api')->get('/api/user');
```

### Headers

```php
$response = $this->withHeaders([
    'X-Header' => 'Value',
    'Accept' => 'application/json',
])->post('/api/posts', $data);
```

### Cookies

```php
$response = $this->withCookie('name', 'value')->get('/');
```

### Session

```php
$response = $this->withSession(['key' => 'value'])->get('/');
```

## Response Assertions

### Status Assertions

```php
$response->assertStatus(200);
$response->assertOk();
$response->assertCreated();
$response->assertNoContent();
$response->assertNotFound();
$response->assertForbidden();
$response->assertUnauthorized();
```

### Redirect Assertions

```php
$response->assertRedirect('/home');
$response->assertRedirect(route('home'));
$response->assertRedirectToRoute('home');
```

### View Assertions

```php
$response->assertViewIs('posts.index');
$response->assertViewHas('posts');
$response->assertViewHas('posts', function ($posts) {
    return $posts->count() > 0;
});
$response->assertViewMissing('errors');
```

### JSON Assertions

```php
$response->assertJson([
    'name' => 'John',
    'email' => 'john@example.com',
]);

$response->assertJsonFragment([
    'name' => 'John',
]);

$response->assertJsonStructure([
    'data' => [
        '*' => ['id', 'name', 'email'],
    ],
]);

$response->assertJsonPath('data.name', 'John');
$response->assertJsonCount(5, 'data');
```

### Session Assertions

```php
$response->assertSessionHas('status');
$response->assertSessionHas('status', 'Profile updated!');
$response->assertSessionHasErrors(['email']);
$response->assertSessionDoesntHaveErrors(['email']);
```

## Database Testing

### Database Transactions

Automatically rollback database changes after each test:

```php
use NeoPhp\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_post()
    {
        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Test content',
        ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
        ]);
    }
}
```

### Database Assertions

```php
$this->assertDatabaseHas('users', [
    'email' => 'john@example.com',
]);

$this->assertDatabaseMissing('users', [
    'email' => 'deleted@example.com',
]);

$this->assertDatabaseCount('users', 5);

$this->assertDeleted($post);
$this->assertSoftDeleted($post);
```

## Model Factories

### Creating Factories

```bash
php neo make:factory UserFactory --model=User
```

This creates `database/factories/UserFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\User;
use NeoPhp\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }
}
```

### Using Factories

```php
// Create single model
$user = User::factory()->create();

// Create with attributes
$user = User::factory()->create([
    'name' => 'John Doe',
]);

// Create multiple
$users = User::factory()->count(3)->create();

// Make without saving
$user = User::factory()->make();

// Using states
$admin = User::factory()->admin()->create();
$unverified = User::factory()->unverified()->create();
```

### Factory Relationships

```php
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
        ];
    }
}

// Create post with user
$post = Post::factory()->create();

// Create post with specific user
$post = Post::factory()->create([
    'user_id' => $user->id,
]);

// Create user with posts
$user = User::factory()
    ->has(Post::factory()->count(3))
    ->create();
```

## Mocking

### Mocking Facades

```php
use NeoPhp\Support\Facades\Mail;

public function test_sends_welcome_email()
{
    Mail::fake();

    // Perform action
    $user = User::factory()->create();

    // Assert email was sent
    Mail::assertSent(WelcomeEmail::class, function ($mail) use ($user) {
        return $mail->user->id === $user->id;
    });
}
```

### Mocking Events

```php
use NeoPhp\Support\Facades\Event;

public function test_dispatches_event()
{
    Event::fake();

    // Perform action
    $post = Post::create([...]);

    // Assert event was dispatched
    Event::assertDispatched(PostCreated::class);
}
```

### Mocking Queue

```php
use NeoPhp\Support\Facades\Queue;

public function test_dispatches_job()
{
    Queue::fake();

    // Perform action
    ProcessUpload::dispatch($file);

    // Assert job was pushed
    Queue::assertPushed(ProcessUpload::class);
}
```

## Practical Examples

### Example 1: Authentication Tests

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use NeoPhp\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password()
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
```

### Example 2: API Tests

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Tests\TestCase;
use NeoPhp\Foundation\Testing\RefreshDatabase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_posts()
    {
        Post::factory()->count(5)->create();

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'title', 'content', 'created_at'],
                     ],
                 ]);
    }

    public function test_can_create_post()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/posts', [
                'title' => 'Test Post',
                'content' => 'Test content',
            ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'data' => [
                         'title' => 'Test Post',
                     ],
                 ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'user_id' => $user->id,
        ]);
    }

    public function test_cannot_create_post_without_title()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/posts', [
                'content' => 'Test content',
            ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }

    public function test_can_update_own_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')
            ->putJson("/api/posts/{$post->id}", [
                'title' => 'Updated Title',
                'content' => 'Updated content',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_cannot_update_other_users_post()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'api')
            ->putJson("/api/posts/{$post->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(403);
    }
}
```

### Example 3: Model Tests

```php
<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Post;
use Tests\TestCase;
use NeoPhp\Foundation\Testing\RefreshDatabase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_posts()
    {
        $user = User::factory()
            ->has(Post::factory()->count(3))
            ->create();

        $this->assertCount(3, $user->posts);
        $this->assertInstanceOf(Post::class, $user->posts->first());
    }

    public function test_user_full_name_attribute()
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->full_name);
    }

    public function test_user_can_check_role()
    {
        $user = User::factory()->admin()->create();

        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('editor'));
    }

    public function test_soft_delete_user()
    {
        $user = User::factory()->create();

        $user->delete();

        $this->assertSoftDeleted($user);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
```

## Best Practices

### 1. Use Descriptive Test Names

```php
// Good
public function test_user_can_create_post()
public function test_guest_cannot_access_dashboard()

// Bad
public function test_post()
public function test1()
```

### 2. One Assertion Per Test (When Possible)

```php
public function test_user_can_login()
{
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->post('/login', [...]);

    // Assert
    $this->assertAuthenticatedAs($user);
}
```

### 3. Use Factories Instead of Manual Creation

```php
// Good
$user = User::factory()->create();

// Bad
$user = User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
]);
```

### 4. Use RefreshDatabase

```php
class PostTest extends TestCase
{
    use RefreshDatabase;
}
```

### 5. Test Both Success and Failure Cases

```php
public function test_can_create_post_with_valid_data() { }
public function test_cannot_create_post_without_title() { }
public function test_cannot_create_post_as_guest() { }
```

## Next Steps

- [HTTP Tests](http-tests.md) - Detailed HTTP testing
- [Database Tests](database-tests.md) - Database testing
- [Mocking](mocking.md) - Mocking services
- [Factories](factories.md) - Model factories
