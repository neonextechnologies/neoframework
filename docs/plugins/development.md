# Plugin Development

## Getting Started

This guide walks you through creating a complete plugin for NeoFramework from scratch.

## Creating Your First Plugin

### Initialize Plugin Structure

Use the generator command to scaffold a new plugin:

```bash
php neo make:plugin MyPlugin
```

This creates the following structure:

```
plugins/my-plugin/
├── plugin.json
├── Plugin.php
├── README.md
├── composer.json
├── config/
│   └── config.php
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
└── database/
    ├── migrations/
    └── seeders/
```

### Plugin Manifest

Edit `plugin.json` to define your plugin:

```json
{
    "name": "my-plugin",
    "title": "My Plugin",
    "description": "A sample plugin for NeoFramework",
    "version": "1.0.0",
    "author": "Your Name",
    "email": "you@example.com",
    "homepage": "https://github.com/yourusername/my-plugin",
    "license": "MIT",
    "keywords": ["neo", "plugin"],
    "require": {
        "php": ">=8.1",
        "neoframework/framework": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "MyPlugin\\": "src/"
        }
    },
    "providers": [
        "MyPlugin\\MyPluginServiceProvider"
    ]
}
```

### Main Plugin Class

Create the main plugin class in `Plugin.php`:

```php
<?php

namespace MyPlugin;

use Neo\Plugin\Plugin as BasePlugin;

class Plugin extends BasePlugin
{
    /**
     * Register plugin services
     */
    public function register(): void
    {
        // Register service container bindings
        $this->app->singleton(Services\MyService::class, function($app) {
            return new Services\MyService(
                config('my-plugin.api_key')
            );
        });
        
        // Merge plugin configuration
        $this->mergeConfigFrom(
            $this->path('config/config.php'),
            'my-plugin'
        );
    }
    
    /**
     * Boot plugin
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom($this->path('routes/web.php'));
        
        // Load views
        $this->loadViewsFrom(
            $this->path('resources/views'),
            'my-plugin'
        );
        
        // Load translations
        $this->loadTranslationsFrom(
            $this->path('resources/lang'),
            'my-plugin'
        );
        
        // Load migrations
        $this->loadMigrationsFrom($this->path('database/migrations'));
        
        // Publish assets
        $this->publishes([
            $this->path('resources/assets') => public_path('plugins/my-plugin'),
        ], 'my-plugin-assets');
        
        // Publish configuration
        $this->publishes([
            $this->path('config/config.php') => config_path('my-plugin.php'),
        ], 'my-plugin-config');
        
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\MyCommand::class,
            ]);
        }
    }
}
```

## Building Plugin Features

### Controllers

Create a controller in `src/Controllers/MyController.php`:

```php
<?php

namespace MyPlugin\Controllers;

use Neo\Http\Controller;
use Neo\Http\Request;
use MyPlugin\Services\MyService;

class MyController extends Controller
{
    protected MyService $service;
    
    public function __construct(MyService $service)
    {
        $this->service = $service;
    }
    
    public function index()
    {
        $items = $this->service->getAllItems();
        
        return view('my-plugin::index', [
            'items' => $items
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $item = $this->service->createItem($validated);
        
        return redirect()
            ->route('my-plugin.index')
            ->with('success', 'Item created successfully');
    }
    
    public function show(int $id)
    {
        $item = $this->service->getItem($id);
        
        if (!$item) {
            abort(404);
        }
        
        return view('my-plugin::show', compact('item'));
    }
    
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $item = $this->service->updateItem($id, $validated);
        
        return redirect()
            ->route('my-plugin.show', $id)
            ->with('success', 'Item updated successfully');
    }
    
    public function destroy(int $id)
    {
        $this->service->deleteItem($id);
        
        return redirect()
            ->route('my-plugin.index')
            ->with('success', 'Item deleted successfully');
    }
}
```

### Models

Create a model in `src/Models/Item.php`:

```php
<?php

namespace MyPlugin\Models;

use Neo\Database\Model;
use Neo\Metadata\Attributes\*;

#[Table(name: 'my_plugin_items')]
#[Timestamps]
#[SoftDeletes]
class Item extends Model
{
    #[Column(type: 'integer', autoIncrement: true)]
    #[PrimaryKey]
    public int $id;
    
    #[Column(type: 'string', length: 255)]
    #[Validation(rules: 'required|max:255')]
    #[FormField(type: 'text', label: 'Item Name')]
    public string $name;
    
    #[Column(type: 'text', nullable: true)]
    #[Validation(rules: 'nullable|max:1000')]
    #[FormField(type: 'textarea', label: 'Description', rows: 5)]
    public ?string $description;
    
    #[Column(type: 'boolean')]
    #[Default(true)]
    #[FormField(type: 'checkbox', label: 'Active')]
    public bool $is_active;
    
    #[Column(type: 'datetime')]
    public DateTime $created_at;
    
    #[Column(type: 'datetime')]
    public DateTime $updated_at;
    
    #[Column(type: 'datetime', nullable: true)]
    public ?DateTime $deleted_at;
}
```

