# Welcome to NeoFramework

<div align="center">

![NeoFramework Logo](https://via.placeholder.com/200x200/667eea/ffffff?text=Neo)

**Modern PHP Framework for Web Artisans**

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square&logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Production%20Ready-brightgreen?style=flat-square)](https://github.com/neonextechnologies/neoframework)
[![Progress](https://img.shields.io/badge/Progress-95%25-success?style=flat-square)](DEVELOPMENT_ROADMAP.md)

</div>

---

## About NeoFramework

**NeoFramework** is a modern, production-ready full-stack PHP framework that combines the elegance of Laravel with modular architecture principles. Built from the ground up with PHP 8.0+ features, it provides everything you need to build scalable web applications.

### Why NeoFramework?

- 🚀 **Production Ready** - Battle-tested with 95% feature completeness
- 🏗️ **Modular Architecture** - Clean separation of concerns with plugin system
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
