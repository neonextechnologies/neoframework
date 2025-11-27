# Caching

## Introduction

NeoFramework provides an expressive, unified API for various caching backends. The cache configuration is located at `config/cache.php`, where you can specify which cache driver you would like to be used by default throughout your application.

## Configuration

The cache configuration file is located at `config/cache.php`:

```php
return [
    'default' => env('CACHE_DRIVER', 'file'),

    'stores' => [
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('cache'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],

        'memcached' => [
            'driver' => 'memcached',
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],
    ],

    'prefix' => env('CACHE_PREFIX', 'neo_cache'),
];
```

## Basic Usage

### Storing Items

```php
use NeoPhp\Support\Facades\Cache;

// Store indefinitely
Cache::put('key', 'value');

// Store for specific time (seconds)
Cache::put('key', 'value', 3600);

// Store using DateTime
Cache::put('key', 'value', now()->addMinutes(10));

// Store if not present
Cache::add('key', 'value', 3600);
```

### Retrieving Items

```php
// Get value
$value = Cache::get('key');

// Get with default
$value = Cache::get('key', 'default');

// Get with closure default
$value = Cache::get('key', function () {
    return DB::table('users')->count();
});

// Get and delete
$value = Cache::pull('key');
```

### Checking Existence

```php
if (Cache::has('key')) {
    // Key exists
}

// Check if missing
if (Cache::missing('key')) {
    // Key doesn't exist
}
```

### Removing Items

```php
// Remove specific key
Cache::forget('key');

// Clear all cache
Cache::flush();
```

### Atomic Operations

```php
// Increment
Cache::increment('counter');
Cache::increment('counter', 5);

// Decrement
Cache::decrement('counter');
Cache::decrement('counter', 5);
```

## Cache Keys

### Remember

Retrieve or store:

```php
$value = Cache::remember('users', 3600, function () {
    return DB::table('users')->get();
});

// Remember forever
$value = Cache::rememberForever('settings', function () {
    return DB::table('settings')->pluck('value', 'key');
});
```

### Tags

Group related cache items:

```php
// Store with tags
Cache::tags(['people', 'artists'])->put('John', $john, 3600);
Cache::tags(['people', 'authors'])->put('Anne', $anne, 3600);

// Retrieve with tags
$john = Cache::tags(['people', 'artists'])->get('John');

// Flush tagged items
Cache::tags(['people', 'authors'])->flush();
Cache::tags(['people'])->flush(); // Flushes both John and Anne
```

## Cache Drivers

### File Driver

Stores cache in files:

```php
'file' => [
    'driver' => 'file',
    'path' => storage_path('cache'),
],
```

### Array Driver

Stores cache in memory (useful for testing):

```php
'array' => [
    'driver' => 'array',
    'serialize' => false,
],
```

### Redis Driver

```php
'redis' => [
    'driver' => 'redis',
    'connection' => 'cache',
],
```

Environment configuration:

```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Memcached Driver

```php
'memcached' => [
    'driver' => 'memcached',
    'servers' => [
        [
            'host' => env('MEMCACHED_HOST', '127.0.0.1'),
            'port' => env('MEMCACHED_PORT', 11211),
            'weight' => 100,
        ],
    ],
],
```

## Multiple Cache Stores

```php
// Use specific store
$value = Cache::store('redis')->get('key');

// Store in specific store
Cache::store('redis')->put('key', 'value', 3600);

// Use array cache for testing
Cache::store('array')->put('test-key', 'value');
```

## Cache Events

### Listening to Cache Events

```php
use NeoPhp\Support\Facades\Event;

Event::listen('cache.hit', function ($key, $value) {
    // Cache hit
});

Event::listen('cache.missed', function ($key) {
    // Cache miss
});

Event::listen('cache.written', function ($key, $value, $minutes) {
    // Cache written
});

Event::listen('cache.deleted', function ($key) {
    // Cache deleted
});
```

## Cache Locks

### Acquiring Locks

```php
use NeoPhp\Support\Facades\Cache;

$lock = Cache::lock('process-orders', 10);

if ($lock->get()) {
    // Lock acquired for 10 seconds
    try {
        // Process orders
    } finally {
        $lock->release();
    }
}
```

### Block Until Lock is Available

```php
Cache::lock('process-orders')
    ->block(5, function () {
        // Lock acquired after waiting maximum of 5 seconds
    });
```

## Practical Examples

### Example 1: Caching Database Queries

```php
<?php

namespace App\Repositories;

use App\Models\Product;
use NeoPhp\Support\Facades\Cache;

class ProductRepository
{
    public function getAllActive()
    {
        return Cache::remember('products.active', 3600, function () {
            return Product::where('active', true)
                ->with('category')
                ->orderBy('name')
                ->get();
        });
    }