### Services

Create a service in `src/Services/MyService.php`:

```php
<?php

namespace MyPlugin\Services;

use MyPlugin\Models\Item;
use Neo\Database\Collection;

class MyService
{
    protected string $apiKey;
    
    public function __construct(string $apiKey = '')
    {
        $this->apiKey = $apiKey;
    }
    
    public function getAllItems(): Collection
    {
        return Item::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    public function getItem(int $id): ?Item
    {
        return Item::find($id);
    }
    
    public function createItem(array $data): Item
    {
        $item = new Item();
        $item->name = $data['name'];
        $item->description = $data['description'] ?? null;
        $item->is_active = $data['is_active'] ?? true;
        $item->save();
        
        // Trigger event
        event(new \MyPlugin\Events\ItemCreated($item));
        
        return $item;
    }
    
    public function updateItem(int $id, array $data): Item
    {
        $item = Item::findOrFail($id);
        $item->name = $data['name'];
        $item->description = $data['description'] ?? null;
        
        if (isset($data['is_active'])) {
            $item->is_active = $data['is_active'];
        }
        
        $item->save();
        
        // Trigger event
        event(new \MyPlugin\Events\ItemUpdated($item));
        
        return $item;
    }
    
    public function deleteItem(int $id): bool
    {
        $item = Item::findOrFail($id);
        $deleted = $item->delete();
        
        if ($deleted) {
            event(new \MyPlugin\Events\ItemDeleted($item));
        }
        
        return $deleted;
    }
    
    /**
     * Call external API (example)
     */
    public function syncWithApi(): array
    {
        $client = new \GuzzleHttp\Client();
        
        $response = $client->get('https://api.example.com/items', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
            ]
        ]);
        
        return json_decode($response->getBody(), true);
    }
}
```

### Routes

Define routes in `routes/web.php`:

```php
<?php

use MyPlugin\Controllers\MyController;
use Neo\Routing\Router;

Router::group(['prefix' => 'my-plugin', 'as' => 'my-plugin.'], function() {
    
    // List items
    Router::get('/', [MyController::class, 'index'])
        ->name('index');
    
    // Create item form
    Router::get('/create', [MyController::class, 'create'])
        ->name('create');
    
    // Store item
    Router::post('/', [MyController::class, 'store'])
        ->name('store');
    
    // Show item
    Router::get('/{id}', [MyController::class, 'show'])
        ->name('show');
    
    // Edit item form
    Router::get('/{id}/edit', [MyController::class, 'edit'])
        ->name('edit');
    
    // Update item
    Router::put('/{id}', [MyController::class, 'update'])
        ->name('update');
    
    // Delete item
    Router::delete('/{id}', [MyController::class, 'destroy'])
        ->name('destroy');
});

// API routes
Router::group(['prefix' => 'api/my-plugin', 'as' => 'api.my-plugin.'], function() {
    
    Router::get('/items', [MyController::class, 'apiIndex'])
        ->name('items.index');
    
    Router::get('/items/{id}', [MyController::class, 'apiShow'])
        ->name('items.show');
});
```

### Middleware

Create middleware in `src/Middleware/MyMiddleware.php`:

```php
<?php

namespace MyPlugin\Middleware;

use Closure;
use Neo\Http\Request;
use Neo\Http\Response;

class MyMiddleware
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Before request
        if (!config('my-plugin.enabled')) {
            abort(503, 'Plugin is disabled');
        }
        
        // Log request
        logger()->info('MyPlugin middleware', [
            'url' => $request->url(),
            'method' => $request->method(),
        ]);
        
        $response = $next($request);
        
        // After request
        $response->header('X-My-Plugin', 'Active');
        
        return $response;
    }
}
```

Register middleware in plugin class:

```php
<?php

public function boot(): void
{
    // Register middleware
    $this->app['router']->middleware('my-plugin', \MyPlugin\Middleware\MyMiddleware::class);
    
    // Apply to routes
    $this->app['router']->group(['middleware' => 'my-plugin'], function() {
        $this->loadRoutesFrom($this->path('routes/web.php'));
    });
}
```

### Views

Create views in `resources/views/`:

**index.blade.php**:
```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('my-plugin::messages.items') }}</h1>
    
    <a href="{{ route('my-plugin.create') }}" class="btn btn-primary">
        {{ __('my-plugin::messages.create_item') }}
    </a>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('my-plugin::messages.name') }}</th>
                <th>{{ __('my-plugin::messages.description') }}</th>
                <th>{{ __('my-plugin::messages.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->description }}</td>
                <td>
                    <a href="{{ route('my-plugin.show', $item->id) }}">View</a>
                    <a href="{{ route('my-plugin.edit', $item->id) }}">Edit</a>
                    <form action="{{ route('my-plugin.destroy', $item->id) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
```

### Migrations

Create migration in `database/migrations/2024_01_01_000000_create_items_table.php`:

```php
<?php

use Neo\Database\Migration;
use Neo\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema->create('my_plugin_items', function(Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('is_active');
            $table->index('created_at');
        });
    }
    
    public function down(): void
    {
        $this->schema->dropIfExists('my_plugin_items');
    }
};
```

