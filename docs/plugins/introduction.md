# Plugin System

## Introduction

NeoFramework's plugin system provides a powerful and flexible way to extend the framework's functionality without modifying core code. Plugins are self-contained packages that can add features, modify behavior, and integrate seamlessly with your application.

## What are Plugins?

Plugins are modular extensions that can:

- Add new routes and controllers
- Register service providers
- Extend existing functionality
- Add middleware and commands
- Provide views and assets
- Integrate third-party services
- Modify application behavior through hooks

## Plugin Architecture

### Plugin Structure

A typical plugin directory structure:

```
plugins/
└── my-plugin/
    ├── plugin.json          # Plugin manifest
    ├── Plugin.php           # Main plugin class
    ├── README.md            # Documentation
    ├── composer.json        # Dependencies
    ├── config/
    │   └── config.php       # Configuration
    ├── src/
    │   ├── Controllers/
    │   ├── Models/
    │   ├── Services/
    │   └── Middleware/
    ├── routes/
    │   └── web.php
    ├── resources/
    │   ├── views/
    │   ├── lang/
    │   └── assets/
    ├── database/
    │   ├── migrations/
    │   └── seeders/
    └── tests/
        ├── Unit/
        └── Feature/
```

### Plugin Manifest

The `plugin.json` file defines plugin metadata:

```json
{
    "name": "my-plugin",
    "title": "My Awesome Plugin",
    "description": "A plugin that adds awesome features",
    "version": "1.0.0",
    "author": "Your Name",
    "email": "you@example.com",
    "homepage": "https://example.com",
    "license": "MIT",
    "keywords": ["neo", "plugin", "awesome"],
    "require": {
        "php": ">=8.1",
        "neoframework/framework": "^1.0"
    },
    "dependencies": [
        "another-plugin"
    ],
    "autoload": {
        "psr-4": {
            "MyPlugin\\": "src/"
        }
    },
    "providers": [
        "MyPlugin\\MyPluginServiceProvider"
    ],
    "hooks": {
        "boot": "MyPlugin\\Plugin::onBoot",
        "request.before": "MyPlugin\\Plugin::beforeRequest"
    }
}
```

## Core Concepts

### Plugin Class

Every plugin has a main class that extends `Neo\Plugin\Plugin`:

```php
<?php

namespace MyPlugin;

use Neo\Plugin\Plugin as BasePlugin;

class Plugin extends BasePlugin
{
    /**
     * Plugin initialization
     */
    public function register(): void
    {
        // Register services, bindings, etc.
        $this->app->singleton('my-plugin.service', function($app) {
            return new Services\MyService();
        });
    }
    
    /**
     * Plugin boot
     */
    public function boot(): void
    {
        // Boot plugin features
        $this->loadRoutes();
        $this->loadViews();
        $this->loadMigrations();
        $this->publishAssets();
    }
    
    /**
     * Load plugin routes
     */
    protected function loadRoutes(): void
    {
        if (file_exists($routes = $this->path('routes/web.php'))) {
            $this->loadRoutesFrom($routes);
        }
    }
    
    /**
     * Load plugin views
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(
            $this->path('resources/views'),
            'my-plugin'
        );
    }
    
    /**
     * Load migrations
     */
    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom($this->path('database/migrations'));
    }
    
    /**
     * Publish assets
     */
    protected function publishAssets(): void
    {
        $this->publishes([
            $this->path('resources/assets') => public_path('plugins/my-plugin'),
        ], 'my-plugin-assets');
    }
}
```

### Service Providers

Plugins can register service providers:

```php
<?php

namespace MyPlugin;

use Neo\Foundation\ServiceProvider;

class MyPluginServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->app->bind('my-plugin.repository', function($app) {
            return new Repositories\MyRepository();
        });
        
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php',
            'my-plugin'
        );
    }
    
    /**
     * Boot services
     */
    public function boot(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\MyCommand::class,
            ]);
        }
        
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('my-plugin.php'),
        ], 'my-plugin-config');
    }
}
```

## Plugin Lifecycle

### Installation

Install a plugin:

```bash
# Via Composer
composer require vendor/plugin-name

# Via CLI
php neo plugin:install plugin-name

# From ZIP
php neo plugin:install /path/to/plugin.zip
```

### Activation

Activate installed plugins:

```bash
# Activate plugin
php neo plugin:activate plugin-name

# Activate all plugins
php neo plugin:activate --all
```

### Deactivation

Deactivate plugins:

```bash
# Deactivate plugin
php neo plugin:deactivate plugin-name

# Deactivate all plugins
php neo plugin:deactivate --all
```

### Uninstallation

Uninstall plugins:

```bash
# Uninstall plugin
php neo plugin:uninstall plugin-name

# Uninstall with data removal
php neo plugin:uninstall plugin-name --purge
```