    public function find($id)
    {
        return Cache::remember("products.{$id}", 3600, function () use ($id) {
            return Product::with(['category', 'images', 'reviews'])
                ->findOrFail($id);
        });
    }

    public function getFeatured()
    {
        return Cache::tags(['products', 'featured'])
            ->remember('products.featured', 1800, function () {
                return Product::where('featured', true)
                    ->limit(10)
                    ->get();
            });
    }

    public function clearCache($productId = null)
    {
        if ($productId) {
            Cache::forget("products.{$productId}");
        } else {
            Cache::tags(['products'])->flush();
        }
    }
}

// Usage in controller
class ProductController extends Controller
{
    protected $products;

    public function __construct(ProductRepository $products)
    {
        $this->products = $products;
    }

    public function index()
    {
        $products = $this->products->getAllActive();
        return view('products.index', compact('products'));
    }

    public function show($id)
    {
        $product = $this->products->find($id);
        return view('products.show', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());

        // Clear cache for this product
        $this->products->clearCache($id);

        return redirect()->route('products.show', $id);
    }
}
```

### Example 2: Caching API Responses

```php
<?php

namespace App\Services;

use NeoPhp\Support\Facades\Cache;
use NeoPhp\Support\Facades\Http;

class WeatherService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openweathermap.org/data/2.5';

    public function __construct()
    {
        $this->apiKey = config('services.weather.api_key');
    }

    public function getCurrentWeather($city)
    {
        $cacheKey = "weather.{$city}.current";

        return Cache::remember($cacheKey, 600, function () use ($city) {
            $response = Http::get("{$this->baseUrl}/weather", [
                'q' => $city,
                'appid' => $this->apiKey,
                'units' => 'metric',
            ]);

            if ($response->failed()) {
                throw new \Exception('Failed to fetch weather data');
            }

            return $response->json();
        });
    }

    public function getForecast($city, $days = 5)
    {
        $cacheKey = "weather.{$city}.forecast.{$days}";

        return Cache::remember($cacheKey, 1800, function () use ($city, $days) {
            $response = Http::get("{$this->baseUrl}/forecast", [
                'q' => $city,
                'appid' => $this->apiKey,
                'units' => 'metric',
                'cnt' => $days * 8, // 8 forecasts per day (every 3 hours)
            ]);

            return $response->json();
        });
    }

    public function getHistoricalData($city, $date)
    {
        $cacheKey = "weather.{$city}.history.{$date}";

        // Historical data rarely changes, cache for 1 day
        return Cache::remember($cacheKey, 86400, function () use ($city, $date) {
            $timestamp = strtotime($date);

            $response = Http::get("{$this->baseUrl}/timemachine", [
                'q' => $city,
                'appid' => $this->apiKey,
                'dt' => $timestamp,
            ]);

            return $response->json();
        });
    }
}

// Usage in controller
class WeatherController extends Controller
{
    protected $weather;

    public function __construct(WeatherService $weather)
    {
        $this->weather = $weather;
    }