Run migrations:

```bash
php neo migrate
```

### Commands

Create a command in `src/Commands/MyCommand.php`:

```php
<?php

namespace MyPlugin\Commands;

use Neo\Console\Command;
use MyPlugin\Services\MyService;

class MyCommand extends Command
{
    protected string $signature = 'my-plugin:sync';
    protected string $description = 'Sync data with external API';
    
    public function handle(MyService $service): int
    {
        $this->info('Starting sync...');
        
        try {
            $data = $service->syncWithApi();
            
            $this->info('Synced ' . count($data) . ' items');
            $this->line('Sync completed successfully');
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
```

Register command in plugin class:

```php
<?php

public function boot(): void
{
    if ($this->app->runningInConsole()) {
        $this->commands([
            Commands\MyCommand::class,
        ]);
    }
}
```

## Advanced Features

### Events

Create events in `src/Events/`:

```php
<?php

namespace MyPlugin\Events;

use MyPlugin\Models\Item;
use Neo\Events\Event;

class ItemCreated extends Event
{
    public Item $item;
    
    public function __construct(Item $item)
    {
        $this->item = $item;
    }
}
```

### Event Listeners

Create listener in `src/Listeners/`:

```php
<?php

namespace MyPlugin\Listeners;

use MyPlugin\Events\ItemCreated;
use Neo\Logging\Log;

class LogItemCreation
{
    public function handle(ItemCreated $event): void
    {
        Log::info('Item created', [
            'item_id' => $event->item->id,
            'name' => $event->item->name,
        ]);
        
        // Send notification
        // Update cache
        // Trigger webhook
    }
}
```

Register listeners:

```php
<?php

use Neo\Events\Event;

public function boot(): void
{
    Event::listen(
        \MyPlugin\Events\ItemCreated::class,
        \MyPlugin\Listeners\LogItemCreation::class
    );
}
```

### API Integration

Create API client:

```php
<?php

namespace MyPlugin\Services;

use GuzzleHttp\Client;

class ApiClient
{
    protected Client $client;
    protected string $apiKey;
    
    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'timeout' => 30,
        ]);
        $this->apiKey = $apiKey;
    }
    
    public function get(string $endpoint, array $params = []): array
    {
        $response = $this->client->get($endpoint, [
            'headers' => $this->getHeaders(),
            'query' => $params,
        ]);
        
        return json_decode($response->getBody(), true);
    }
    
    public function post(string $endpoint, array $data): array
    {
        $response = $this->client->post($endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $data,
        ]);
        
        return json_decode($response->getBody(), true);
    }
    
    protected function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
}
```

### Testing

Create tests in `tests/`:

```php
<?php

namespace MyPlugin\Tests\Unit;

use Tests\TestCase;
use MyPlugin\Services\MyService;
use MyPlugin\Models\Item;

class MyServiceTest extends TestCase
{
    protected MyService $service;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->service = new MyService('test-api-key');
    }
    
    public function test_creates_item()
    {
        $data = [
            'name' => 'Test Item',
            'description' => 'Test Description',
        ];
        
        $item = $this->service->createItem($data);
        
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals('Test Item', $item->name);
        $this->assertDatabaseHas('my_plugin_items', [
            'name' => 'Test Item',
        ]);
    }
    
    public function test_updates_item()
    {
        $item = Item::factory()->create();
        
        $updated = $this->service->updateItem($item->id, [
            'name' => 'Updated Name',
        ]);
        
        $this->assertEquals('Updated Name', $updated->name);
    }
}
```

## Configuration

Create configuration file in `config/config.php`:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Plugin Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable the plugin
    |
    */
    'enabled' => env('MY_PLUGIN_ENABLED', true),
    
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | External API settings
    |
    */
    'api' => [
        'base_url' => env('MY_PLUGIN_API_URL', 'https://api.example.com'),
        'key' => env('MY_PLUGIN_API_KEY', ''),
        'timeout' => env('MY_PLUGIN_API_TIMEOUT', 30),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable/disable specific features
    |
    */
    'features' => [
        'sync' => true,
        'webhooks' => false,
        'notifications' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Plugin cache configuration
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'prefix' => 'my_plugin:',
    ],
];
```

## Best Practices

1. **Use Dependency Injection**: Inject dependencies in constructors
2. **Follow PSR Standards**: Adhere to PSR-4 autoloading and PSR-12 coding style
3. **Write Tests**: Maintain good test coverage
4. **Document Code**: Add PHPDoc blocks to classes and methods
5. **Handle Errors**: Use try-catch blocks and proper error handling
6. **Validate Input**: Always validate and sanitize user input
7. **Use Events**: Leverage events for extensibility
8. **Cache Wisely**: Cache expensive operations
9. **Optimize Queries**: Use eager loading and query optimization
10. **Version Properly**: Follow semantic versioning

## Next Steps

- Explore [Plugin API Reference](plugin-api.md)
- Learn about [Plugin Distribution](distribution.md)
- Return to [Plugin Introduction](introduction.md)
