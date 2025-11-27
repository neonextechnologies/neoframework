# Plugins 🔌

## Introduction

NeoFramework's plugin system provides a powerful way to extend your application's functionality without modifying core code. Plugins are self-contained packages that can add features, modify behavior, and integrate with your application through hooks, service providers, and the container.

The plugin architecture is inspired by WordPress and Neonex Core, combining the flexibility of hooks with modern PHP dependency injection and object-oriented design patterns. Plugins can be installed, activated, deactivated, and uninstalled independently, making your application truly modular and extensible.

## Understanding Plugins

A plugin is a self-contained module that:
- Has its own namespace and directory structure
- Implements the `PluginInterface`
- Can register hooks, services, and routes
- Has a defined lifecycle (install, activate, deactivate, uninstall)
- Can declare dependencies on other plugins
- Can be enabled or disabled without affecting core functionality

## Creating a Plugin

### Using the Generator

Generate a new plugin using the Neo CLI:

```bash
php neo make:plugin Analytics
php neo make:plugin PaymentGateway
php neo make:plugin SocialSharing
```

This creates a plugin structure in `plugins/`:

```
plugins/
└── analytics/
    ├── Plugin.php
    ├── config.php
    ├── routes.php
    ├── README.md
    ├── Controllers/
    ├── Models/
    ├── Views/
    └── Migrations/
```

### Basic Plugin Structure

```php
<?php

namespace Plugins\Analytics;

use NeoPhp\Plugin\Plugin as BasePlugin;
use NeoPhp\DI\Container;

class Plugin extends BasePlugin
{
    /**
     * Plugin name
     */
    protected string $name = 'Analytics';

    /**
     * Plugin version
     */
    protected string $version = '1.0.0';

    /**
     * Plugin description
     */
    protected string $description = 'Advanced analytics and tracking for your application';

    /**
     * Plugin dependencies
     */
    protected array $dependencies = [];

    /**
     * Install the plugin
     */
    public function install(): void
    {
        // Create database tables
        $this->createTables();
        
        // Set default configuration
        $this->setDefaultConfig();
    }

    /**
     * Uninstall the plugin
     */
    public function uninstall(): void
    {
        // Remove database tables
        $this->dropTables();
        
        // Clean up configuration
        $this->removeConfig();
    }

    /**
     * Boot the plugin
     */
    public function boot(): void
    {
        // Register hooks
        $this->registerHooks();
        
        // Register services
        $this->registerServices();
        
        // Load routes
        $this->loadRoutes();
    }

    /**
     * Register plugin hooks
     */
    protected function registerHooks(): void
    {
        hook_action('page.view', [$this, 'trackPageView'], 10, 1);
        hook_action('user.registered', [$this, 'trackSignup'], 10, 1);
        hook_filter('dashboard.widgets', [$this, 'addAnalyticsWidget'], 10, 1);
    }

    /**
     * Register plugin services
     */
    protected function registerServices(): void
    {
        app()->singleton(AnalyticsService::class, function ($app) {
            return new AnalyticsService(
                config('analytics.api_key'),
                config('analytics.enabled')
            );
        });
    }

    /**
     * Load plugin routes
     */
    protected function loadRoutes(): void
    {
        require __DIR__ . '/routes.php';
    }

    /**
     * Track page view
     */
    public function trackPageView($request): void
    {
        $analytics = app(AnalyticsService::class);
        $analytics->track('page_view', [
            'url' => $request->url(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Track user signup
     */
    public function trackSignup($user): void
    {
        $analytics = app(AnalyticsService::class);
        $analytics->track('signup', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Add analytics widget to dashboard
     */
    public function addAnalyticsWidget(array $widgets): array
    {
        $widgets[] = [
            'name' => 'Analytics Overview',
            'view' => 'analytics::dashboard.widget',
            'size' => 'large',
        ];
        
        return $widgets;
    }
}
```

## Plugin Lifecycle 🔄

### Installation

The `install()` method runs once when the plugin is first installed:

```php
public function install(): void
{
    // Create database tables
    Schema::create('analytics_events', function ($table) {
        $table->id();
        $table->string('event_type');
        $table->json('data');
        $table->timestamp('tracked_at');
        $table->index(['event_type', 'tracked_at']);
    });
    
    // Create default configuration
    DB::table('plugin_settings')->insert([
        'plugin' => 'analytics',
        'key' => 'enabled',
        'value' => true,
    ]);
    
    // Schedule cleanup job
    Schedule::daily()->at('02:00')->call(function () {
        DB::table('analytics_events')
            ->where('tracked_at', '<', now()->subMonths(6))
            ->delete();
    });
}
```

### Activation

Activation enables the plugin after installation:

```php
public function activate(): void
{
    parent::activate();
    
    // Enable tracking
    config(['analytics.enabled' => true]);
    
    // Clear caches
    Cache::tags('analytics')->flush();
    
    // Trigger activation hooks
    do_action('plugin.activated', $this);
}
```

### Deactivation

Deactivation temporarily disables the plugin:

```php
public function deactivate(): void
{
    parent::deactivate();
    
    // Disable tracking
    config(['analytics.enabled' => false]);
    
    // Remove scheduled tasks
    Schedule::clearCallbacks('analytics.*');
    
    // Trigger deactivation hooks
    do_action('plugin.deactivated', $this);
}
```

### Uninstallation

The `uninstall()` method removes all plugin data:

```php
public function uninstall(): void
{
    // Remove database tables
    Schema::dropIfExists('analytics_events');
    
    // Remove configuration
    DB::table('plugin_settings')
        ->where('plugin', 'analytics')
        ->delete();
    
    // Remove uploaded files
    Storage::deleteDirectory('analytics');
    
    // Clear all caches
    Cache::tags(['analytics', 'plugin:analytics'])->flush();
}
```

## Plugin Management 🛠️

### The PluginManager

The `PluginManager` handles plugin discovery, registration, and lifecycle:

```php
use NeoPhp\Plugin\PluginManager;

$manager = app(PluginManager::class);

// Discover plugins
$plugins = $manager->discover();

// Register a plugin
$manager->register(AnalyticsPlugin::class);

// Install a plugin
$manager->install('Analytics');

// Activate a plugin
$manager->activate('Analytics');

// Deactivate a plugin
$manager->deactivate('Analytics');

// Uninstall a plugin
$manager->uninstall('Analytics');

// Get all plugins
$allPlugins = $manager->getAllPlugins();

// Get active plugins
$activePlugins = $manager->getActivePlugins();

// Check plugin status
if ($manager->isInstalled('Analytics')) {
    // Plugin is installed
}

if ($manager->isActive('Analytics')) {
    // Plugin is active
}
```

### CLI Commands

Manage plugins from the command line:

```bash
# List all plugins
php neo plugin:list

# Install a plugin
php neo plugin:install Analytics

# Activate a plugin
php neo plugin:activate Analytics

# Deactivate a plugin
php neo plugin:deactivate Analytics

# Uninstall a plugin
php neo plugin:uninstall Analytics

# Generate a new plugin
php neo make:plugin MyPlugin
```

## Working with Dependencies 🔗

### Declaring Dependencies

Plugins can depend on other plugins:

```php
class AdvancedAnalyticsPlugin extends Plugin
{
    protected string $name = 'AdvancedAnalytics';
    
    /**
     * Required plugins
     */
    protected array $dependencies = [
        'Analytics',
        'Database',
    ];
    
    public function boot(): void
    {
        // Analytics plugin is guaranteed to be loaded
        $analytics = app(AnalyticsService::class);
        
        // Add advanced features
        $this->registerAdvancedTracking($analytics);
    }
}
```

### Checking Dependencies

The PluginManager validates dependencies before installation:

```php
try {
    $manager->install('AdvancedAnalytics');
} catch (\RuntimeException $e) {
    // Error: "Plugin AdvancedAnalytics requires Analytics"
    echo $e->getMessage();
}

// Install dependencies first
$manager->install('Analytics');
$manager->install('AdvancedAnalytics'); // Now succeeds
```

