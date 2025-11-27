# Plugin API Reference

## Overview

This document provides a comprehensive reference for the NeoFramework Plugin API, including all available classes, methods, and interfaces.

## Core Classes

### Plugin

Base class for all plugins.

```php
<?php

namespace Neo\Plugin;

abstract class Plugin
{
    /**
     * Get the plugin name
     */
    public function getName(): string;
    
    /**
     * Get the plugin version
     */
    public function getVersion(): string;
    
    /**
     * Get the plugin path
     */
    public function getPath(string $path = ''): string;
    
    /**
     * Get plugin configuration
     */
    public function getConfig(string $key = null, $default = null);
    
    /**
     * Get plugin manifest
     */
    public function getManifest(): array;
    
    /**
     * Register plugin services
     */
    abstract public function register(): void;
    
    /**
     * Boot plugin
     */
    abstract public function boot(): void;
    
    /**
     * Load routes from file
     */
    protected function loadRoutesFrom(string $path): void;
    
    /**
     * Load views from directory
     */
    protected function loadViewsFrom(string $path, string $namespace): void;
    
    /**
     * Load translations from directory
     */
    protected function loadTranslationsFrom(string $path, string $namespace): void;
    
    /**
     * Load migrations from directory
     */
    protected function loadMigrationsFrom(string $path): void;
    
    /**
     * Publish files
     */
    protected function publishes(array $paths, string $tag = null): void;
    
    /**
     * Merge configuration
     */
    protected function mergeConfigFrom(string $path, string $key): void;
    
    /**
     * Register console commands
     */
    protected function commands(array $commands): void;
}
```

**Example Usage**:

```php
<?php

namespace MyPlugin;

use Neo\Plugin\Plugin as BasePlugin;

class Plugin extends BasePlugin
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            $this->getPath('config/config.php'),
            'my-plugin'
        );
    }
    
    public function boot(): void
    {
        $this->loadRoutesFrom($this->getPath('routes/web.php'));
        $this->loadViewsFrom($this->getPath('resources/views'), 'my-plugin');
    }
}
```

### PluginManager

Manages all installed plugins.

```php
<?php

namespace Neo\Plugin;

class PluginManager
{
    /**
     * Get all plugins
     */
    public function all(): array;
    
    /**
     * Get active plugins
     */
    public function active(): array;
    
    /**
     * Get inactive plugins
     */
    public function inactive(): array;
    
    /**
     * Get specific plugin
     */
    public function get(string $name): ?Plugin;
    
    /**
     * Check if plugin exists
     */
    public function has(string $name): bool;
    
    /**
     * Check if plugin is active
     */
    public function isActive(string $name): bool;
    
    /**
     * Activate plugin
     */
    public function activate(string $name): bool;
    
    /**
     * Deactivate plugin
     */
    public function deactivate(string $name): bool;
    
    /**
     * Install plugin
     */
    public function install(string $source): Plugin;
    
    /**
     * Uninstall plugin
     */
    public function uninstall(string $name, bool $purge = false): bool;
    
    /**
     * Update plugin
     */
    public function update(string $name): bool;
    
    /**
     * Get plugin information
     */
    public function info(string $name): array;
    
    /**
     * Check plugin dependencies
     */
    public function checkDependencies(string $name): array;
    
    /**
     * Get missing dependencies
     */
    public function getMissingDependencies(string $name): array;
}
```

**Example Usage**:

```php
<?php

use Neo\Plugin\PluginManager;

$manager = app(PluginManager::class);

// Get all active plugins
$active = $manager->active();

// Check if plugin is active
if ($manager->isActive('my-plugin')) {
    // Plugin is active
}

// Get plugin instance
$plugin = $manager->get('my-plugin');
$config = $plugin->getConfig();
```

### Hooks

Plugin hook system for extending functionality.

