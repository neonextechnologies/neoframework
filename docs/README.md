# Welcome to NeoFramework

<div align="center">

![PHP Version](https://img.shields.io/badge/PHP-8.0%20to%208.4-777BB4?style=flat-square&logo=php)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)
![Status](https://img.shields.io/badge/Status-Production%20Ready-brightgreen?style=flat-square)
![Type](https://img.shields.io/badge/Type-Metadata%20Driven-orange?style=flat-square)

**Modern PHP Framework for Web Artisans**

</div>

---

## About NeoFramework

**NeoFramework** is a modern, production-ready full-stack PHP framework that combines the elegance of Laravel with modular architecture principles. Built from the ground up with PHP 8.0+ features, it provides everything you need to build scalable web applications.

### Why NeoFramework?

- 🚀 **Production Ready** - Battle-tested with 95% feature completeness
- 🏛️ **Modular Architecture** - Clean separation of concerns with plugin system
- 🗄️ **Advanced ORM** - Eloquent-like with full relationship support
- 🔐 **Complete Authentication** - Multi-guard auth with authorization
- 🧪 **Testing First** - Built-in PHPUnit integration with factories
- 🌍 **International** - Multi-language support out of the box
- 🛠️ **Developer Tools** - Debug toolbar, error pages, 25+ CLI commands
- 📚 **Well Documented** - Comprehensive guides and API reference

---

## Quick Start

Get started with NeoFramework in minutes:

```bash
# Clone the repository
git clone https://github.com/neonextechnologies/neoframework.git
cd neoframework

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php neo app:key

# Run migrations
php neo migrate

# Start development server
php neo serve
```

Visit `http://localhost:8000` and you're ready to go!

---

## Core Features

### 🏛️ Foundation Architecture

NeoFramework uses a **contract-first** architecture with clean separation:

- **Foundation Layer** - Contract-first architecture with pure interfaces
- **Plugin System** - Extensible with WordPress-style hooks
- **Service Providers** - Deferred loading and dependency management
- **Metadata-Driven** - PHP 8 Attributes for declarative development

### 🗄️ Advanced ORM

Eloquent-like ORM with full relationship support:

```php
// Relationships
$user->posts()->with('comments', 'tags')->get();
$post->user()->first();
$user->roles()->attach($roleId);

// Eager Loading
$users = User::with(['posts.comments', 'roles'])->get();

// Query Scopes
User::active()->verified()->latest()->paginate(15);

// Soft Deletes
$user->delete();
User::withTrashed()->get();
```

### 🔐 Complete Authentication

Multi-guard authentication with authorization:

```php
// Authentication
auth('web')->attempt($credentials, $remember = true);
auth('api')->user();

// Password Reset
PasswordBroker::sendResetLink($email);

// Email Verification
$user->sendEmailVerificationNotification();

// Authorization
Gate::define('update-post', fn($user, $post) => 
    $user->id === $post->user_id
);

$this->authorize('update', $post);
```

### 📧 Queue & Mail System

Background job processing with multiple drivers:

```php
// Dispatch Jobs
ProcessPodcast::dispatch($podcast);

// Job Chains
ProcessPodcast::withChain([
    new OptimizePodcast,
    new ReleasePodcast
])->dispatch($podcast);

// Send Mail
Mail::to($user)->send(new WelcomeEmail($user));

// Queueable Mail
Mail::to($user)->queue(new InvoiceEmail($invoice));
```

### 🧪 Testing Suite

Built-in PHPUnit integration with factories:

```php
public function test_user_can_create_post()
{
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->post('/posts', [
            'title' => 'Test Post',
            'body' => 'Content'
        ])
        ->assertStatus(201)
        ->assertJson(['success' => true]);
}
```

### 🛠️ CLI Tools

25+ powerful commands for code generation:

```bash
# Generate CRUD
php neo make:crud Post --api --test

# Generate Components
php neo make:controller PostController --resource
php neo make:model Post --migration --factory
php neo make:middleware CheckAge
php neo make:mail WelcomeEmail --markdown

# Database Operations
php neo migrate
php neo db:seed
php neo migrate:rollback
```

---

## What's Inside?

### Core Components

- **Routing** - Fast routing with middleware support
- **Controllers** - Resource controllers with dependency injection
- **Views** - Blade-inspired templating engine
- **Validation** - Comprehensive validation rules
- **Middleware** - Request/response pipeline
- **Events** - Event-driven architecture
- **Cache** - Multi-driver caching (File, Redis, Memcached)
- **Logging** - Monolog integration with multiple channels
- **Storage** - File storage abstraction (Local, S3, FTP)
- **Notifications** - Multi-channel notifications (Mail, SMS, Slack)
- **Scheduling** - Cron-based task scheduling
- **Localization** - Multi-language support with pluralization

### Developer Tools

- **Debug Toolbar** - Real-time performance monitoring
- **Error Pages** - Beautiful error pages with stack traces
- **Code Generators** - 25+ CLI commands
- **Testing Suite** - PHPUnit integration with factories
- **API Resources** - JSON transformation layer
- **Form Generation** - Metadata-driven form builder

---

## Architecture

### Foundation Framework

NeoFramework is built as a **Foundation Framework** - not a full-stack framework like Laravel, but a solid architectural foundation for building one:

```
Traditional Full Framework (Laravel, Symfony):
└── Everything built-in (Database, Auth, Queue, Cache, etc.)

NeoFramework Foundation Framework:
├── Foundation Layer - Contract-first architecture with pure interfaces
├── Plugin System - Extensible with WordPress-style hooks  
├── Service Provider System - Deferred loading and dependency management
└── Metadata-Driven Development - PHP 8 Attributes for declarative development
```

### Metadata-Driven Development

Use PHP 8 Attributes for declarative programming:

```php
#[Table(name: 'users')]
#[SoftDeletes]
class User extends Model
{
    #[Column(type: 'string', length: 100)]
    #[Validation('required|email|unique:users')]
    public string $email;
    
    #[Column(type: 'string')]
    #[Hidden]
    public string $password;
    
    #[HasMany(Post::class)]
    public function posts() {}
}
```

---

## Documentation

Explore comprehensive documentation:

- [Installation Guide](getting-started/installation.md)
- [Quick Start Tutorial](getting-started/quick-start.md)
- [Foundation Architecture](core-concepts/foundation-architecture.md)
- [Database & ORM](database/getting-started.md)
- [Authentication](security/authentication.md)
- [Testing Guide](testing/getting-started.md)
- [CLI Tools](cli-tools/introduction.md)

---

## Requirements

- PHP 8.0+ with extensions:
  - PDO (MySQL/PostgreSQL)
  - mbstring
  - OpenSSL
  - JSON
- Composer 2.0+
- MySQL 5.7+ / PostgreSQL 10+ / SQLite 3.8+

---

## License

NeoFramework is open-source software licensed under the [MIT license](LICENSE).

---

## Contributing

We welcome contributions! Please see our [Contributing Guide](contributing/guidelines.md) for details.

---

<div align="center">

**Built with ❤️ by Neonex Technologies**

[GitHub](https://github.com/neonextechnologies/neoframework) • [Documentation](https://docs.neoframework.io) • [Community](resources/community.md)

</div>

# Run migrations
php neo migrate

# Start development server
php neo serve
```

Visit `http://localhost:8000` and you're ready to go! 🎉

---

## What's Inside?

### Core Features

- **Advanced ORM** - Models, relationships, eager loading, scopes, events
- **Authentication** - Session & token guards, password reset, email verification
- **Authorization** - Gates, policies, role-based access control
- **HTTP Layer** - Routing, middleware, form requests, file uploads
- **Queue System** - Job dispatching, chains, batches
- **Testing** - PHPUnit integration, HTTP testing, database testing, factories
- **Localization** - Translation system with pluralization
- **Developer Tools** - Debug toolbar, beautiful error pages

### Architecture

```
neoframework/
├── app/              # Application code
│   ├── Controllers/  # HTTP controllers
│   ├── Models/       # Eloquent models
│   ├── Middleware/   # HTTP middleware
│   └── Providers/    # Service providers
├── src/              # Framework core
│   ├── Database/     # ORM & Query Builder
│   ├── Auth/         # Authentication
│   ├── Http/         # HTTP abstractions
│   ├── Queue/        # Queue system
│   └── Testing/      # Testing framework
├── config/           # Configuration files
├── database/         # Migrations & seeders
├── resources/        # Views & translations
├── routes/           # Route definitions
└── tests/            # Test suites
```

---

## Documentation Sections

### 📖 Getting Started
- [Introduction](introduction.md) - Framework overview and philosophy
- [Installation](getting-started/installation.md) - Complete installation guide
- [Configuration](getting-started/configuration.md) - Environment configuration
- [Quick Start](getting-started/quick-start.md) - Build your first app
- [Directory Structure](getting-started/directory-structure.md) - Understand the layout

### 🏗️ Core Concepts
- [Architecture](core-concepts/foundation-architecture.md) - Framework architecture
- [Service Container](service-providers/container.md) - Dependency injection
- [Service Providers](service-providers/introduction.md) - Application bootstrapping
- [Middleware](core-concepts/middleware.md) - HTTP middleware pipeline
- [Contracts](core-concepts/contracts.md) - Interface-based design

### 🗄️ Database
- [Getting Started](database/getting-started.md) - Database basics
- [Query Builder](database/query-builder.md) - Building SQL queries
- [ORM (Models)](database/orm.md) - Eloquent-like ORM
- [Relationships](database/relationships.md) - Model relationships
- [Migrations](database/migrations.md) - Database migrations
- [Seeders](database/seeders.md) - Data seeding

### 🔐 Security
- [Authentication](security/authentication.md) - User authentication
- [Authorization](security/authorization.md) - Gates & policies
- [Password Reset](security/password-reset.md) - Password recovery
- [Email Verification](security/email-verification.md) - Email verification
- [CSRF Protection](advanced/security.md) - Cross-site request forgery

### 🌐 HTTP & Routing
- [Routing](http/routing.md) - Define routes
- [Controllers](http/controllers.md) - HTTP controllers
- [Requests](http/requests.md) - HTTP requests
- [Responses](http/responses.md) - HTTP responses
- [Form Validation](http/validation.md) - Validate input
- [API Resources](http/api-resources.md) - Transform responses

### 📧 Services
- [Queue System](advanced/queue.md) - Background jobs
- [Mail](mail/introduction.md) - Send emails
- [Cache](advanced/caching.md) - Cache data
- [Events](advanced/events.md) - Event system
- [Logging](advanced/logging.md) - Application logs
- [Storage](storage/introduction.md) - File storage

### 🧪 Testing
- [Getting Started](testing/getting-started.md) - Testing basics
- [HTTP Tests](testing/http-tests.md) - Test HTTP endpoints
- [Database Tests](testing/database-tests.md) - Test database operations
- [Factories](testing/factories.md) - Generate test data
- [Mocking](testing/mocking.md) - Mock dependencies

### 🌍 Frontend & Views
- [Blade Templates](views/blade.md) - Templating engine
- [Localization](localization/introduction.md) - Multi-language support
- [Asset Management](frontend/assets.md) - Manage assets

### 🛠️ CLI & Tools
- [CLI Introduction](cli-tools/introduction.md) - Command-line tools
- [Code Generators](cli-tools/generators/) - Generate code
- [Custom Commands](cli-tools/custom-commands.md) - Create commands
- [Database Commands](cli-tools/database-commands.md) - Database tools

### 📚 API Reference
- [Core Classes](api-reference/core.md)
- [Database API](api-reference/database.md)
- [HTTP API](api-reference/http.md)
- [Auth API](api-reference/auth.md)
- [Queue API](api-reference/queue.md)
- [Validation API](api-reference/validation.md)

---

## Examples & Tutorials

### Complete Application Examples

- [Building a Blog](tutorials/blog.md) - Full blog application
- [REST API](tutorials/rest-api.md) - Build RESTful API
- [E-commerce Platform](tutorials/ecommerce.md) - Shopping cart system
- [Real-time Chat](tutorials/realtime-chat.md) - WebSocket chat

### Code Snippets

```php
// Define a model with relationships
class Post extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['title', 'body'];
    protected $casts = ['published_at' => 'datetime'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
}

// Use in controller
public function index()
{
    $posts = Post::with('user', 'comments')
        ->published()
        ->latest()
        ->paginate(15);
    
    return PostResource::collection($posts);
}

// Test it
public function test_can_list_posts()
{
    $posts = Post::factory()->count(3)->create();
    
    $response = $this->json('GET', '/api/posts');
    
    $response->assertOk()
        ->assertJsonCount(3, 'data');
}
```

---

## Community & Support

### Get Help

- 📖 [Documentation](https://neoframework.docs.io)
- 💬 [GitHub Discussions](https://github.com/neonextechnologies/neoframework/discussions)
- 🐛 [Issue Tracker](https://github.com/neonextechnologies/neoframework/issues)
- 📧 [Email Support](mailto:support@neonext.com)

### Contributing

We welcome contributions! See our [Contributing Guide](../CONTRIBUTING.md) for details.

- [Code of Conduct](../CODE_OF_CONDUCT.md)
- [Development Roadmap](../DEVELOPMENT_ROADMAP.md)
- [Pull Request Guidelines](../contributing/pull-requests.md)

---

## License

NeoFramework is open-source software licensed under the [MIT license](../LICENSE).

---

## Credits

Created with ❤️ by [Neonex Technologies](https://neonext.com)

Special thanks to all [contributors](https://github.com/neonextechnologies/neoframework/graphs/contributors) who have helped build NeoFramework.

---

<div align="center">

**Ready to build something amazing?**

[Get Started →](getting-started/installation.md) | [View on GitHub →](https://github.com/neonextechnologies/neoframework)

</div>