## Plugin Hooks Integration 🪝

### Registering Hooks

Plugins can register hooks to extend application behavior:

```php
class SeoPlugin extends Plugin
{
    protected string $name = 'SEO';
    
    public function boot(): void
    {
        // Add meta tags to pages
        hook_filter('page.head', [$this, 'addMetaTags'], 10, 1);
        
        // Generate sitemap on content changes
        hook_action('post.created', [$this, 'updateSitemap']);
        hook_action('post.updated', [$this, 'updateSitemap']);
        hook_action('post.deleted', [$this, 'updateSitemap']);
        
        // Add structured data
        hook_filter('post.content', [$this, 'addStructuredData'], 10, 2);
        
        // Optimize images
        hook_action('media.uploaded', [$this, 'optimizeImage']);
    }
    
    public function addMetaTags(string $head): string
    {
        $meta = view('seo::meta-tags', [
            'title' => $this->getOptimizedTitle(),
            'description' => $this->getMetaDescription(),
            'keywords' => $this->getMetaKeywords(),
        ])->render();
        
        return $head . $meta;
    }
    
    public function addStructuredData(string $content, $post): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $post->excerpt,
            'author' => [
                '@type' => 'Person',
                'name' => $post->author->name,
            ],
            'datePublished' => $post->published_at->toIso8601String(),
        ];
        
        $script = '<script type="application/ld+json">'
            . json_encode($schema)
            . '</script>';
        
        return $content . $script;
    }
    
    public function updateSitemap(): void
    {
        dispatch(new GenerateSitemap());
    }
}
```

### Plugin-Specific Hooks

Plugins can provide their own hooks for extensibility:

```php
class PaymentPlugin extends Plugin
{
    public function processPayment(Order $order): bool
    {
        // Allow other plugins to validate before payment
        do_action('payment.before_process', $order);
        
        // Process the payment
        $result = $this->gateway->charge($order->total);
        
        // Allow modification of payment result
        $result = apply_filters('payment.result', $result, $order);
        
        if ($result->success) {
            do_action('payment.success', $order, $result);
        } else {
            do_action('payment.failed', $order, $result);
        }
        
        // Always fired regardless of result
        do_action('payment.processed', $order, $result);
        
        return $result->success;
    }
}

// Other plugins can hook into the payment process
hook_action('payment.success', function ($order, $result) {
    // Send receipt email
    Mail::to($order->user)->send(new PaymentReceipt($order));
});

hook_action('payment.failed', function ($order, $result) {
    // Log failed payment
    logger()->warning('Payment failed', [
        'order_id' => $order->id,
        'error' => $result->error,
    ]);
});
```

## Real-World Examples 🌍

### Social Sharing Plugin

```php
<?php

namespace Plugins\SocialSharing;

use NeoPhp\Plugin\Plugin;

class SocialSharingPlugin extends Plugin
{
    protected string $name = 'SocialSharing';
    protected string $version = '1.0.0';
    protected string $description = 'Add social sharing buttons to your content';
    
    protected array $platforms = ['facebook', 'twitter', 'linkedin', 'pinterest'];
    
    public function boot(): void
    {
        // Add sharing buttons to content
        hook_filter('post.content', [$this, 'addSharingButtons'], 100, 2);
        
        // Track shares
        hook_action('social.share', [$this, 'trackShare'], 10, 2);
        
        // Register routes for share tracking
        $this->registerRoutes();
    }
    
    public function addSharingButtons(string $content, $post): string
    {
        if (!config('social_sharing.enabled', true)) {
            return $content;
        }
        
        $buttons = view('social-sharing::buttons', [
            'url' => $post->url(),
            'title' => $post->title,
            'platforms' => config('social_sharing.platforms', $this->platforms),
        ])->render();
        
        $position = config('social_sharing.position', 'bottom');
        
        if ($position === 'top') {
            return $buttons . $content;
        } elseif ($position === 'both') {
            return $buttons . $content . $buttons;
        }
        
        return $content . $buttons;
    }
    
    public function trackShare(string $platform, $post): void
    {
        DB::table('social_shares')->insert([
            'post_id' => $post->id,
            'platform' => $platform,
            'shared_at' => now(),
        ]);
        
        // Update share count on post
        $post->increment($platform . '_shares');
        
        // Clear cache
        Cache::forget("post.{$post->id}.stats");
    }
    
    protected function registerRoutes(): void
    {
        app('router')->post('/social/share', function ($request) {
            $platform = $request->input('platform');
            $postId = $request->input('post_id');
            $post = Post::findOrFail($postId);
            
            do_action('social.share', $platform, $post);
            
            return response()->json(['success' => true]);
        });
    }
}
```