```php
<?php

namespace Neo\Plugin;

class Hooks
{
    /**
     * Register a hook callback
     */
    public static function register(string $hook, callable $callback, int $priority = 10): void;
    
    /**
     * Trigger a hook
     */
    public static function trigger(string $hook, ...$args): void;
    
    /**
     * Apply filters
     */
    public static function apply(string $hook, $value, ...$args);
    
    /**
     * Remove a hook callback
     */
    public static function remove(string $hook, callable $callback): bool;
    
    /**
     * Check if hook has callbacks
     */
    public static function has(string $hook): bool;
    
    /**
     * Get all registered hooks
     */
    public static function all(): array;
    
    /**
     * Clear all hooks
     */
    public static function clear(): void;
}
```

**Example Usage**:

```php
<?php

use Neo\Plugin\Hooks;

// Register hook
Hooks::register('user.created', function($user) {
    logger()->info('User created', ['user_id' => $user->id]);
});

// Trigger hook
Hooks::trigger('user.created', $user);

// Apply filter
$content = Hooks::apply('content.filter', $originalContent);

// Register filter with priority
Hooks::register('content.filter', function($content) {
    return strip_tags($content);
}, 5);
```

## Plugin Events

### Event Classes

```php
<?php

namespace Neo\Plugin\Events;

/**
 * Plugin installing event
 */
class PluginInstalling
{
    public Plugin $plugin;
    
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}

/**
 * Plugin installed event
 */
class PluginInstalled
{
    public Plugin $plugin;
    
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}

/**
 * Plugin activating event
 */
class PluginActivating
{
    public Plugin $plugin;
    
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}

/**
 * Plugin activated event
 */
class PluginActivated
{
    public Plugin $plugin;
    
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}

/**
 * Plugin deactivating event
 */
class PluginDeactivating
{
    public Plugin $plugin;
    
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}

/**
 * Plugin deactivated event
 */
class PluginDeactivated
{
    public Plugin $plugin;
    
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}

/**
 * Plugin uninstalling event
 */
class PluginUninstalling
{
    public Plugin $plugin;
    public bool $purge;
    
    public function __construct(Plugin $plugin, bool $purge = false)
    {
        $this->plugin = $plugin;
        $this->purge = $purge;
    }
}

/**
 * Plugin uninstalled event
 */
class PluginUninstalled
{
    public Plugin $plugin;
    
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}
```

**Example Usage**:

```php
<?php

use Neo\Events\Event;
use Neo\Plugin\Events\PluginActivated;

Event::listen(PluginActivated::class, function(PluginActivated $event) {
    $plugin = $event->plugin;
    
    // Perform post-activation tasks
    logger()->info("Plugin {$plugin->getName()} activated");
    
    // Clear cache
    cache()->clear();
});
```

## Helper Functions

### plugin()

Get plugin instance or manager.

```php
<?php

/**
 * Get plugin instance or manager
 */
function plugin(string $name = null): Plugin|PluginManager
{
    if ($name === null) {
        return app(PluginManager::class);
    }
    
    return app(PluginManager::class)->get($name);
}
```

**Example Usage**:

```php
<?php

// Get plugin manager
$manager = plugin();

// Get specific plugin
$myPlugin = plugin('my-plugin');
$config = $myPlugin->getConfig('api.key');
```

### plugin_path()

Get path to plugin directory.

```php
<?php

/**
 * Get plugin path
 */
function plugin_path(string $name, string $path = ''): string
{
    $plugin = plugin($name);
    return $plugin ? $plugin->getPath($path) : '';
}
```

**Example Usage**:

```php
<?php

// Get plugin config path
$configPath = plugin_path('my-plugin', 'config/config.php');

// Get plugin views path
$viewsPath = plugin_path('my-plugin', 'resources/views');
```

### plugin_config()

Get plugin configuration value.

```php
<?php

/**
 * Get plugin config
 */
function plugin_config(string $plugin, string $key = null, $default = null)
{
    $instance = plugin($plugin);
    return $instance ? $instance->getConfig($key, $default) : $default;
}
```