    public function current(Request $request)
    {
        $city = $request->input('city', 'London');

        try {
            $weather = $this->weather->getCurrentWeather($city);
            return response()->json($weather);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function forecast(Request $request)
    {
        $city = $request->input('city', 'London');
        $days = $request->input('days', 5);

        $forecast = $this->weather->getForecast($city, $days);
        return response()->json($forecast);
    }
}
```

### Example 3: User Dashboard Statistics with Cache

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use NeoPhp\Support\Facades\Cache;
use NeoPhp\Support\Facades\DB;

class DashboardService
{
    public function getUserStats($userId)
    {
        return Cache::tags(['users', "user.{$userId}"])
            ->remember("user.{$userId}.stats", 3600, function () use ($userId) {
                $user = User::findOrFail($userId);

                return [
                    'total_orders' => $user->orders()->count(),
                    'total_spent' => $user->orders()->sum('total'),
                    'average_order' => $user->orders()->avg('total'),
                    'favorite_category' => $this->getFavoriteCategory($userId),
                    'last_order_date' => $user->orders()->latest()->first()?->created_at,
                ];
            });
    }

    public function getRecentOrders($userId, $limit = 10)
    {
        return Cache::tags(['users', "user.{$userId}", 'orders'])
            ->remember("user.{$userId}.recent_orders", 1800, function () use ($userId, $limit) {
                return Order::where('user_id', $userId)
                    ->with(['items.product'])
                    ->latest()
                    ->limit($limit)
                    ->get();
            });
    }

    public function getRecommendedProducts($userId, $limit = 5)
    {
        return Cache::remember("user.{$userId}.recommended", 7200, function () use ($userId, $limit) {
            // Get user's order history
            $orderedProductIds = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.user_id', $userId)
                ->pluck('order_items.product_id')
                ->unique();

            // Get categories from ordered products
            $categories = Product::whereIn('id', $orderedProductIds)
                ->pluck('category_id')
                ->unique();

            // Recommend products from same categories
            return Product::whereIn('category_id', $categories)
                ->whereNotIn('id', $orderedProductIds)
                ->where('active', true)
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        });
    }

    public function clearUserCache($userId)
    {
        Cache::tags(["user.{$userId}"])->flush();
    }

    protected function getFavoriteCategory($userId)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.user_id', $userId)
            ->select('categories.name', DB::raw('COUNT(*) as count'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('count')
            ->first()?->name;
    }
}

// Usage in controller
class DashboardController extends Controller
{
    protected $dashboard;

    public function __construct(DashboardService $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    public function index()
    {
        $userId = auth()->id();

        $stats = $this->dashboard->getUserStats($userId);
        $recentOrders = $this->dashboard->getRecentOrders($userId);
        $recommended = $this->dashboard->getRecommendedProducts($userId);

        return view('dashboard', compact('stats', 'recentOrders', 'recommended'));
    }
}

// Clear cache when order is created
class OrderObserver
{
    public function created(Order $order)
    {
        app(DashboardService::class)->clearUserCache($order->user_id);
    }
}
```

### Example 4: Cache Lock for Processing

```php
<?php

namespace App\Services;

use NeoPhp\Support\Facades\Cache;
use NeoPhp\Support\Facades\Log;

class ReportGenerationService
{
    public function generateMonthlyReport($month, $year)
    {
        $lockKey = "report.monthly.{$year}.{$month}";
        $cacheKey = "report.monthly.{$year}.{$month}.result";

        // Check if report is already cached
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Acquire lock (prevents duplicate processing)
        $lock = Cache::lock($lockKey, 600); // 10 minutes

        if (!$lock->get()) {
            // Another process is generating the report
            // Wait for it to finish
            return $lock->block(300, function () use ($cacheKey) {
                return Cache::get($cacheKey);
            });
        }

        try {
            Log::info("Generating monthly report for {$month}/{$year}");

            // Generate report (expensive operation)
            $report = $this->processReport($month, $year);

            // Cache the result for 30 days
            Cache::put($cacheKey, $report, 2592000);

            return $report;
        } finally {
            $lock->release();
        }
    }

    protected function processReport($month, $year)
    {
        // Simulate expensive processing
        sleep(5);

        return [
            'month' => $month,
            'year' => $year,
            'total_sales' => rand(10000, 50000),
            'total_orders' => rand(100, 500),
            'new_customers' => rand(20, 100),
        ];
    }
}

// Usage
$service = new ReportGenerationService();
$report = $service->generateMonthlyReport(10, 2024);
```

## Best Practices

### 1. Use Appropriate Cache Times

```php
// Short-lived (5 minutes)
Cache::put('popular-products', $products, 300);

// Medium (1 hour)
Cache::put('user-settings', $settings, 3600);

// Long-lived (24 hours)
Cache::put('site-config', $config, 86400);
```

### 2. Use Cache Tags for Related Data

```php
Cache::tags(['users', 'profiles'])->put($key, $value, 3600);
Cache::tags(['users'])->flush(); // Clear all user-related cache
```

### 3. Always Have Fallbacks

```php
$value = Cache::get('key', function () {
    return DB::table('settings')->first();
});
```

### 4. Clear Cache When Data Changes

```php
public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);
    $product->update($request->all());

    // Clear related cache
    Cache::forget("product.{$id}");
    Cache::tags(['products'])->flush();
}
```

### 5. Use Cache Locks for Race Conditions

```php
$lock = Cache::lock('process-payments', 10);

if ($lock->get()) {
    try {
        // Process payments
    } finally {
        $lock->release();
    }
}
```

### 6. Monitor Cache Performance

```php
// Log cache hits and misses
Event::listen('cache.hit', function ($key) {
    Log::info("Cache hit: {$key}");
});

Event::listen('cache.missed', function ($key) {
    Log::info("Cache miss: {$key}");
});
```

## Testing

### Cache Fake

```php
use NeoPhp\Support\Facades\Cache;

public function test_caches_products()
{
    Cache::shouldReceive('remember')
        ->once()
        ->with('products', 3600, \Closure::class)
        ->andReturn(collect(['product1', 'product2']));

    $products = $this->repository->getAllProducts();

    $this->assertCount(2, $products);
}
```

## Next Steps

- [Queue](queue.md) - Background job processing
- [Events](events.md) - Event system
- [Logging](logging.md) - Application logging
- [Performance](../core-concepts/performance.md) - Performance optimization
