# 🎮 Controller Generator

Generate controller classes for handling HTTP requests in your NeoFramework application. The controller generator supports various controller types including resource, API, and invokable controllers.

## 📋 Table of Contents

- [Basic Usage](#basic-usage)
- [Controller Types](#controller-types)
- [Command Options](#command-options)
- [Generated Code](#generated-code)
- [Advanced Examples](#advanced-examples)
- [Best Practices](#best-practices)

## 🚀 Basic Usage

### Generate Basic Controller

```bash
php neo make:controller UserController
```

**Generated:** `app/Controllers/UserController.php`

```php
<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Neo\Http\Request;
use Neo\Http\Response;

class UserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index(Request $request): Response
    {
        //
    }
}
```

## 🎯 Controller Types

### Resource Controller

Generate a controller with all RESTful methods.

```bash
php neo make:controller PostController --resource
```

**Shorthand:**

```bash
php neo make:controller PostController -r
```

**Generated Code:**

```php
<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Neo\Http\Request;
use Neo\Http\Response;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): Response
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, int $id): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id): Response
    {
        //
    }
}
```

**Route Registration:**

```php
// routes/web.php
Route::resource('posts', PostController::class);
```

**Generated Routes:**

| Method | URI | Action | Route Name |
|--------|-----|--------|------------|
| GET | /posts | index | posts.index |
| GET | /posts/create | create | posts.create |
| POST | /posts | store | posts.store |
| GET | /posts/{id} | show | posts.show |
| GET | /posts/{id}/edit | edit | posts.edit |
| PUT/PATCH | /posts/{id} | update | posts.update |
| DELETE | /posts/{id} | destroy | posts.destroy |

### API Controller

Generate a controller for API endpoints (without create/edit methods).

```bash
php neo make:controller ApiUserController --api
```

**Generated Code:**

```php
<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Neo\Http\Request;
use Neo\Http\JsonResponse;

class ApiUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        //
    }
}
```

**Route Registration:**

```php
// routes/api.php
Route::apiResource('users', ApiUserController::class);
```

### Invokable Controller

Generate a single-action controller.

```bash
php neo make:controller ShowProfileController --invokable
```

**Shorthand:**

```bash
php neo make:controller ShowProfileController -i
```

**Generated Code:**

```php
<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Neo\Http\Request;
use Neo\Http\Response;

class ShowProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        //
    }
}
```

**Route Registration:**

```php
// routes/web.php
Route::get('/profile', ShowProfileController::class);
```

## ⚙️ Command Options

### Available Options

| Option | Shortcut | Description |
|--------|----------|-------------|
| `--resource` | `-r` | Generate resource controller |
| `--api` | | Generate API controller |
| `--invokable` | `-i` | Generate invokable controller |
| `--model=<name>` | `-m` | Generate controller with model injection |
| `--parent=<name>` | `-p` | Generate nested resource controller |
| `--force` | | Overwrite existing controller |

### Model Injection

Generate controller with model type-hinting.

```bash
php neo make:controller PostController --resource --model=Post
```

**Generated Code:**

```php
<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\Post;
use Neo\Http\Request;
use Neo\Http\Response;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $posts = Post::all();
        
        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        
        $post = Post::create($validated);
        
        return redirect()
            ->route('posts.show', $post->id)
            ->with('success', 'Post created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post): Response
    {
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Post $post): Response
    {
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post): Response
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        
        $post->update($validated);
        
        return redirect()
            ->route('posts.show', $post->id)
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Post $post): Response
    {
        $post->delete();
        
        return redirect()
            ->route('posts.index')
            ->with('success', 'Post deleted successfully!');
    }
}
```

### Nested Resources

Generate controller for nested resources.

```bash
php neo make:controller CommentController --resource --parent=Post
```

**Generated Code:**

```php
<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\Post;
use App\Models\Comment;
use Neo\Http\Request;
use Neo\Http\Response;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Post $post): Response
    {
        $comments = $post->comments;
        
        return view('comments.index', compact('post', 'comments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Post $post): Response
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);
        
        $comment = $post->comments()->create($validated);
        
        return redirect()
            ->route('posts.comments.index', $post->id)
            ->with('success', 'Comment added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post, Comment $comment): Response
    {
        return view('comments.show', compact('post', 'comment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post, Comment $comment): Response
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);
        
        $comment->update($validated);
        
        return redirect()
            ->route('posts.comments.show', [$post->id, $comment->id])
            ->with('success', 'Comment updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Post $post, Comment $comment): Response
    {
        $comment->delete();
        
        return redirect()
            ->route('posts.comments.index', $post->id)
            ->with('success', 'Comment deleted successfully!');
    }
}
```

**Route Registration:**

```php
// routes/web.php
Route::resource('posts.comments', CommentController::class);
```

## 📝 Advanced Examples

### Complete CRUD Controller

```php
<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\Product;
use Neo\Http\Request;
use Neo\Http\Response;
use Neo\Http\RedirectResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request): Response
    {
        $products = Product::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->input('category'), function ($query, $category) {
                $query->where('category_id', $category);
            })
            ->paginate(15);
        
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(Request $request): Response
    {
        return view('products.create');
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:2048',
        ]);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products');
        }
        
        $product = Product::create($validated);
        
        return redirect()
            ->route('products.show', $product->id)
            ->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified product.
     */
    public function show(Request $request, Product $product): Response
    {
        $product->load(['category', 'reviews']);
        
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the product.
     */
    public function edit(Request $request, Product $product): Response
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:2048',
        ]);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products');
        }
        
        $product->update($validated);
        
        return redirect()
            ->route('products.show', $product->id)
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        // Delete image if exists
        if ($product->image) {
            Storage::delete($product->image);
        }
        
        $product->delete();
        
        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully!');
    }
}
```

### API Controller with Responses

```php
<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\Post;
use Neo\Http\Request;
use Neo\Http\JsonResponse;

class ApiPostController extends Controller
{
    /**
     * Display a listing of posts.
     */
    public function index(Request $request): JsonResponse
    {
        $posts = Post::with('author')
            ->latest()
            ->paginate($request->input('per_page', 15));
        
        return response()->json([
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
        ]);
        
        $post = Post::create($validated);
        
        return response()->json([
            'message' => 'Post created successfully',
            'data' => $post,
        ], 201);
    }

    /**
     * Display the specified post.
     */
    public function show(Request $request, Post $post): JsonResponse
    {
        $post->load(['author', 'comments', 'tags']);
        
        return response()->json([
            'data' => $post,
        ]);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:draft,published',
        ]);
        
        $post->update($validated);
        
        return response()->json([
            'message' => 'Post updated successfully',
            'data' => $post->fresh(),
        ]);
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Request $request, Post $post): JsonResponse
    {
        $post->delete();
        
        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }
}
```

### Controller with Middleware

```php
<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Neo\Http\Request;
use Neo\Http\Response;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Apply middleware to all methods
        $this->middleware('auth');
        $this->middleware('admin');
        
        // Apply middleware to specific methods
        $this->middleware('verified')->only(['create', 'store']);
        
        // Apply middleware except specific methods
        $this->middleware('throttle:60,1')->except(['index', 'show']);
    }

    /**
     * Display admin dashboard.
     */
    public function dashboard(Request $request): Response
    {
        return view('admin.dashboard');
    }
}
```

### Controller with Service Injection

```php
<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Services\OrderService;
use App\Services\PaymentService;
use Neo\Http\Request;
use Neo\Http\Response;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private OrderService $orderService,
        private PaymentService $paymentService
    ) {}

    /**
     * Process a new order.
     */
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'payment_method' => 'required|string',
        ]);
        
        // Use injected services
        $order = $this->orderService->create($validated['items']);
        $payment = $this->paymentService->process(
            $order,
            $validated['payment_method']
        );
        
        if ($payment->isSuccessful()) {
            return redirect()
                ->route('orders.show', $order->id)
                ->with('success', 'Order placed successfully!');
        }
        
        return back()
            ->with('error', 'Payment failed. Please try again.');
    }
}
```

## 🎯 Best Practices

### Controller Organization

**Single Responsibility:**

```php
// Good: Each controller handles one resource
class UserController extends Controller { }
class PostController extends Controller { }
class CommentController extends Controller { }

// Bad: One controller doing too much
class DashboardController extends Controller {
    public function users() { }
    public function posts() { }
    public function comments() { }
}
```

**Thin Controllers:**

```php
// Good: Logic in services
class PostController extends Controller
{
    public function store(Request $request, PostService $service)
    {
        $validated = $request->validate([...]);
        $post = $service->createPost($validated);
        return redirect()->route('posts.show', $post->id);
    }
}

// Bad: Too much logic in controller
class PostController extends Controller
{
    public function store(Request $request)
    {
        // 50+ lines of business logic here
    }
}
```

### Validation

```php
// Good: Extract to form request
public function store(StorePostRequest $request)
{
    $post = Post::create($request->validated());
    return redirect()->route('posts.show', $post->id);
}

// Good: Simple inline validation
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'content' => 'required',
    ]);
    
    $post = Post::create($validated);
    return redirect()->route('posts.show', $post->id);
}
```

### Response Types

```php
// Web responses
public function index(): Response
{
    return view('posts.index');
}

// JSON responses
public function index(): JsonResponse
{
    return response()->json(['data' => Post::all()]);
}

// Redirect responses
public function store(Request $request): RedirectResponse
{
    // ...
    return redirect()->route('posts.index');
}
```

### Route Model Binding

```php
// Good: Use route model binding
public function show(Post $post): Response
{
    return view('posts.show', compact('post'));
}

// Bad: Manual loading
public function show(int $id): Response
{
    $post = Post::findOrFail($id);
    return view('posts.show', compact('post'));
}
```

## 📚 Related Documentation

- [Routing](../basics/routing.md) - Define routes for controllers
- [Requests](../basics/requests.md) - Handle HTTP requests
- [Responses](../basics/responses.md) - Return HTTP responses
- [Validation](../basics/validation.md) - Validate request data
- [Middleware](../basics/middleware.md) - Apply middleware to controllers

## 🔗 Quick Reference

```bash
# Basic controller
php neo make:controller UserController

# Resource controller
php neo make:controller PostController --resource

# API controller
php neo make:controller ApiUserController --api

# Invokable controller
php neo make:controller ShowDashboard --invokable

# With model injection
php neo make:controller PostController -r -m Post

# Nested resource
php neo make:controller CommentController -r --parent=Post

# Force overwrite
php neo make:controller UserController --force
```
