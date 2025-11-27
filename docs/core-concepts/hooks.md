# Hooks System 🪝

## Introduction

NeoFramework provides a powerful WordPress-style hooks system that allows you to modify or extend the framework's behavior without modifying core files. The hooks system consists of **actions** (do something at a specific point) and **filters** (modify data before it's used).

The hooks system enables a plugin-based architecture where you can build extensible applications that other developers can customize through well-defined extension points. This is the same system that makes WordPress so extensible, brought to NeoFramework with modern PHP practices.

## Understanding Actions vs Filters

### Actions 🎬

Actions allow you to **execute code** at specific points in your application. Actions don't return anything; they simply perform tasks.

**Use actions when you want to:**
- Send notifications
- Log events
- Update external services
- Trigger side effects

### Filters 🔍

Filters allow you to **modify data** before it's used or displayed. Filters always return a value.

**Use filters when you want to:**
- Modify content before display
- Transform data
- Add or remove items from arrays
- Change configuration values

## Basic Usage

### Adding Actions

```php
use function NeoPhp\Foundation\hook_action;
use function NeoPhp\Foundation\do_action;

// Register an action hook
hook_action('user.registered', function ($user) {
    // Send welcome email
    Mail::to($user->email)->send(new WelcomeEmail($user));
}, $priority = 10, $acceptedArgs = 1);

// Trigger the action
do_action('user.registered', $user);
```

### Adding Filters

```php
use function NeoPhp\Foundation\hook_filter;
use function NeoPhp\Foundation\apply_filters;

// Register a filter hook
hook_filter('user.display_name', function ($name, $user) {
    return strtoupper($name);
}, $priority = 10, $acceptedArgs = 2);

// Apply the filter
$displayName = apply_filters('user.display_name', $user->name, $user);
// Result: "JOHN DOE" (if original was "John Doe")
```

## The HookManager Class

The `NeoPhp\Plugin\HookManager` class manages all hooks in your application:

```php
<?php

use NeoPhp\Plugin\HookManager;

// Add action
HookManager::addAction('app.booted', function () {
    logger()->info('Application booted');
});

// Add filter
HookManager::addFilter('config.app_name', function ($name) {
    return $name . ' v2.0';
});

// Execute action
HookManager::doAction('app.booted');

// Apply filter
$appName = HookManager::applyFilters('config.app_name', 'MyApp');
// Result: "MyApp v2.0"
```

## Working with Priorities ⚡

Hooks execute in order of priority (lowest to highest). Default priority is 10.

```php
// This runs first (priority 5)
hook_action('post.published', function ($post) {
    logger()->info('Post published: ' . $post->title);
}, 5);

// This runs second (priority 10, default)
hook_action('post.published', function ($post) {
    Cache::forget('posts.latest');
});

// This runs third (priority 20)
hook_action('post.published', function ($post) {
    SocialMedia::share($post);
}, 20);

// Trigger in order: log -> cache -> social
do_action('post.published', $post);
```

## Multiple Parameters

### Actions with Multiple Parameters

```php
// Register action with multiple parameters
hook_action('order.created', function ($order, $user, $items) {
    Notification::send($user, new OrderConfirmation($order, $items));
}, 10, 3); // acceptedArgs = 3

// Trigger with multiple arguments
do_action('order.created', $order, $user, $items);
```

### Filters with Multiple Parameters

```php
// Filter with context
hook_filter('product.price', function ($price, $product, $user) {
    if ($user->isPremium()) {
        return $price * 0.9; // 10% discount
    }
    return $price;
}, 10, 3);

// Apply filter with context
$finalPrice = apply_filters('product.price', $product->price, $product, $user);
```

## Helper Functions 🛠️

NeoFramework provides global helper functions for working with hooks:

### hook_action()

Register an action hook:

```php
hook_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
```

### do_action()

Execute an action hook:

```php
do_action(string $hook, ...$args): void
```

### hook_filter()

Register a filter hook:

```php
hook_filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
```

### apply_filters()

Apply a filter hook:

```php
apply_filters(string $hook, mixed $value, ...$args): mixed
```

### has_action()

Check if an action exists:

```php
if (has_action('user.registered')) {
    // Action exists
}
```

### has_filter()

Check if a filter exists:

```php
if (has_filter('content.display')) {
    // Filter exists
}
```

### remove_action()

Remove a specific action:

```php
remove_action(string $hook, callable $callback, int $priority = 10): bool
```

### remove_filter()

Remove a specific filter:

```php
remove_filter(string $hook, callable $callback, int $priority = 10): bool
```

## Common Hook Patterns 🎯

### Application Lifecycle Hooks