**Example Usage**:

```php
<?php

// Get plugin config value
$apiKey = plugin_config('my-plugin', 'api.key');

// Get entire config
$config = plugin_config('my-plugin');
```

### plugin_active()

Check if plugin is active.

```php
<?php

/**
 * Check if plugin is active
 */
function plugin_active(string $name): bool
{
    return plugin()->isActive($name);
}
```

**Example Usage**:

```php
<?php

if (plugin_active('my-plugin')) {
    // Plugin is active
}
```

## Configuration API

### Loading Configuration

```php
<?php

// In plugin class
public function register(): void
{
    // Merge plugin config with app config
    $this->mergeConfigFrom(
        $this->getPath('config/config.php'),
        'my-plugin'
    );
}

// Access configuration
$value = config('my-plugin.setting');
$apiKey = config('my-plugin.api.key');
```

### Publishing Configuration

```php
<?php

public function boot(): void
{
    // Publish configuration file
    $this->publishes([
        $this->getPath('config/config.php') => config_path('my-plugin.php'),
    ], 'my-plugin-config');
}

// Publish via command
// php neo vendor:publish --tag=my-plugin-config
```

## Route API

### Loading Routes

```php
<?php

public function boot(): void
{
    // Load routes
    $this->loadRoutesFrom($this->getPath('routes/web.php'));
    
    // With middleware
    $this->app['router']->group([
        'middleware' => ['web', 'auth'],
        'prefix' => 'my-plugin',
    ], function() {
        $this->loadRoutesFrom($this->getPath('routes/web.php'));
    });
}
```

### Route Registration

```php
<?php

// In routes/web.php
use MyPlugin\Controllers\MyController;
use Neo\Routing\Router;

Router::group(['prefix' => 'my-plugin'], function() {
    Router::get('/', [MyController::class, 'index'])
        ->name('my-plugin.index');
        
    Router::get('/{id}', [MyController::class, 'show'])
        ->name('my-plugin.show');
});
```

## View API

### Loading Views

```php
<?php

public function boot(): void
{
    // Load views with namespace
    $this->loadViewsFrom(
        $this->getPath('resources/views'),
        'my-plugin'
    );
}
```

### Rendering Views

```php
<?php

// In controller
return view('my-plugin::index', $data);

// With shared data
view()->share('pluginName', 'My Plugin');

// Compose views
view()->composer('my-plugin::*', function($view) {
    $view->with('sharedData', getData());
});
```

### Publishing Views

```php
<?php

public function boot(): void
{
    $this->publishes([
        $this->getPath('resources/views') => resource_path('views/vendor/my-plugin'),
    ], 'my-plugin-views');
}
```

## Asset API

### Publishing Assets

```php
<?php

public function boot(): void
{
    // Publish assets
    $this->publishes([
        $this->getPath('resources/assets') => public_path('plugins/my-plugin'),
    ], 'my-plugin-assets');
}

// Publish via command
// php neo vendor:publish --tag=my-plugin-assets
```

### Asset URLs

```php
<?php

// In views
<link rel="stylesheet" href="{{ asset('plugins/my-plugin/css/style.css') }}">
<script src="{{ asset('plugins/my-plugin/js/script.js') }}"></script>
<img src="{{ asset('plugins/my-plugin/images/logo.png') }}">
```

## Database API

### Loading Migrations

```php
<?php

public function boot(): void
{
    // Load migrations
    $this->loadMigrationsFrom($this->getPath('database/migrations'));
}

// Run migrations
// php neo migrate
```

### Migration Class

```php
<?php

use Neo\Database\Migration;
use Neo\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema->create('my_plugin_table', function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        $this->schema->dropIfExists('my_plugin_table');
    }
};
```

## Command API

### Registering Commands

```php
<?php

public function boot(): void
{
    if ($this->app->runningInConsole()) {
        $this->commands([
            Commands\MyCommand::class,
            Commands\SyncCommand::class,
        ]);
    }
}
```

