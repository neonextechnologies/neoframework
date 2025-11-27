# Introduction

## Welcome to NeoFramework

NeoFramework is a modern, elegant PHP framework designed for building robust web applications with speed and confidence. Inspired by the best practices from Laravel and built on solid architectural principles, NeoFramework provides all the tools you need to create maintainable, scalable applications.

## Philosophy

NeoFramework follows these core principles:

### 1. **Developer Happiness**

We believe that great applications are built by happy developers. NeoFramework provides an expressive, elegant syntax that makes common tasks simple and enjoyable.

```php
// Elegant, readable code
$users = User::with('posts')
    ->where('active', true)
    ->latest()
    ->paginate(15);
```

### 2. **Convention Over Configuration**

Sensible defaults mean you can start building immediately, while still having the flexibility to customize everything.

```php
// Works out of the box
class Post extends Model
{
    // Automatically uses 'posts' table
    // Automatically handles timestamps
    // Automatically handles mass assignment
}
```

### 3. **Modern PHP**

Built for PHP 8.0+, leveraging modern features like attributes, named arguments, and strict typing.

```php
#[Route('/users/{id}')]
public function show(int $id): JsonResponse
{
    return response()->json(
        UserResource::make(User::findOrFail($id))
    );
}
```

### 4. **Testing First**

Testing is a first-class citizen. Every feature is designed to be easily testable.

```php
public function test_user_can_create_post()
{
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->post('/posts', ['title' => 'My Post'])
        ->assertCreated();
}
```

## What Makes NeoFramework Special?

### Full-Stack Framework

NeoFramework isn't just a routing library or ORM - it's a complete ecosystem:

- **Powerful ORM** with relationships, eager loading, and scopes
- **Authentication & Authorization** out of the box
- **Queue System** for background processing
- **Mail System** for sending emails
- **Testing Suite** with factories and assertions
- **Localization** for multi-language apps
- **CLI Tools** for rapid development

### Modular Architecture

Built on a modular monolith architecture, allowing you to:

- Keep code organized and maintainable
- Share modules between projects
- Extend functionality with plugins
- Build reusable packages

### Production Ready

With 95% feature completeness, NeoFramework is ready for production use:

- ✅ Comprehensive error handling
- ✅ Security best practices
- ✅ Performance optimization
- ✅ Logging and monitoring
- ✅ Database migrations
- ✅ Environment configuration

## Core Features

### 🗄️ Advanced ORM

```php
// Define relationships
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

// Eager load relationships
$users = User::with(['posts.comments', 'roles'])->get();

// Use query scopes
$activeUsers = User::active()->verified()->get();

// Handle model events
User::creating(function($user) {
    $user->uuid = Str::uuid();
});
```

### 🔐 Complete Authentication

```php
// Multiple authentication guards
auth('web')->attempt($credentials, $remember = true);
auth('api')->user();

// Authorization with policies
$this->authorize('update', $post);

// Gates for simple checks
Gate::define('admin', fn($user) => $user->isAdmin());
```

### 🌐 RESTful APIs

```php
// Form request validation
class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'body' => 'required',
        ];
    }
}

// API resources
class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => new UserResource($this->user),
        ];
    }
}
```

### 📧 Queue & Mail

```php
// Queue jobs
ProcessPodcast::dispatch($podcast)
    ->delay(now()->addMinutes(10))
    ->onQueue('processing');

// Chain jobs
Bus::chain([
    new ProcessPodcast($podcast),
    new PublishPodcast($podcast),
    new NotifyUsers($podcast)
])->dispatch();

// Send mail
Mail::to($user)->send(new OrderShipped($order));
```

### 🧪 Testing

```php
// HTTP testing
$response = $this->actingAs($user)
    ->json('POST', '/api/posts', $data);

$response->assertCreated()
    ->assertJsonFragment(['title' => 'My Post']);

// Database testing
$this->assertDatabaseHas('posts', [
    'title' => 'My Post',
    'user_id' => $user->id
]);

// Use factories
$users = User::factory()->count(10)->create();
```

### 🌍 Localization

```php
// Translations
echo __('messages.welcome');
echo trans('auth.failed');

// Pluralization
echo trans_choice('messages.posts', $count);

// Multiple languages
set_locale('th');
```

## Architecture Overview

```
Request → Router → Middleware → Controller → Model → Database
                                    ↓
                              View/Response
```

### Request Lifecycle

1. **Bootstrap** - Load configuration and services
2. **Routing** - Match request to route
3. **Middleware** - Process request through middleware pipeline
4. **Controller** - Execute controller action
5. **Response** - Return response to client

### Service Container

The service container is the heart of NeoFramework, managing class dependencies and performing dependency injection:

```php
// Register services
$app->singleton(UserRepository::class, EloquentUserRepository::class);

// Resolve from container
$repository = app(UserRepository::class);

// Automatic injection
class UserController extends Controller
{
    public function __construct(
        private UserRepository $users
    ) {}
}
```

## Who Should Use NeoFramework?

NeoFramework is perfect for:

- **Startups** building MVPs quickly
- **Agencies** developing client projects
- **Enterprises** needing maintainable systems
- **Developers** who love Laravel's elegance
- **Teams** requiring modular architecture
- **Projects** that need long-term maintainability

## Learning Resources

### Documentation

- **Getting Started** - Installation and configuration
- **Core Concepts** - Understand the architecture
- **Database** - Master the ORM
- **Security** - Authentication and authorization
- **Testing** - Write reliable tests
- **API Reference** - Complete class documentation

### Tutorials

- Build a blog from scratch
- Create a REST API
- Develop an e-commerce platform
- Real-time chat application

### Community

- GitHub Discussions
- Issue Tracker
- Email Support
- Contributing Guide

## Next Steps

Ready to get started? Here's what to do next:

1. **[Install NeoFramework](getting-started/installation.md)** - Set up your development environment
2. **[Quick Start Tutorial](getting-started/quick-start.md)** - Build your first application
3. **[Read Core Concepts](architecture/container.md)** - Understand the framework
4. **[Explore Examples](tutorials/blog.md)** - Learn from real applications

## Version Information

- **Current Version:** 2.0.0
- **PHP Requirement:** 8.0+
- **Status:** Production Ready (95%)
- **License:** MIT

## Credits

NeoFramework is developed by [Neonex Technologies](https://neonext.com) and is open-source software licensed under the MIT license.

Special thanks to the Laravel community for inspiration and the PHP community for building amazing tools.

---

**Let's build something amazing together!** 🚀

[Get Started →](getting-started/installation.md)