```php
// Application initialization
hook_action('app.booting', function ($app) {
    // Before application boots
});

hook_action('app.booted', function ($app) {
    // After application boots
});

// Request handling
hook_action('request.received', function ($request) {
    logger()->debug('Request: ' . $request->path());
});

hook_action('response.sending', function ($response) {
    // Before response sent to client
});

// Application shutdown
hook_action('app.terminating', function () {
    // Clean up resources
});
```

### Model Lifecycle Hooks

```php
use App\Models\Post;

// Before creating
hook_action('model.creating', function ($model) {
    if ($model instanceof Post) {
        $model->slug = str_slug($model->title);
    }
});

// After creating
hook_action('model.created', function ($model) {
    if ($model instanceof Post) {
        Cache::tags('posts')->flush();
    }
});

// Before updating
hook_action('model.updating', function ($model) {
    $model->updated_at = now();
});

// After deleting
hook_action('model.deleted', function ($model) {
    if ($model instanceof Post) {
        Storage::delete($model->image);
    }
});
```

### Content Filters

```php
// Filter post content
hook_filter('post.content', function ($content) {
    // Add reading time
    $words = str_word_count(strip_tags($content));
    $minutes = ceil($words / 200);
    
    return "<div class='reading-time'>{$minutes} min read</div>" . $content;
});

// Filter user display name
hook_filter('user.display_name', function ($name, $user) {
    if ($user->isVerified()) {
        return $name . ' ✓';
    }
    return $name;
}, 10, 2);

// Filter search results
hook_filter('search.results', function ($results, $query) {
    // Boost premium content
    return $results->sortByDesc(function ($item) {
        return $item->isPremium() ? 1 : 0;
    });
}, 10, 2);
```

### Authentication Hooks

```php
// Before login attempt
hook_action('auth.attempting', function ($credentials) {
    logger()->info('Login attempt', ['email' => $credentials['email']]);
});

// After successful login
hook_action('auth.login', function ($user) {
    $user->update(['last_login_at' => now()]);
    
    event(new UserLoggedIn($user));
});

// After logout
hook_action('auth.logout', function ($user) {
    Cache::forget("user.{$user->id}.permissions");
});

// Failed login
hook_action('auth.failed', function ($credentials) {
    RateLimiter::hit('login:' . $credentials['email']);
});
```

## Real-World Examples 🌍

### Building an Extensible Blog System

```php
// In your Blog module or core code

class BlogService
{
    public function publishPost(Post $post): void
    {
        // Allow modification before publishing
        $post = apply_filters('blog.pre_publish', $post);
        
        // Publish the post
        $post->status = 'published';
        $post->published_at = now();
        $post->save();
        
        // Notify that post was published
        do_action('blog.post_published', $post);
        
        // Allow post-processing
        do_action('blog.after_publish', $post);
    }
    
    public function getContent(Post $post): string
    {
        $content = $post->content;
        
        // Apply content filters
        $content = apply_filters('blog.content', $content, $post);
        $content = apply_filters('blog.content.' . $post->type, $content, $post);
        
        return $content;
    }
}

// Plugin developers can extend functionality

// Add automatic excerpts
hook_filter('blog.content', function ($content, $post) {
    if (!$post->excerpt && strlen($content) > 500) {
        $post->excerpt = substr(strip_tags($content), 0, 200) . '...';
        $post->save();
    }
    return $content;
}, 10, 2);

// Add social sharing buttons
hook_filter('blog.content', function ($content, $post) {
    $sharing = view('partials.social-sharing', ['post' => $post]);
    return $content . $sharing;
}, 20, 2);

// Send notifications when published
hook_action('blog.post_published', function ($post) {
    $subscribers = User::where('subscribed', true)->get();
    
    foreach ($subscribers as $subscriber) {
        Mail::to($subscriber)->queue(new NewPostNotification($post));
    }
});

// Update search index
hook_action('blog.post_published', function ($post) {
    SearchIndex::update($post);
});
```

### E-Commerce Order Processing

