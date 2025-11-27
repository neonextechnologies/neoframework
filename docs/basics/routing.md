# Routing

## Introduction

Routing is one of the most fundamental features of any web framework. In NeoFramework, all routes are defined in the `routes/web.php` and `routes/api.php` files, providing a clean and expressive syntax for defining your application's URL structure.

## Basic Routing

### Simple Routes

The most basic routes accept a URI and a closure:

```php
use NeoPhp\Routing\Route;

Route::get('/hello', function() {
    return 'Hello World';
});

Route::post('/users', function() {
    // Create user
});

Route::put('/users/{id}', function($id) {
    // Update user
});

Route::delete('/users/{id}', function($id) {
    // Delete user
});
```

### Available Router Methods

```php
Route::get($uri, $callback);
Route::post($uri, $callback);
Route::put($uri, $callback);
Route::patch($uri, $callback);
Route::delete($uri, $callback);
Route::options($uri, $callback);
```

### Multiple HTTP Methods

```php
Route::match(['get', 'post'], '/form', function() {
    // Handle GET or POST
});

Route::any('/api', function() {
    // Handle any HTTP method
});
```

## Route Parameters

### Required Parameters

```php
Route::get('/users/{id}', function($id) {
    return "User ID: {$id}";
});

Route::get('/posts/{postId}/comments/{commentId}', function($postId, $commentId) {
    return "Post {$postId}, Comment {$commentId}";
});
```

### Optional Parameters

```php
Route::get('/users/{name?}', function($name = 'Guest') {
    return "Hello, {$name}";
});
```

### Regular Expression Constraints

```php
Route::get('/users/{id}', function($id) {
    return "User ID: {$id}";
})->where('id', '[0-9]+');

Route::get('/posts/{slug}', function($slug) {
    return "Post: {$slug}";
})->where('slug', '[a-z-]+');

// Multiple constraints
Route::get('/users/{id}/posts/{slug}', function($id, $slug) {
    // ...
})->where(['id' => '[0-9]+', 'slug' => '[a-z-]+']);
```

### Global Constraints

Define global parameter patterns:

```php
// In RouteServiceProvider
Route::pattern('id', '[0-9]+');
Route::pattern('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');

// Now 'id' always expects numbers
Route::get('/users/{id}', ...);
Route::get('/posts/{id}', ...);
```

## Named Routes

Assign names to routes for easy URL generation:

```php
Route::get('/users/{id}', [UserController::class, 'show'])
    ->name('users.show');

Route::post('/users', [UserController::class, 'store'])
    ->name('users.store');
```

Generate URLs using route names:

```php
// Generate URL
$url = route('users.show', ['id' => 123]);
// /users/123

// Redirect to named route
return redirect()->route('users.show', ['id' => 123]);

// In views
<a href="<?= route('users.show', ['id' => $user->id]) ?>">View User</a>
```

## Route Groups

### Middleware Groups

```php
Route::middleware(['auth'])->group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
});

// Multiple middleware
Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/billing', [BillingController::class, 'index']);
});
```

### Prefix Groups

```php
Route::prefix('admin')->group(function() {
    Route::get('/users', ...);     // /admin/users
    Route::get('/posts', ...);     // /admin/posts
});
```

### Name Prefix Groups

```php
Route::name('admin.')->group(function() {
    Route::get('/users', ...)->name('users.index');  // admin.users.index
    Route::get('/posts', ...)->name('posts.index');  // admin.posts.index
});
```

### Combined Groups

```php
Route::prefix('api')
    ->middleware(['auth:api', 'throttle:60,1'])
    ->name('api.')
    ->group(function() {
        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');  // api.users.index
            
        Route::post('/users', [UserController::class, 'store'])
            ->name('users.store');  // api.users.store
    });
```

### Nested Groups

```php
Route::prefix('admin')->middleware('auth')->group(function() {
    Route::prefix('users')->group(function() {
        Route::get('/', ...);          // /admin/users
        Route::get('/{id}', ...);      // /admin/users/123
    });
    
    Route::prefix('posts')->group(function() {
        Route::get('/', ...);          // /admin/posts
        Route::get('/{id}', ...);      // /admin/posts/456
    });
});
```

## Controller Routes

### Basic Controller Routes

```php
use App\Controllers\UserController;

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
```

### Resource Controllers

Define RESTful resource routes automatically:

```php
Route::resource('posts', PostController::class);
```

This creates the following routes:

| Method    | URI                  | Action  | Route Name    |
|-----------|---------------------|---------|---------------|
| GET       | /posts              | index   | posts.index   |
| GET       | /posts/create       | create  | posts.create  |
| POST      | /posts              | store   | posts.store   |
| GET       | /posts/{id}         | show    | posts.show    |
| GET       | /posts/{id}/edit    | edit    | posts.edit    |
| PUT/PATCH | /posts/{id}         | update  | posts.update  |
| DELETE    | /posts/{id}         | destroy | posts.destroy |

### Partial Resources

Only include specific actions:

```php
// Only index and show
Route::resource('photos', PhotoController::class)
    ->only(['index', 'show']);

// All except create and store
Route::resource('posts', PostController::class)
    ->except(['create', 'store']);
```

### API Resources

API resources exclude create/edit (HTML forms):

```php
Route::apiResource('posts', PostController::class);
```

Creates:

| Method    | URI            | Action  | Route Name    |
|-----------|---------------|---------|---------------|
| GET       | /posts        | index   | posts.index   |
| POST      | /posts        | store   | posts.store   |
| GET       | /posts/{id}   | show    | posts.show    |
| PUT/PATCH | /posts/{id}   | update  | posts.update  |
| DELETE    | /posts/{id}   | destroy | posts.destroy |

### Nested Resources