## Plugin Management

### Listing Plugins

```bash
# List all plugins
php neo plugin:list

# List active plugins
php neo plugin:list --active

# List inactive plugins
php neo plugin:list --inactive
```

### Plugin Information

```bash
# Show plugin details
php neo plugin:info plugin-name

# Show plugin dependencies
php neo plugin:dependencies plugin-name
```

### Updating Plugins

```bash
# Update single plugin
php neo plugin:update plugin-name

# Update all plugins
php neo plugin:update --all

# Check for updates
php neo plugin:outdated
```

## Plugin API

### Accessing Plugins

```php
<?php

use Neo\Plugin\PluginManager;

// Get plugin manager
$plugins = app(PluginManager::class);

// Get specific plugin
$plugin = $plugins->get('plugin-name');

// Check if plugin exists
if ($plugins->has('plugin-name')) {
    // Plugin exists
}

// Check if plugin is active
if ($plugins->isActive('plugin-name')) {
    // Plugin is active
}

// Get all active plugins
$active = $plugins->active();

// Get all plugins
$all = $plugins->all();
```

### Plugin Information

```php
<?php

$plugin = $plugins->get('my-plugin');

// Get plugin name
$name = $plugin->getName();

// Get plugin version
$version = $plugin->getVersion();

// Get plugin path
$path = $plugin->getPath();

// Get plugin config
$config = $plugin->getConfig();

// Get plugin manifest
$manifest = $plugin->getManifest();
```

### Plugin Events

```php
<?php

use Neo\Events\Event;
use Neo\Plugin\Events\*;

// Plugin installing
Event::listen(PluginInstalling::class, function($event) {
    $plugin = $event->plugin;
    // Perform pre-installation tasks
});

// Plugin installed
Event::listen(PluginInstalled::class, function($event) {
    $plugin = $event->plugin;
    // Perform post-installation tasks
});

// Plugin activating
Event::listen(PluginActivating::class, function($event) {
    $plugin = $event->plugin;
    // Check requirements
});

// Plugin activated
Event::listen(PluginActivated::class, function($event) {
    $plugin = $event->plugin;
    // Setup plugin data
});

// Plugin deactivating
Event::listen(PluginDeactivating::class, function($event) {
    $plugin = $event->plugin;
    // Cleanup tasks
});

// Plugin deactivated
Event::listen(PluginDeactivated::class, function($event) {
    $plugin = $event->plugin;
    // Finalize deactivation
});

// Plugin uninstalling
Event::listen(PluginUninstalling::class, function($event) {
    $plugin = $event->plugin;
    // Remove plugin data
});

// Plugin uninstalled
Event::listen(PluginUninstalled::class, function($event) {
    $plugin = $event->plugin;
    // Final cleanup
});
```

## Plugin Hooks

### Registering Hooks

```php
<?php

namespace MyPlugin;

use Neo\Plugin\Plugin as BasePlugin;
use Neo\Plugin\Hooks;

class Plugin extends BasePlugin
{
    public function boot(): void
    {
        // Register hooks
        Hooks::register('user.created', [$this, 'onUserCreated']);
        Hooks::register('post.published', [$this, 'onPostPublished']);
    }
    
    public function onUserCreated($user)
    {
        // Handle user created event
        logger()->info('User created via plugin', ['user' => $user->id]);
    }
    
    public function onPostPublished($post)
    {
        // Handle post published event
        // Send notifications, update cache, etc.
    }
}
```

### Triggering Hooks

```php
<?php

use Neo\Plugin\Hooks;

// Trigger a hook
Hooks::trigger('user.created', $user);

// Trigger with multiple arguments
Hooks::trigger('post.updated', $post, $oldData, $newData);

// Trigger and get results
$results = Hooks::apply('content.filter', $content);
```

### Available Hooks

Common framework hooks:

- `app.boot` - Application booting
- `app.booted` - Application booted
- `request.before` - Before request handling
- `request.after` - After request handling
- `route.matched` - Route matched
- `controller.before` - Before controller action
- `controller.after` - After controller action
- `view.render` - View rendering
- `cache.write` - Cache writing
- `cache.read` - Cache reading
- `database.query` - Database query execution
- `model.creating` - Model creating
- `model.created` - Model created
- `model.updating` - Model updating
- `model.updated` - Model updated
- `model.deleting` - Model deleting
- `model.deleted` - Model deleted

## Plugin Configuration

### Configuration Files

Create plugin configuration:

```php
<?php

// plugins/my-plugin/config/config.php
return [
    'enabled' => true,
    
    'settings' => [
        'option1' => 'value1',
        'option2' => 'value2',
    ],
    
    'features' => [
        'feature1' => true,
        'feature2' => false,
    ],
    
    'api' => [
        'endpoint' => env('MY_PLUGIN_API_ENDPOINT'),
        'key' => env('MY_PLUGIN_API_KEY'),
    ],
];
```