```php
class OrderService
{
    public function processOrder(Order $order): void
    {
        // Validate order
        do_action('order.validating', $order);
        
        // Calculate totals with filters
        $subtotal = $order->items->sum('total');
        $subtotal = apply_filters('order.subtotal', $subtotal, $order);
        
        $tax = $this->calculateTax($subtotal, $order);
        $tax = apply_filters('order.tax', $tax, $order);
        
        $shipping = $this->calculateShipping($order);
        $shipping = apply_filters('order.shipping', $shipping, $order);
        
        $discount = apply_filters('order.discount', 0, $order);
        
        $total = $subtotal + $tax + $shipping - $discount;
        $total = apply_filters('order.total', $total, $order);
        
        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'discount' => $discount,
            'total' => $total,
        ]);
        
        // Process payment
        do_action('order.before_payment', $order);
        
        $this->processPayment($order);
        
        do_action('order.payment_processed', $order);
        
        // Complete order
        $order->status = 'completed';
        $order->save();
        
        do_action('order.completed', $order);
    }
}

// Add loyalty points discount
hook_filter('order.discount', function ($discount, $order) {
    $user = $order->user;
    $pointsDiscount = $user->loyalty_points * 0.01; // $0.01 per point
    
    return $discount + $pointsDiscount;
}, 10, 2);

// Add referral discount
hook_filter('order.discount', function ($discount, $order) {
    if ($order->referral_code) {
        return $discount + ($order->subtotal * 0.1); // 10% off
    }
    return $discount;
}, 10, 2);

// Send order confirmation
hook_action('order.completed', function ($order) {
    Mail::to($order->user)->send(new OrderConfirmation($order));
});

// Update inventory
hook_action('order.completed', function ($order) {
    foreach ($order->items as $item) {
        $product = $item->product;
        $product->decrement('stock', $item->quantity);
    }
});

// Award loyalty points
hook_action('order.completed', function ($order) {
    $points = floor($order->total / 10); // 1 point per $10
    $order->user->increment('loyalty_points', $points);
});
```

### Plugin System Integration

```php
class PluginLoader
{
    public function loadPlugin(Plugin $plugin): void
    {
        // Allow plugins to register their hooks
        do_action('plugin.loading', $plugin);
        
        // Initialize plugin
        $plugin->boot();
        
        // Register plugin's hooks
        foreach ($plugin->getHooks() as $hook => $callbacks) {
            foreach ($callbacks as $callback) {
                if ($callback['type'] === 'action') {
                    hook_action(
                        $hook,
                        $callback['callback'],
                        $callback['priority']
                    );
                } else {
                    hook_filter(
                        $hook,
                        $callback['callback'],
                        $callback['priority']
                    );
                }
            }
        }
        
        do_action('plugin.loaded', $plugin);
    }
}

// Example plugin using hooks
class SeoPlugin extends Plugin
{
    public function boot(): void
    {
        // Add meta tags to pages
        hook_filter('page.head', function ($head, $page) {
            $meta = $this->generateMetaTags($page);
            return $head . $meta;
        }, 10, 2);
        
        // Generate sitemap on post publish
        hook_action('blog.post_published', function ($post) {
            $this->updateSitemap();
        });
        
        // Add structured data
        hook_filter('post.content', function ($content, $post) {
            $schema = $this->generateSchema($post);
            return $content . $schema;
        }, 10, 2);
    }
}
```

## Advanced Techniques 🚀

### Conditional Hooks

```php
// Only add hook in specific conditions
if (app()->environment('production')) {
    hook_action('exception.occurred', function ($exception) {
        ExternalLogger::logError($exception);
    });
}

// User-specific filters
hook_filter('ui.features', function ($features, $user) {
    if ($user->hasRole('admin')) {
        $features[] = 'advanced_settings';
    }
    return $features;
}, 10, 2);
```

### Removing Hooks Dynamically

```php
// Store callback reference
$callback = function ($content) {
    return strtoupper($content);
};

hook_filter('content.display', $callback);

// Later, remove it
remove_filter('content.display', $callback);

// Remove all hooks for a specific action
remove_all_actions('user.deleted');
```

### Hook Groups

```php
class HookGroup
{
    protected array $hooks = [];
    
    public function add(string $hook, callable $callback, int $priority = 10): void
    {
        $this->hooks[] = compact('hook', 'callback', 'priority');
        hook_action($hook, $callback, $priority);
    }
    
    public function removeAll(): void
    {
        foreach ($this->hooks as $hook) {
            remove_action($hook['hook'], $hook['callback'], $hook['priority']);
        }
    }
}

// Usage
$analyticsHooks = new HookGroup();
$analyticsHooks->add('page.view', [$analytics, 'trackPageView']);
$analyticsHooks->add('user.registered', [$analytics, 'trackSignup']);

// Later, disable all analytics hooks
$analyticsHooks->removeAll();
```

### Debugging Hooks

```php
class HookDebugger
{
    public static function logHooks(string $pattern = '*'): void
    {
        hook_action($pattern, function (...$args) use ($pattern) {
            logger()->debug("Action: {$pattern}", $args);
        }, 1); // Low priority to run first
        
        hook_filter($pattern, function ($value, ...$args) use ($pattern) {
            logger()->debug("Filter: {$pattern}", [
                'before' => $value,
                'args' => $args
            ]);
            return $value;
        }, 1);
    }
}

// Debug all post-related hooks
HookDebugger::logHooks('post.*');
```

