# Configuration

## Introduction

All configuration files for NeoFramework are stored in the `config` directory. Each option is documented, so feel free to explore and familiarize yourself with the options available.

## Environment Configuration

### The .env File

NeoFramework uses environment variables to manage configuration across different environments. Your `.env` file should never be committed to source control.

```env
APP_NAME="NeoFramework"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myapp
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
QUEUE_CONNECTION=database
MAIL_MAILER=smtp
```

### Accessing Environment Variables

You can access environment variables using the `env()` helper:

```php
$appName = env('APP_NAME', 'NeoFramework');
$debug = env('APP_DEBUG', false);
```

The second parameter is the default value if the variable isn't set.

### Environment Variable Types

Environment variables are loaded as strings. Some common values are converted:

| `.env` Value | `env()` Value |
|--------------|---------------|
| true / (true) | `true` |
| false / (false) | `false` |
| empty / (empty) | `''` |
| null / (null) | `null` |

## Configuration Files

### Application Configuration

**File:** `config/app.php`

```php
return [
    'name' => env('APP_NAME', 'NeoFramework'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'Asia/Bangkok',
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => 'en',
];
```

### Database Configuration

**File:** `config/database.php`

```php
return [
    'default' => env('DB_CONNECTION', 'mysql'),
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'myapp'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', 'database.sqlite'),
        ],
        
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 5432),
            'database' => env('DB_DATABASE', 'myapp'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
        ],
    ],
];
```

### Cache Configuration

**File:** `config/cache.php`

```php
return [
    'default' => env('CACHE_DRIVER', 'file'),
    
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('cache'),
        ],
        
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],
    ],
];
```

### Queue Configuration

**File:** `config/queue.php`

```php
return [
    'default' => env('QUEUE_CONNECTION', 'database'),
    
    'connections' => [
        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ],
        
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'default',
            'retry_after' => 90,
        ],
    ],
];
```

### Mail Configuration

**File:** `config/mail.php`

```php
return [
    'default' => env('MAIL_MAILER', 'smtp'),
    
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        ],
        
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => '/usr/sbin/sendmail -bs',
        ],
    ],
    
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'NeoFramework'),
    ],
];
```

## Accessing Configuration Values

### Using the config() Helper

Access configuration values using dot notation:

```php
// Get single value
$appName = config('app.name');
$timezone = config('app.timezone');

// Get with default
$debug = config('app.debug', false);

// Get nested values
$driver = config('database.connections.mysql.driver');
```

### Setting Configuration Values

Set configuration values at runtime:

```php
config(['app.name' => 'My Application']);

// Set multiple values
config([
    'app.name' => 'My App',
    'app.timezone' => 'UTC',
]);
```

> **Note:** Runtime configuration changes are not persisted. They only affect the current request.

## Configuration Caching

For production, cache your configuration for better performance:

```bash
php neo config:cache
```

This will create a single cached file containing all configuration. To clear the cache:

```bash
php neo config:clear
```

> **Warning:** When configuration is cached, the `.env` file will not be loaded. All configuration must be in config files.

## Environment-Specific Configuration

### Determining the Current Environment

```php
$env = app()->environment();

if (app()->environment('local')) {
    // Local environment
}

if (app()->environment(['local', 'staging'])) {
    // Local or staging
}
```

### Multiple Environment Files

You can create environment-specific files:

- `.env.local` - Local development
- `.env.staging` - Staging server
- `.env.production` - Production server

Load a specific environment file:

```bash
APP_ENV=staging php neo migrate
```

## Configuration Values by Environment

### Development (Local)

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_DATABASE=myapp_dev

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
```

### Staging

```env
APP_ENV=staging
APP_DEBUG=true
APP_URL=https://staging.example.com

DB_CONNECTION=mysql
DB_DATABASE=myapp_staging

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
MAIL_MAILER=smtp
```

### Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com

DB_CONNECTION=mysql
DB_DATABASE=myapp_prod

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
MAIL_MAILER=smtp
```

## Security Considerations

### Protecting Sensitive Data

Never commit your `.env` file to version control. Add it to `.gitignore`:

```gitignore
.env
.env.backup
.env.production
```

### Environment File Encryption

For additional security, encrypt sensitive values:

```env
DB_PASSWORD=ENC:encrypted_value_here
```

### Server-Level Environment Variables

For production, consider setting environment variables at the server level instead of using `.env` files.

**Apache:**
```apache
SetEnv APP_ENV production
SetEnv APP_DEBUG false
```

**Nginx:**
```nginx
fastcgi_param APP_ENV production;
fastcgi_param APP_DEBUG false;
```

## Custom Configuration Files

Create your own configuration files in the `config/` directory:

**config/services.php:**
```php
<?php

return [
    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URL'),
    ],
    
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
];
```

Access your custom configuration:

```php
$githubId = config('services.github.client_id');
```

## Best Practices

1. **Use Environment Variables** - Keep sensitive data in `.env`
2. **Never Commit .env** - Add to `.gitignore`
3. **Cache in Production** - Run `config:cache` for performance
4. **Document Config Options** - Add comments to config files
5. **Use Defaults** - Always provide sensible default values
6. **Group Related Config** - Create separate files for different features

## Next Steps

- [Quick Start Tutorial](quick-start.md) - Build your first app
- [Directory Structure](directory-structure.md) - Understand the layout
- [Routing](../basics/routing.md) - Define routes
