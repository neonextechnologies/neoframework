# 🚀 NeoFramework - Modern PHP Full-Stack Framework

<div align="center">

![PHP Version](https://img.shields.io/badge/PHP-8.0%20to%208.4-777BB4?style=flat-square&logo=php)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)
![Status](https://img.shields.io/badge/Status-Production%20Ready-brightgreen?style=flat-square)
![Type](https://img.shields.io/badge/Type-Full%20Stack-blue?style=flat-square)
![Progress](https://img.shields.io/badge/Progress-95%25-success?style=flat-square)

**A modern, elegant PHP framework for web artisans**  
*Full-stack framework with advanced ORM, authentication, testing, and developer tools*

[Features](#-features) • [Quick Start](#-quick-start) • [Documentation](#-documentation) • [Roadmap](#-development-status)

</div>

---

## 📖 About NeoFramework

**NeoFramework** is a **production-ready, modular monolith full-stack PHP framework** inspired by Neonex Core Architecture and Laravel. It provides enterprise-grade features while maintaining simplicity and excellent developer experience.

### 🎯 Key Highlights

- ✅ **95% Complete** - Production ready with all core features
- 🏗️ **Modular Architecture** - Clean separation with plugin system
- 🗄️ **Advanced ORM** - Eloquent-like with full relationship support
- 🔐 **Complete Auth** - Multi-guard authentication & authorization
- 🧪 **Testing Suite** - PHPUnit integration with factories
- 🌍 **Localization** - Multi-language support with pluralization
- 🛠️ **25+ CLI Commands** - Comprehensive code generators
- 📊 **Debug Toolbar** - Real-time performance monitoring

---

## ✨ Features

### 🗄️ Advanced ORM (100% Complete)

**Eloquent-like ORM with full relationship support:**

```php
// Relationships
$user->posts()->with('comments', 'tags')->get();
$post->user()->first();
$user->roles()->attach($roleId);

// Eager Loading
$users = User::with(['posts.comments', 'roles'])->get();

// Query Scopes
User::active()->verified()->latest()->paginate(15);

// Accessors & Mutators
$user->full_name;  // accessor
$user->password = 'secret';  // auto-hashes

// Model Events
User::creating(fn($user) => $user->uuid = uuid());

// Soft Deletes
$user->delete();
User::withTrashed()->get();

// Attribute Casting
protected $casts = [
    'is_admin' => 'boolean',
    'settings' => 'array',
    'created_at' => 'datetime'
];
```

### 🔐 Authentication & Authorization (100% Complete)

**Complete auth system with multiple guards:**

```php
// Multi-Guard Authentication
auth('web')->attempt($credentials, $remember = true);
auth('api')->user();

// Password Reset
PasswordBroker::sendResetLink($email);
PasswordBroker::reset($email, $token, $newPassword);

// Email Verification
$user->sendEmailVerificationNotification();
$user->markEmailAsVerified();

// Gates & Policies
Gate::define('update-post', fn($user, $post) => 
    $user->id === $post->user_id
);

$this->authorize('update', $post);

// Helper Functions
if (can('update', $post)) { ... }
cannot('delete', $post) ? ... : ...;
```

### 🌐 HTTP Layer (100% Complete)

**Modern request handling and validation:**

```php
// Form Request Validation
class StorePostRequest extends FormRequest
{
    public function authorize(): bool {
        return $this->user()->can('create', Post::class);
    }
    
    public function rules(): array {
        return [
            'title' => 'required|max:255',
            'body' => 'required',
            'tags' => 'array'
        ];
    }
}

// API Resources
class UserResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'posts' => PostResource::collection(
                $this->whenLoaded('posts')
            ),
        ];
    }
}

return UserResource::collection($users);
```

### 📧 Queue & Mail (100% Complete)

**Job dispatching with chains and batches:**

```php
// Job Classes
class ProcessPodcast extends Job
{
    use Dispatchable, Queueable;
    
    public function handle(): void {
        // Process podcast
    }
}

// Dispatch
ProcessPodcast::dispatch($podcast)
    ->delay(now()->addMinutes(10))
    ->onQueue('processing');

// Job Chains
Bus::chain([
    new ProcessPodcast($podcast),
    new PublishPodcast($podcast),
    new NotifyUsers($podcast)
])->dispatch();

// Mailable Classes
class OrderShipped extends Mailable
{
    public function build() {
        return $this->view('emails.order-shipped')
            ->attach('/path/to/invoice.pdf');
    }
}

Mail::to($user)->queue(new OrderShipped($order));
```

### 🧪 Testing Support (100% Complete)

**Comprehensive testing framework:**

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

// Model Factories
$users = User::factory()->count(10)->create();
$admin = User::factory()->admin()->verified()->create();
```

### 🌍 Localization (100% Complete)

**Multi-language support:**

```php
// Translation
echo __('messages.welcome');
echo trans('messages.hello', ['name' => 'John']);

// Pluralization
echo trans_choice('items.users', 5);  // "5 users"

// Set Locale
set_locale('th');
app_locale();  // 'th'

// Translation Files
resources/lang/en/messages.php
resources/lang/th/messages.php
```

### 🛠️ CLI Tools (25+ Commands)

```bash
# Code Generators
php neo make:model Post -m -c -r -f -s
php neo make:controller PostController
php neo make:request StorePostRequest
php neo make:resource PostResource
php neo make:job ProcessPost
php neo make:mail OrderShipped
php neo make:policy PostPolicy
php neo make:test PostTest
php neo make:factory UserFactory

# Database
php neo migrate
php neo migrate:fresh --seed
php neo db:seed

# Development
php neo serve
php neo test
php neo route:list
```

### 📊 Developer Tools (100% Complete)

**Debug toolbar and error pages:**

- ✅ **Debug Bar** - Time, memory, queries, logs
- ✅ **Whoops-style Error Pages** - Beautiful error traces
- ✅ **Query Logger** - Track all database queries
- ✅ **Performance Profiler** - Real-time metrics

---

## 🚀 Quick Start
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
