# 🚀 NeoFramework - Modern PHP Full-Stack Framework

<div align="center">

![PHP Version](https://img.shields.io/badge/PHP-8.0%20to%208.4-777BB4?style=flat-square&logo=php)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)
![Status](https://img.shields.io/badge/Status-In%20Development-orange?style=flat-square)
![Type](https://img.shields.io/badge/Type-Full%20Stack-blue?style=flat-square)

**A modern, elegant PHP framework for web artisans**  
*Built on solid foundation with powerful ORM, advanced authentication, and developer-friendly tools*

[Features](#-current-features) • [Roadmap](#-development-roadmap) • [Quick Start](#-quick-start)

</div>

---

## 📖 About NeoFramework

**NeoFramework** is a **Modular Monolith Full-Stack PHP Framework** inspired by Neonex Core Architecture. It combines the best of Laravel's developer experience with modular architecture for building scalable, maintainable applications.

### 🏗️ Core Architecture Principles

Based on **Neonex Core Architecture**:

1. **Modular Monolith**
   - Module Registry & Auto-Discovery
   - Dependency Injection Container
   - Plugin Architecture

2. **Clean Package Layout**
   - `cmd/` - CLI Commands
   - `internal/` - Private Core
   - `pkg/` - Reusable Packages
   - `modules/` - Business Modules

3. **CLI Integration**
   - `neo new` - Scaffolding
   - `neo serve` - Development Server
   - `neo module` - Module Management

### ✨ Key Features

- 🏗️ **Modular Architecture** - Self-contained, reusable modules ✅
- 🗄️ **Advanced ORM** - Eloquent-like with relationships *(in development)*
- 🔐 **Complete Auth** - Authentication & authorization *(in development)*
- 🎨 **Blade Templates** - Elegant templating engine ✅
- 🛠️ **CLI Tools** - Module generators & scaffolding ✅
- 📦 **DI Container** - Automatic dependency injection ✅
- 🔌 **Plugin System** - Extensible with hooks ✅
- 📝 **Metadata-Driven** - PHP 8 Attributes ✅
- ⚡ **Performance** - Lightweight and fast ✅

### 📊 Development Status

```
1. Modular Monolith (70% Complete)
├─ ✅ Module Registry & Auto-Discovery
├─ ✅ Dependency Injection Container
├─ ✅ Plugin Architecture
└─ 🚧 Module Management CLI

2. Clean Package Layout (60% Complete)
├─ ✅ cmd/ - CLI Commands
├─ ✅ src/ - Private Core (internal/)
├─ 🚧 pkg/ - Reusable Packages
└─ 🚧 modules/ - Business Modules

3. Full-Stack Features (30% Complete)
├─ ✅ Router & Middleware
├─ ✅ Basic ORM & Query Builder
├─ ✅ Blade Templating
├─ ✅ Cache, Queue, Events
├─ ✅ Basic Authentication
├─ 🚧 Advanced ORM (Relationships)
├─ 🚧 Authorization (Policies & Gates)
├─ 🚧 Form Request Validation
├─ 🚧 API Resources
└─ 🚧 Testing Support
```

**See:** [DEVELOPMENT_ROADMAP.md](DEVELOPMENT_ROADMAP.md) for detailed plan

---

## ✨ Current Features

### 🏗️ Core Foundation

**Dependency Injection Container:**
```php
// Service registration
$app->singleton(DatabaseInterface::class, MySQLDatabase::class);
$app->bind(CacheInterface::class, RedisCache::class);

// Automatic injection in controllers
class UserController {
    public function __construct(
        private DatabaseInterface $db,
        private CacheInterface $cache
    ) {}
}
```

**Service Providers:**
```php
class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void {
        $this->app->singleton('payment', fn() => new StripePayment(
            config('payment.stripe_key')
        ));
    }
}
```

### 🗄️ Database Layer

**Query Builder:**
```php
$users = DB::table('users')
    ->where('active', 1)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

**Basic ORM:**
```php
// Find, create, update, delete
$user = User::find(1);
$user = User::create(['name' => 'John', 'email' => 'john@example.com']);
$user->update(['name' => 'Jane']);
$user->delete();
```

**Migrations:**
```bash
php neo make:migration create_users_table
php neo migrate
php neo migrate:rollback
```

### 🎨 Blade Templates

```blade
@extends('layouts.app')

@section('content')
    <h1>{{ $title }}</h1>
    
    @foreach($users as $user)
        <div class="user">
            {{ $user->name }}
            @if($user->isAdmin())
                <span class="badge">Admin</span>
            @endif
        </div>
    @endforeach
@endsection
```

### 🔐 Authentication (Basic)

```php
// Session auth
Auth::attempt(['email' => $email, 'password' => $password]);
$user = Auth::user();
Auth::logout();

// JWT tokens
$token = JWT::encode(['user_id' => $user->id]);
$data = JWT::decode($token);

// Basic RBAC
$user->hasRole('admin');
$user->can('edit-post');
```

### 🛠️ CLI Tools

```bash
# Generators
php neo make:model User -m -c
php neo make:controller UserController --resource
php neo make:middleware AuthMiddleware
php neo make:migration create_posts_table
php neo make:seeder UserSeeder

# Database
php neo migrate
php neo migrate:rollback
php neo db:seed

# Development
php neo serve
php neo route:list
php neo cache:clear
```

### 📦 Other Components

- ✅ **Cache** - File, Redis, Memcached drivers
- ✅ **Events** - Event dispatcher with listeners
- ✅ **Queue** - Job queue (basic)
- ✅ **Logging** - PSR-3 logger
- ✅ **Mail** - Multiple mail drivers
- ✅ **Storage** - File storage abstraction
- ✅ **Validation** - Input validation
- ✅ **Pagination** - Query result pagination
- ✅ **Security** - CSRF & XSS protection

---

## 🎯 Development Roadmap

### Phase 1: Modular Architecture (Weeks 1-3) 🔴 CRITICAL

**1. Complete Module System**
- ✅ Module Registry & Auto-Discovery
- 🚧 Module Lifecycle (boot, register, destroy)
- 🚧 Module Dependencies & Imports
- 🚧 Module Configuration

**2. Module CLI Tools**
```bash
php neo make:module Blog          # Create new module
php neo module:list               # List all modules
php neo module:enable Blog        # Enable module
php neo module:disable Blog       # Disable module
```

**3. Clean Package Layout**
```
neoframework/
├── cmd/              # CLI entry points
├── internal/         # Private framework core (src/)
├── pkg/              # Reusable packages
│   ├── auth/         # Auth package
│   ├── cache/        # Cache package
│   └── database/     # Database package
├── modules/          # Business modules
│   ├── blog/
│   ├── shop/
│   └── user/
└── app/              # Application layer
    ├── AppModule.php
    └── Modules/      # App-specific modules
```

**4. Example Modules**
- Auth Module (Login, Register, Password Reset)
- Blog Module (Posts, Comments, Categories)
- Shop Module (Products, Orders, Cart)

---

### Phase 2: Advanced ORM (Weeks 4-6) 🟠 HIGH

**1. Eloquent-like Features**
- Relationships (HasOne, HasMany, BelongsTo, ManyToMany)
- Eager Loading & Lazy Loading
- Query Scopes (local, global)
- Accessors & Mutators
- Model Events & Observers
- Soft Deletes
- Casting & Hidden attributes

**2. Module-aware ORM**
- Models scoped to modules
- Cross-module relationships
- Module-specific migrations

---

### Phase 3: Auth & API (Weeks 7-9) 🟡 MEDIUM

**1. Advanced Authentication Module**
- Password Reset
- Email Verification
- Remember Me
- Multi-Auth Guards
- Two-Factor Authentication
- Social Login (OAuth)

**2. Authorization System**
- Gates & Policies
- Module-level permissions
- RBAC within modules

**3. API Resources**
- JsonResource transformation
- API versioning
- Module-based API routes

---

### Phase 4: Developer Experience (Weeks 10-12) 🟢 LOW

**1. Testing Support**
- Module testing helpers
- Feature tests per module
- Integration tests across modules

**2. Documentation**
- Module development guide
- Architecture documentation
- Best practices

**3. Module Marketplace Concept**
- Module packaging
- Module distribution
- Module versioning

---

## 🚀 Quick Start

### Installation

```bash
# Clone the repository
git clone https://github.com/neonextechnologies/neoframework.git
cd neoframework

# Install dependencies
composer install

# Setup environment
cp .env.example .env

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=neoframework
DB_USERNAME=root
DB_PASSWORD=

# Run migrations
php neo migrate

# Start development server
php neo serve
```

Visit: `http://localhost:8000`

### Create Your First Module

```bash
# Create a new module
php neo make:module Blog

# This generates:
modules/blog/
├── BlogModule.php
├── Controllers/
├── Models/
├── Services/
├── routes.php
└── config.php

# Edit the module
# modules/blog/BlogModule.php
```

```php
<?php

namespace Modules\Blog;

use NeoPhp\Core\Attributes\Module;

#[Module(
    name: 'blog',
    version: '1.0.0',
    providers: [BlogServiceProvider::class],
    imports: []
)]
class BlogModule
{
    public function boot(): void
    {
        // Register routes, views, migrations
    }
}
```

### Create Module Features

```bash
# Generate controller in module
php neo make:controller Blog/PostController --module=blog

# Generate model in module
php neo make:model Blog/Post --module=blog -m

# Generate service
php neo make:service Blog/PostService --module=blog

# List all modules
php neo module:list

# Enable/Disable module
php neo module:enable blog
php neo module:disable blog
```

---

## 📁 Project Structure

```
neoframework/
├── cmd/                        # CLI Commands (Neonex Style)
│   └── neo                     # CLI executable
│
├── internal/                   # Private Core (mapped from src/)
│   ├── Auth/
│   ├── Cache/
│   ├── Console/
│   ├── Container/
│   ├── Database/
│   ├── Http/
│   └── ...
│
├── pkg/                        # Reusable Packages
│   ├── auth/                   # Auth package (exportable)
│   ├── cache/                  # Cache package
│   ├── database/               # Database package
│   └── http/                   # HTTP package
│
├── modules/                    # Business Modules
│   ├── blog/
│   │   ├── BlogModule.php
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── routes.php
│   │   └── config.php
│   ├── shop/
│   └── user/
│
├── app/                        # Application Layer
│   ├── AppModule.php           # Root module
│   ├── Controllers/            # Shared controllers
│   ├── Models/                 # Shared models
│   ├── Middleware/
│   ├── Providers/
│   └── Modules/                # App-specific modules
│
├── config/                     # Global configuration
├── database/
│   ├── migrations/             # Global migrations
│   └── seeders/
├── public/                     # Web root
├── routes/                     # Global routes
├── storage/
└── tests/
```

### 📦 Module Structure Example

```php
modules/blog/
├── BlogModule.php              # Module definition
├── Controllers/
│   ├── PostController.php
│   └── CommentController.php
├── Models/
│   ├── Post.php
│   └── Comment.php
├── Services/
│   └── PostService.php
├── Repositories/
│   └── PostRepository.php
├── Views/
│   └── posts/
│       ├── index.blade.php
│       └── show.blade.php
├── Migrations/
│   └── 2025_11_27_create_posts_table.php
├── Tests/
│   └── Feature/
│       └── PostTest.php
├── routes.php                  # Module routes
├── config.php                  # Module config
└── module.json                 # Module metadata
```

---

## 🤝 Contributing

We welcome contributions! NeoFramework is actively being developed and we'd love your help.

### How to Contribute

1. Check [DEVELOPMENT_ROADMAP.md](DEVELOPMENT_ROADMAP.md) for features to implement
2. Fork the repository
3. Create a feature branch (`git checkout -b feature/AmazingFeature`)
4. Make your changes
5. Commit your changes (`git commit -m 'Add AmazingFeature'`)
6. Push to the branch (`git push origin feature/AmazingFeature`)
7. Open a Pull Request

### Development Setup

```bash
git clone https://github.com/neonextechnologies/neoframework.git
cd neoframework
composer install
composer dump-autoload
```

---

## 📄 License

MIT License - See [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

Inspired by:
- **Laravel** - Service providers, Eloquent ORM, Blade templates
- **NestJS** - Module architecture
- **Neonex Core** - Foundation architecture patterns

---

<div align="center">

**Built with ❤️ by [Neonex Technologies](https://neonex.co.th)**

[![GitHub](https://img.shields.io/badge/GitHub-neonextechnologies-181717?style=flat-square&logo=github)](https://github.com/neonextechnologies)

</div>