### Cache Optimization Plugin

```php
<?php

namespace Plugins\CacheOptimization;

use NeoPhp\Plugin\Plugin;
use NeoPhp\Cache\CacheManager;

class CacheOptimizationPlugin extends Plugin
{
    protected string $name = 'CacheOptimization';
    protected string $version = '1.0.0';
    protected array $dependencies = ['Cache'];
    
    public function boot(): void
    {
        // Cache database queries
        hook_filter('db.query', [$this, 'cacheQuery'], 10, 2);
        
        // Cache page responses
        hook_filter('response.send', [$this, 'cacheResponse'], 10, 2);
        
        // Invalidate cache on updates
        hook_action('model.saved', [$this, 'invalidateCache']);
        hook_action('model.deleted', [$this, 'invalidateCache']);
        
        // Warm cache for popular content
        hook_action('app.booted', [$this, 'warmCache']);
    }
    
    public function cacheQuery($query, $bindings): mixed
    {
        if (!$this->shouldCacheQuery($query)) {
            return null; // Don't cache
        }
        
        $key = $this->generateQueryCacheKey($query, $bindings);
        
        return Cache::remember($key, 3600, function () use ($query, $bindings) {
            return DB::select($query, $bindings);
        });
    }
    
    public function cacheResponse($response, $request): void
    {
        if (!$this->shouldCacheResponse($request, $response)) {
            return;
        }
        
        $key = 'page:' . md5($request->fullUrl());
        
        Cache::put($key, [
            'content' => $response->getContent(),
            'headers' => $response->headers->all(),
            'status' => $response->getStatusCode(),
        ], 3600);
    }
    
    public function invalidateCache($model): void
    {
        $tags = $this->getCacheTags($model);
        Cache::tags($tags)->flush();
    }
    
    public function warmCache(): void
    {
        if (!app()->runningInConsole()) {
            return;
        }
        
        // Cache popular posts
        $posts = Post::popular()->take(20)->get();
        foreach ($posts as $post) {
            Cache::tags('posts')->put(
                "post:{$post->id}",
                $post,
                now()->addDay()
            );
        }
        
        // Cache user preferences
        $this->warmUserPreferences();
    }
    
    protected function shouldCacheQuery(string $query): bool
    {
        // Don't cache write operations
        $writePatterns = ['INSERT', 'UPDATE', 'DELETE', 'ALTER', 'DROP'];
        
        foreach ($writePatterns as $pattern) {
            if (stripos($query, $pattern) !== false) {
                return false;
            }
        }
        
        return true;
    }
}
```

### Multi-Language Plugin

