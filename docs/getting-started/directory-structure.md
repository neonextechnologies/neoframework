# Directory Structure

## Introduction

Understanding the directory structure is crucial for working efficiently with NeoFramework. This guide explains the purpose of each directory and file.

## Root Directory Structure

```
neoframework/
├── app/                    # Application code
├── bootstrap/              # Framework bootstrap
├── config/                 # Configuration files
├── database/               # Database files
├── docs/                   # Documentation
├── public/                 # Web server document root
├── resources/              # Views and assets
├── routes/                 # Route definitions
├── src/                    # Framework core
├── storage/                # Generated files
├── tests/                  # Automated tests
├── .env                    # Environment variables
├── .env.example            # Example environment file
├── .gitignore             # Git ignore rules
├── composer.json          # Composer dependencies
├── neo                    # CLI entry point
├── phpunit.xml            # PHPUnit configuration
└── README.md              # Project readme
```

## The App Directory

The `app` directory contains the core code of your application.

```
app/
├── Console/               # Console commands
│   └── Commands/
├── Controllers/           # HTTP controllers
│   ├── Controller.php    # Base controller
│   └── HomeController.php
├── Middleware/            # HTTP middleware
│   ├── AuthMiddleware.php
│   └── CorsMiddleware.php
├── Models/                # Eloquent models
│   └── User.php
├── Modules/               # Application modules
│   └── User/
├── Policies/              # Authorization policies
├── Providers/             # Service providers
│   └── AppServiceProvider.php
└── AppModule.php         # Main application module
```

### Console Directory

Contains all custom Artisan commands. Each command is a class that extends the `Command` base class.

```php
namespace App\Console\Commands;

use NeoPhp\Console\Command;

class SendEmails extends Command
{
    protected string $signature = 'emails:send';
    protected string $description = 'Send emails to users';

    public function handle(): int
    {
        // Command logic
        return 0;
    }
}
```

### Controllers Directory

Contains all HTTP controllers. Controllers handle incoming requests and return responses.

```php
namespace App\Controllers;

use App\Models\User;
use NeoPhp\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }
}
```

### Middleware Directory

Contains middleware classes that filter HTTP requests entering your application.

```php
namespace App\Middleware;

use NeoPhp\Http\Request;
use NeoPhp\Http\Response;

class CheckAge
{
    public function handle(Request $request, callable $next): Response
    {
        if ($request->input('age') < 18) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
```

### Models Directory

Contains all Eloquent model classes representing database tables.

```php
namespace App\Models;

use NeoPhp\Database\Model;

class Post extends Model
{
    protected $fillable = ['title', 'body'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Policies Directory

Contains authorization policy classes for your models.

```php
namespace App\Policies;

use App\Models\User;
use App\Models\Post;

class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
```

### Providers Directory

Contains service providers that bootstrap services, register bindings, and configure the application.

```php
namespace App\Providers;

use NeoPhp\Container\Container;

class AppServiceProvider
{
    public function register(Container $container): void
    {
        // Register bindings
    }

    public function boot(): void
    {
        // Bootstrap code
    }
}
```

## The Bootstrap Directory

Contains files that bootstrap the framework.

```
bootstrap/
├── app.php                # Creates application instance
└── cache/                 # Framework cache
```

The `app.php` file creates the application instance and binds important interfaces.

## The Config Directory

Contains all configuration files.

```
config/
├── app.php                # Application configuration
├── cache.php              # Cache configuration
├── cors.php               # CORS configuration
├── database.php           # Database connections
├── mail.php               # Mail configuration
└── queue.php              # Queue configuration
```

All options are documented within the files themselves.

## The Database Directory

Contains database migrations, seeders, and factories.

```
database/
├── factories/             # Model factories
│   └── UserFactory.php
├── migrations/            # Database migrations
│   └── 2024_01_01_create_users_table.php
└── seeders/               # Database seeders
    └── DatabaseSeeder.php