```php
Route::resource('posts.comments', CommentController::class);
```

Creates routes like:
- GET `/posts/{post}/comments`
- POST `/posts/{post}/comments`
- GET `/posts/{post}/comments/{comment}`
- PUT `/posts/{post}/comments/{comment}`
- DELETE `/posts/{post}/comments/{comment}`

## Route Model Binding

### Implicit Binding

Automatically inject model instances:

```php
use App\Models\User;

Route::get('/users/{user}', function(User $user) {
    return $user->email;
});

// /users/123 automatically loads User::find(123)
```

Works with resource controllers:

```php
class UserController extends Controller
{
    public function show(User $user)
    {
        // $user is already loaded
        return new UserResource($user);
    }
}
```

### Custom Key

Use a different column for binding:

```php
Route::get('/posts/{post:slug}', function(Post $post) {
    return $post;
});

// /posts/my-first-post loads Post::where('slug', 'my-first-post')->first()
```

### Explicit Binding

Define custom resolution logic:

```php
// In RouteServiceProvider
public function boot()
{
    Route::bind('user', function($value) {
        return User::where('uuid', $value)->firstOrFail();
    });
}
```

## Middleware

### Assigning Middleware

```php
Route::get('/profile', [ProfileController::class, 'show'])
    ->middleware('auth');

// Multiple middleware
Route::post('/posts', [PostController::class, 'store'])
    ->middleware(['auth', 'verified']);

// Middleware with parameters
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('role:admin');
```

### Middleware Groups

Define middleware groups in `app/Http/Kernel.php`:

```php
protected array $middlewareGroups = [
    'web' => [
        \NeoPhp\Http\Middleware\VerifyCsrfToken::class,
        \NeoPhp\Session\Middleware\StartSession::class,
    ],
    
    'api' => [
        'throttle:60,1',
        \NeoPhp\Http\Middleware\ParseJsonBody::class,
    ],
];
```

Use middleware groups:

```php
Route::middleware('web')->group(function() {
    // Web routes
});

Route::middleware('api')->group(function() {
    // API routes
});
```

## Rate Limiting

### Basic Rate Limiting

```php
Route::middleware('throttle:60,1')->group(function() {
    Route::get('/api/users', [UserController::class, 'index']);
});

// 60 requests per minute
```

### Named Rate Limiters

Define rate limiters in `RouteServiceProvider`:

```php
use NeoPhp\Cache\RateLimiter;

protected function configureRateLimiting()
{
    RateLimiter::for('api', function(Request $request) {
        return $request->user()
            ? Limit::perMinute(100)->by($request->user()->id)
            : Limit::perMinute(10)->by($request->ip());
    });
}
```

Use named limiters:

```php
Route::middleware('throttle:api')->group(function() {
    // ...
});
```

## CORS Configuration

Configure CORS in `config/cors.php`:

```php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

Apply CORS middleware:

```php
Route::middleware('cors')->group(function() {
    Route::get('/api/users', [UserController::class, 'index']);
});
```

## Practical Examples

### Example 1: Blog Routes

```php
// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');

// Authenticated routes
Route::middleware('auth')->group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('posts', PostController::class)->except(['index', 'show']);
});

// Admin routes
Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->name('admin.')
    ->group(function() {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::resource('users', AdminUserController::class);
        Route::resource('posts', AdminPostController::class);
    });
```

### Example 2: API Routes

```php
Route::prefix('api/v1')
    ->middleware(['api', 'throttle:60,1'])
    ->name('api.')
    ->group(function() {
        // Public endpoints
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        
        // Protected endpoints
        Route::middleware('auth:api')->group(function() {
            Route::get('/me', [AuthController::class, 'me']);
            Route::apiResource('posts', PostController::class);
            Route::apiResource('posts.comments', CommentController::class);
        });
    });
```

### Example 3: Multi-Auth Routes

```php
// Customer routes
Route::prefix('customer')
    ->middleware('auth:customer')
    ->name('customer.')
    ->group(function() {
        Route::get('/dashboard', [CustomerDashboardController::class, 'index']);
        Route::resource('orders', OrderController::class);
    });

// Admin routes
Route::prefix('admin')
    ->middleware('auth:admin')
    ->name('admin.')
    ->group(function() {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        Route::resource('customers', CustomerController::class);
    });
```

## Testing Routes

```php
class RouteTest extends TestCase
{
    public function test_home_route()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
    }
    
    public function test_authenticated_route()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
    }
    
    public function test_api_route()
    {
        $response = $this->getJson('/api/users');
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }
}
```

## Route Caching

In production, cache routes for better performance:

```bash
# Cache routes
php neo route:cache

# Clear route cache
php neo route:clear
```

**Note:** Route caching doesn't work with closure-based routes. Use controller actions instead.

## Best Practices

### 1. Use Named Routes

**Good:**
```php
Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
return redirect()->route('users.show', ['id' => 123]);
```

**Bad:**
```php
return redirect('/users/123'); // Hardcoded URL
```

### 2. Group Related Routes

```php
Route::prefix('admin')->middleware('auth')->group(function() {
    // All admin routes together
});
```

### 3. Use Resource Controllers

```php
Route::resource('posts', PostController::class); // Simple and clean
```

### 4. Separate Web and API Routes

Keep `routes/web.php` and `routes/api.php` separate with appropriate middleware.

### 5. Use Route Model Binding

```php
// Instead of manual loading
public function show($id) {
    $user = User::findOrFail($id);
}

// Use implicit binding
public function show(User $user) {
    // $user is already loaded
}
```

## Next Steps

- [Controllers](controllers.md) - Handle route logic
- [Middleware](middleware.md) - Filter HTTP requests
- [Requests](requests.md) - Access request data
- [Responses](responses.md) - Return responses