```php
<?php

namespace Plugins\MultiLanguage;

use NeoPhp\Plugin\Plugin;

class MultiLanguagePlugin extends Plugin
{
    protected string $name = 'MultiLanguage';
    protected string $version = '1.0.0';
    
    protected array $supportedLanguages = ['en', 'es', 'fr', 'de', 'zh'];
    
    public function install(): void
    {
        // Create translations table
        Schema::create('translations', function ($table) {
            $table->id();
            $table->morphs('translatable');
            $table->string('locale', 5);
            $table->string('field');
            $table->text('value');
            $table->unique(['translatable_type', 'translatable_id', 'locale', 'field']);
        });
        
        // Create language settings table
        Schema::create('language_settings', function ($table) {
            $table->string('locale', 5)->primary();
            $table->string('name');
            $table->string('native_name');
            $table->boolean('enabled')->default(true);
            $table->integer('sort_order')->default(0);
        });
    }
    
    public function boot(): void
    {
        // Detect user language
        hook_action('request.received', [$this, 'detectLanguage'], 1);
        
        // Translate model attributes
        hook_filter('model.getAttribute', [$this, 'translateAttribute'], 10, 3);
        
        // Add language switcher to views
        hook_filter('page.header', [$this, 'addLanguageSwitcher']);
        
        // Translate content
        hook_filter('content.display', [$this, 'translateContent'], 10, 2);
        
        // Register routes
        $this->registerLanguageRoutes();
    }
    
    public function detectLanguage($request): void
    {
        // Check URL parameter
        if ($locale = $request->query('lang')) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
            return;
        }
        
        // Check session
        if ($locale = session('locale')) {
            app()->setLocale($locale);
            return;
        }
        
        // Check Accept-Language header
        $preferred = $request->getPreferredLanguage($this->supportedLanguages);
        if ($preferred) {
            app()->setLocale($preferred);
            session(['locale' => $preferred]);
        }
    }
    
    public function translateAttribute($value, $attribute, $model)
    {
        if (!$this->shouldTranslate($model, $attribute)) {
            return $value;
        }
        
        $locale = app()->getLocale();
        
        if ($locale === config('app.fallback_locale')) {
            return $value;
        }
        
        $translation = DB::table('translations')
            ->where('translatable_type', get_class($model))
            ->where('translatable_id', $model->id)
            ->where('field', $attribute)
            ->where('locale', $locale)
            ->value('value');
        
        return $translation ?? $value;
    }
    
    public function addLanguageSwitcher(string $header): string
    {
        $switcher = view('multi-language::switcher', [
            'current' => app()->getLocale(),
            'languages' => $this->getEnabledLanguages(),
        ])->render();
        
        return $header . $switcher;
    }
    
    protected function registerLanguageRoutes(): void
    {
        app('router')->group(['prefix' => 'language'], function ($router) {
            $router->post('/switch', function ($request) {
                $locale = $request->input('locale');
                
                if (in_array($locale, $this->supportedLanguages)) {
                    session(['locale' => $locale]);
                    app()->setLocale($locale);
                }
                
                return redirect()->back();
            });
        });
    }
}
```

## Plugin Configuration ⚙️

### Configuration Files

Each plugin can have its own configuration:

```php
// plugins/analytics/config.php
return [
    'enabled' => env('ANALYTICS_ENABLED', true),
    
    'tracking' => [
        'pageviews' => true,
        'events' => true,
        'user_interactions' => true,
    ],
    
    'api' => [
        'key' => env('ANALYTICS_API_KEY'),
        'endpoint' => env('ANALYTICS_ENDPOINT', 'https://api.analytics.com'),
        'timeout' => 30,
    ],
    
    'retention' => [
        'days' => 90,
        'cleanup_schedule' => '0 2 * * *', // Daily at 2 AM
    ],
];
```

### Loading Configuration

```php
class AnalyticsPlugin extends Plugin
{
    public function boot(): void
    {
        // Merge plugin config with application config
        config(['analytics' => require __DIR__ . '/config.php']);
        
        // Or use a namespaced config
        app('config')->set(
            'plugins.analytics',
            require __DIR__ . '/config.php'
        );
    }
}
```

### Accessing Configuration

```php
// In plugin code
$enabled = config('analytics.enabled');
$apiKey = config('analytics.api.key');

// In application code
if (config('plugins.analytics.enabled')) {
    // Analytics is enabled
}
```

## Best Practices 📋

### 1. Use Namespaces Properly

```php
// Good: Clear namespace structure
namespace Plugins\Analytics\Services;
namespace Plugins\Analytics\Controllers;
namespace Plugins\Analytics\Models;

// Bad: Polluting global namespace
namespace Analytics; // Too generic
```