```

### Migrations

Migrations are like version control for your database:

```php
use NeoPhp\Database\Migration;
use NeoPhp\Database\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function($table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

### Seeders

Seeders populate your database with test data:

```php
use NeoPhp\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(10)->create();
    }
}
```

### Factories

Factories generate fake data for testing:

```php
namespace Database\Factories;

use NeoPhp\Testing\Factory;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->randomString(10),
            'email' => $this->randomEmail(),
        ];
    }
}
```

## The Public Directory

The `public` directory contains the entry point (`index.php`) and assets (images, JavaScript, CSS).

```
public/
├── index.php              # Application entry point
├── .htaccess              # Apache rewrite rules
├── css/                   # Stylesheets
├── js/                    # JavaScript files
└── images/                # Images
```

This is the document root for your web server.

## The Resources Directory

Contains views, raw assets, and language files.

```
resources/
├── lang/                  # Translation files
│   ├── en/
│   │   └── messages.php
│   └── th/
│       └── messages.php
└── views/                 # Blade templates
    ├── layouts/
    │   └── app.blade.php
    └── home.blade.php
```

### Language Files

Translation files for localization:

```php
// resources/lang/en/messages.php
return [
    'welcome' => 'Welcome to our application!',
    'goodbye' => 'Goodbye, :name',
];
```

### Views

Blade template files:

```html
<!-- resources/views/home.blade.php -->
@extends('layouts.app')

@section('content')
    <h1>{{ __('messages.welcome') }}</h1>
@endsection
```

## The Routes Directory

Contains all route definitions for your application.

```
routes/
└── web.php                # Web routes
```

**routes/web.php:**
```php
<?php

use App\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/about', [HomeController::class, 'about']);

// API routes
Route::group(['prefix' => 'api'], function() {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
});
```

## The Src Directory

Contains the framework's core code. **You typically don't need to modify these files.**

```
src/
├── Auth/                  # Authentication
├── Cache/                 # Cache system
├── Console/               # CLI framework
├── Container/             # DI container
├── Database/              # ORM & Query Builder
├── Events/                # Event system
├── Foundation/            # Foundation classes
├── Http/                  # HTTP layer
├── Mail/                  # Mail system
├── Queue/                 # Queue system
├── Testing/               # Testing framework
├── Translation/           # Localization
├── Validation/            # Validation
├── helpers.php            # Helper functions
└── foundation_helpers.php # Foundation helpers
```

## The Storage Directory

Contains compiled templates, file uploads, logs, and cache.

```
storage/
├── app/                   # Application storage
│   └── uploads/          # File uploads
├── cache/                 # Cached files
│   └── data/
├── logs/                  # Application logs
│   └── app.log
└── views/                 # Compiled Blade views
```

### Directory Permissions

The `storage` directory and `bootstrap/cache` directory must be writable:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## The Tests Directory

Contains automated tests.

```
tests/
├── bootstrap.php          # Test bootstrap
├── Feature/               # Feature tests
│   └── ExampleFeatureTest.php
└── Unit/                  # Unit tests
    └── ExampleUnitTest.php
```

### Feature Tests

Test complete features:

```php
use NeoPhp\Testing\TestCase;

class PostTest extends TestCase
{
    public function test_user_can_create_post()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/api/posts', [
                'title' => 'Test Post'
            ]);

        $response->assertStatus(201);
    }
}
```

### Unit Tests

Test individual classes:

```php
use NeoPhp\Testing\TestCase;

class HelperTest extends TestCase
{
    public function test_config_helper()
    {
        $this->assertEquals('production', config('app.env'));
    }
}
```

## The Neo CLI File

The `neo` file is the command-line interface for the framework:

```bash
php neo list                    # List all commands
php neo make:model Post        # Create a model
php neo migrate                # Run migrations
php neo serve                  # Start dev server
```

## Configuration Files

### composer.json

Defines PHP dependencies and autoloading:

```json
{
    "require": {
        "php": "^8.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "NeoPhp\\": "src/",
            "Database\\": "database/"
        },
        "files": [
            "src/helpers.php"
        ]
    }
}
```

### phpunit.xml

PHPUnit testing configuration:

```xml
<?xml version="1.0"?>
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

### .env

Environment-specific configuration:

```env
APP_NAME=NeoFramework
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=myapp
```

## Best Practices

### File Organization

1. **Models** - One model per file in `app/Models/`
2. **Controllers** - Group related actions in one controller
3. **Middleware** - One middleware per file
4. **Policies** - One policy per model

### Naming Conventions

- **Models**: Singular, PascalCase - `User`, `BlogPost`
- **Controllers**: PascalCase + "Controller" - `UserController`
- **Migrations**: Snake_case with timestamp
- **Views**: Kebab-case - `user-profile.blade.php`

### Code Organization Tips

1. Use service providers for bootstrapping
2. Keep controllers thin, move logic to services
3. Use form requests for validation
4. Use policies for authorization
5. Use factories for test data

## Next Steps

Now that you understand the structure:

- [Routing](../basics/routing.md) - Define your routes
- [Controllers](../basics/controllers.md) - Handle requests
- [Models](../database/orm.md) - Work with databases
- [Views](../basics/views.md) - Create templates