## Best Practices 📋

### 1. Use Descriptive Hook Names

```php
// Good: Clear, namespaced, specific
hook_action('blog.post.published', $callback);
hook_filter('ecommerce.order.total', $callback);
hook_action('auth.user.login.success', $callback);

// Bad: Vague, generic
hook_action('publish', $callback);
hook_filter('total', $callback);
hook_action('login', $callback);
```

### 2. Document Your Hooks

```php
/**
 * Fires after a user successfully registers
 * 
 * @param User $user The newly registered user
 * @param array $data Registration data
 */
do_action('user.registered', $user, $data);

/**
 * Filters the post content before display
 * 
 * @param string $content The post content
 * @param Post $post The post object
 * @return string Modified content
 */
$content = apply_filters('post.content', $content, $post);
```

### 3. Provide Context

```php
// Good: Includes relevant context
apply_filters('product.price', $price, $product, $user);
do_action('order.created', $order, $user, $items);

// Bad: Missing context
apply_filters('price', $price);
do_action('created', $order);
```

### 4. Use Priority Thoughtfully

```php
// Validation should run first
hook_action('form.submit', [$validator, 'validate'], 5);

// Normal processing
hook_action('form.submit', [$processor, 'process'], 10);

// Cleanup runs last
hook_action('form.submit', [$cleaner, 'cleanup'], 100);
```

### 5. Handle Errors Gracefully

```php
hook_action('email.sending', function ($email) {
    try {
        ExternalService::notify($email);
    } catch (\Exception $e) {
        logger()->error('External service failed', [
            'error' => $e->getMessage()
        ]);
        // Don't throw - let other hooks continue
    }
});
```

## Testing Hooks 🧪

### Testing Actions

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class HooksTest extends TestCase
{
    public function test_user_registration_triggers_welcome_email(): void
    {
        Mail::fake();
        
        $user = User::factory()->create();
        do_action('user.registered', $user);
        
        Mail::assertSent(WelcomeEmail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
    
    public function test_action_hook_executes_callback(): void
    {
        $executed = false;
        
        hook_action('test.action', function () use (&$executed) {
            $executed = true;
        });
        
        do_action('test.action');
        
        $this->assertTrue($executed);
    }
}
```

### Testing Filters

```php
public function test_content_filter_modifies_text(): void
{
    hook_filter('content.display', function ($content) {
        return strtoupper($content);
    });
    
    $result = apply_filters('content.display', 'hello');
    
    $this->assertEquals('HELLO', $result);
}

public function test_multiple_filters_chain_correctly(): void
{
    hook_filter('price.display', function ($price) {
        return $price * 1.1; // Add 10% tax
    }, 10);
    
    hook_filter('price.display', function ($price) {
        return round($price, 2); // Round to 2 decimals
    }, 20);
    
    $result = apply_filters('price.display', 100);
    
    $this->assertEquals(110.00, $result);
}
```

## Performance Considerations ⚡

### 1. Avoid Heavy Operations in Hooks

```php
// Bad: Heavy database query in filter
hook_filter('user.name', function ($name, $user) {
    $settings = DB::table('user_settings')->where('user_id', $user->id)->first();
    // ...
});

// Good: Cache the results
hook_filter('user.name', function ($name, $user) {
    $settings = Cache::remember("user.{$user->id}.settings", 3600, function () use ($user) {
        return DB::table('user_settings')->where('user_id', $user->id)->first();
    });
    // ...
});
```

### 2. Limit Hook Callbacks

```php
// Check if hook has callbacks before executing
if (has_action('expensive.operation')) {
    do_action('expensive.operation', $data);
}
```

### 3. Use Deferred Hooks for Non-Critical Tasks

```php
// Queue notifications instead of sending immediately
hook_action('order.completed', function ($order) {
    dispatch(new SendOrderNotification($order));
});
```

## Related Documentation

- [Plugins](plugins.md) - Building plugins with hooks
- [Service Providers](service-providers.md) - Registering hooks in providers
- [Events](../advanced/events.md) - Alternative event system
- [Middleware](../basics/middleware.md) - Request/response filtering

## Next Steps

Now that you understand hooks, explore:

1. **[Plugins](plugins.md)** - Build extensible plugins
2. **[Events](../advanced/events.md)** - Learn the event dispatcher
3. **[Service Providers](service-providers.md)** - Organize your hooks
4. **[Metadata](metadata.md)** - Attribute-driven development