### 2. Provide Clear Uninstall

```php
public function uninstall(): void
{
    // Remove all traces of the plugin
    $this->removeDatabase();
    $this->removeFiles();
    $this->removeConfiguration();
    $this->unregisterHooks();
    
    // Log uninstallation
    logger()->info("Plugin {$this->name} uninstalled");
}
```

### 3. Handle Errors Gracefully

```php
public function boot(): void
{
    try {
        $this->registerHooks();
        $this->loadServices();
    } catch (\Exception $e) {
        logger()->error("Plugin {$this->name} failed to boot", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        // Don't throw - allow app to continue
    }
}
```

### 4. Version Your Plugins

```php
class Plugin extends BasePlugin
{
    protected string $version = '1.2.3';
    
    public function boot(): void
    {
        $installedVersion = $this->getInstalledVersion();
        
        if (version_compare($installedVersion, $this->version, '<')) {
            $this->runMigrations($installedVersion, $this->version);
        }
    }
    
    protected function runMigrations(string $from, string $to): void
    {
        // Run database migrations, config updates, etc.
    }
}
```

### 5. Document Your Plugin

```php
/**
 * Analytics Plugin
 * 
 * Provides comprehensive analytics tracking for your application.
 * 
 * Features:
 * - Page view tracking
 * - Event tracking
 * - User behavior analytics
 * - Custom dashboards
 * 
 * Hooks Provided:
 * - analytics.before_track - Fires before tracking event
 * - analytics.tracked - Fires after event is tracked
 * - analytics.dashboard_widgets - Filter for dashboard widgets
 * 
 * Dependencies:
 * - Cache plugin (optional but recommended)
 * - Queue plugin (optional but recommended)
 * 
 * @version 1.0.0
 * @author Your Name
 * @link https://docs.example.com/plugins/analytics
 */
class AnalyticsPlugin extends Plugin
{
    // ...
}
```

## Testing Plugins 🧪

```php
<?php

namespace Tests\Plugins;

use Tests\TestCase;
use Plugins\Analytics\AnalyticsPlugin;
use NeoPhp\Plugin\PluginManager;

class AnalyticsPluginTest extends TestCase
{
    protected PluginManager $manager;
    protected AnalyticsPlugin $plugin;
    
    public function setUp(): void
    {
        parent::setUp();
        
        $this->manager = app(PluginManager::class);
        $this->plugin = new AnalyticsPlugin();
    }
    
    public function test_plugin_installs_successfully(): void
    {
        $this->plugin->install();
        
        $this->assertTrue(
            Schema::hasTable('analytics_events')
        );
    }
    
    public function test_plugin_boots_and_registers_hooks(): void
    {
        $this->plugin->boot();
        
        $this->assertTrue(has_action('page.view'));
        $this->assertTrue(has_action('user.registered'));
    }
    
    public function test_plugin_tracks_page_views(): void
    {
        $this->plugin->boot();
        
        $request = $this->createMockRequest('/test');
        do_action('page.view', $request);
        
        $this->assertDatabaseHas('analytics_events', [
            'event_type' => 'page_view',
        ]);
    }
    
    public function test_plugin_uninstalls_cleanly(): void
    {
        $this->plugin->install();
        $this->plugin->uninstall();
        
        $this->assertFalse(
            Schema::hasTable('analytics_events')
        );
    }
}
```

## Related Documentation

- [Hooks System](hooks.md) - Understanding actions and filters
- [Service Providers](service-providers.md) - Registering services
- [Container](../architecture/container.md) - Dependency injection
- [Modules](../getting-started/directory-structure.md#modules) - Module system

## Next Steps

Explore these topics to master plugin development:

1. **[Hooks System](hooks.md)** - Master actions and filters
2. **[Service Providers](service-providers.md)** - Organize your plugin code
3. **[Metadata](metadata.md)** - Use PHP 8 attributes
4. **[Testing](../testing/getting-started.md)** - Test your plugins