### Command Class

```php
<?php

namespace MyPlugin\Commands;

use Neo\Console\Command;

class MyCommand extends Command
{
    protected string $signature = 'my-plugin:action {--option}';
    protected string $description = 'Plugin command description';
    
    public function handle(): int
    {
        $this->info('Starting...');
        $this->line('Processing...');
        $this->comment('Almost done...');
        $this->question('Question?');
        $this->error('Error occurred');
        
        $confirmed = $this->confirm('Continue?');
        $answer = $this->ask('What is your name?');
        $choice = $this->choice('Choose', ['A', 'B', 'C']);
        
        $this->progressBar(100, function($bar) {
            for ($i = 0; $i < 100; $i++) {
                $bar->advance();
                usleep(10000);
            }
        });
        
        return self::SUCCESS;
    }
}
```

## Service Container API

### Binding Services

```php
<?php

public function register(): void
{
    // Singleton binding
    $this->app->singleton('my-plugin.service', function($app) {
        return new Services\MyService();
    });
    
    // Instance binding
    $this->app->bind('my-plugin.repository', function($app) {
        return new Repositories\MyRepository();
    });
    
    // Interface binding
    $this->app->bind(
        Contracts\MyInterface::class,
        Implementations\MyImplementation::class
    );
}
```

### Resolving Services

```php
<?php

// Via helper
$service = app('my-plugin.service');

// Via make
$service = app()->make(Services\MyService::class);

// Via dependency injection
public function __construct(MyService $service)
{
    $this->service = $service;
}
```

## Cache API

### Using Cache

```php
<?php

use Neo\Cache\Cache;

// Store value
Cache::put('my-plugin:key', $value, 3600);

// Get value
$value = Cache::get('my-plugin:key');

// Remember
$value = Cache::remember('my-plugin:key', 3600, function() {
    return expensiveOperation();
});

// Forget
Cache::forget('my-plugin:key');

// Clear plugin cache
Cache::tags(['my-plugin'])->flush();
```

## Event API

### Dispatching Events

```php
<?php

use Neo\Events\Event;

// Dispatch event
Event::dispatch(new MyPluginEvent($data));

// Fire event (alias)
event(new MyPluginEvent($data));

// Dispatch if
Event::dispatchIf($condition, new MyPluginEvent($data));
```

### Listening to Events

```php
<?php

public function boot(): void
{
    Event::listen(MyEvent::class, MyListener::class);
    
    Event::listen(AnotherEvent::class, function($event) {
        // Handle event
    });
}
```

## Middleware API

### Registering Middleware

```php
<?php

public function boot(): void
{
    // Register middleware
    $this->app['router']->middleware(
        'my-plugin',
        Middleware\MyMiddleware::class
    );
    
    // Apply to routes
    $this->app['router']->group([
        'middleware' => 'my-plugin'
    ], function() {
        // Routes
    });
}
```

## Validation API

### Custom Validation Rules

```php
<?php

namespace MyPlugin\Rules;

use Neo\Validation\Rule;

class CustomRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return /* validation logic */;
    }
    
    public function message(): string
    {
        return 'The :attribute is invalid.';
    }
}
```

### Using Validation

```php
<?php

$request->validate([
    'field' => ['required', new CustomRule()],
]);
```

## Translation API

### Loading Translations

```php
<?php

public function boot(): void
{
    $this->loadTranslationsFrom(
        $this->getPath('resources/lang'),
        'my-plugin'
    );
}
```

### Using Translations

```php
<?php

// In PHP
$message = __('my-plugin::messages.welcome');
$message = trans('my-plugin::messages.greeting', ['name' => $name]);

// In views
{{ __('my-plugin::messages.title') }}
{{ trans_choice('my-plugin::messages.items', $count) }}
```

## Next Steps

- Return to [Plugin Introduction](introduction.md)
- Learn about [Plugin Development](development.md)
- Explore [Plugin Distribution](distribution.md)