### Accessing Configuration

```php
<?php

// Get plugin configuration
$config = config('my-plugin');

// Get specific value
$enabled = config('my-plugin.enabled');
$apiKey = config('my-plugin.api.key');

// Set configuration at runtime
config(['my-plugin.enabled' => false]);
```

### Publishing Configuration

```php
<?php

// In service provider
public function boot()
{
    $this->publishes([
        __DIR__.'/../config/config.php' => config_path('my-plugin.php'),
    ], 'my-plugin-config');
}

// Publish via CLI
php neo vendor:publish --tag=my-plugin-config
```

## Plugin Dependencies

### Declaring Dependencies

In `plugin.json`:

```json
{
    "dependencies": [
        "required-plugin",
        "another-plugin"
    ],
    "conflicts": [
        "incompatible-plugin"
    ],
    "suggests": [
        "optional-plugin"
    ]
}
```

### Checking Dependencies

```php
<?php

$plugin = $plugins->get('my-plugin');

// Check if dependencies are met
if ($plugin->dependenciesMet()) {
    $plugin->activate();
} else {
    $missing = $plugin->getMissingDependencies();
    echo "Missing dependencies: " . implode(', ', $missing);
}
```

## Best Practices

1. **Follow Naming Conventions**: Use consistent, descriptive plugin names
2. **Version Properly**: Use semantic versioning (MAJOR.MINOR.PATCH)
3. **Document Thoroughly**: Provide comprehensive README and documentation
4. **Handle Errors Gracefully**: Catch and log errors appropriately
5. **Test Extensively**: Write unit and feature tests
6. **Optimize Performance**: Minimize impact on application performance
7. **Secure by Default**: Follow security best practices
8. **Provide Configuration**: Allow users to customize behavior
9. **Use Events and Hooks**: Integrate with framework properly
10. **Clean Up**: Remove data on uninstallation if requested

## Security Considerations

### Input Validation

Always validate user input:

```php
<?php

namespace MyPlugin\Controllers;

use Neo\Http\Request;

class MyController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);
        
        // Process validated data
    }
}
```

### Authorization

Check permissions:

```php
<?php

public function update(Request $request, $id)
{
    $item = Item::findOrFail($id);
    
    // Check authorization
    if (!$request->user()->can('update', $item)) {
        abort(403);
    }
    
    // Update item
}
```

### CSRF Protection

Include CSRF tokens in forms:

```php
<!-- In view -->
<form method="POST" action="/my-plugin/save">
    @csrf
    <!-- Form fields -->
    <button type="submit">Submit</button>
</form>
```

### SQL Injection Prevention

Use query builder or prepared statements:

```php
<?php

// Good: Using query builder
$users = DB::table('users')
    ->where('email', $email)
    ->get();

// Good: Using Eloquent
$user = User::where('email', $email)->first();

// Bad: Raw queries with user input
// $users = DB::select("SELECT * FROM users WHERE email = '$email'");
```

## Performance Optimization

### Caching

Use caching for expensive operations:

```php
<?php

use Neo\Cache\Cache;

public function getData()
{
    return Cache::remember('my-plugin.data', 3600, function() {
        return $this->expensiveOperation();
    });
}
```

### Lazy Loading

Load resources only when needed:

```php
<?php

public function boot()
{
    // Only load admin routes in admin area
    if ($this->app->request()->is('admin/*')) {
        $this->loadAdminRoutes();
    }
}
```

### Asset Optimization

Minify and combine assets:

```php
<?php

public function publishAssets()
{
    if (app()->environment('production')) {
        // Publish minified assets
        $this->publishes([
            $this->path('dist') => public_path('plugins/my-plugin'),
        ]);
    }
}
```

## Debugging Plugins

### Enable Debug Mode

```php
<?php

// In plugin class
public function boot()
{
    if (config('my-plugin.debug')) {
        $this->enableDebugMode();
    }
}

protected function enableDebugMode()
{
    // Register debug bar collectors
    // Enable verbose logging
    logger()->debug('Plugin debug mode enabled');
}
```

### Logging

Use framework logger:

```php
<?php

use Neo\Logging\Log;

// Different log levels
Log::debug('Debug information', ['data' => $data]);
Log::info('Informational message');
Log::notice('Normal but significant');
Log::warning('Warning message');
Log::error('Error occurred', ['exception' => $e]);
Log::critical('Critical condition');
Log::alert('Action must be taken immediately');
Log::emergency('System is unusable');
```

## Next Steps

- Learn [Plugin Development](development.md)
- Explore [Plugin API Reference](plugin-api.md)
- Understand [Plugin Distribution](distribution.md)
