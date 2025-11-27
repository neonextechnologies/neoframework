# Controllers

## Introduction

Controllers are responsible for handling HTTP requests and returning responses. They group related request handling logic into a single class, keeping your routes file clean and your code organized.

## Basic Controllers

### Creating Controllers

Create a controller using the CLI:

```bash
php neo make:controller UserController
```

This creates `app/Controllers/UserController.php`:

```php
<?php

namespace App\Controllers;

use NeoPhp\Http\Request;
use NeoPhp\Http\Response;

class UserController extends Controller
{
    public function index()
    {
        // List all users
    }
    
    public function show($id)
    {
        // Show single user
    }
}
```

### Defining Controller Routes

```php
use App\Controllers\UserController;

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
```

## Controller Methods

### Index Method

Display a listing of resources:

```php
public function index()
{
    $users = User::paginate(15);
    
    return view('users.index', compact('users'));
}
```

### Show Method

Display a single resource:

```php
public function show(User $user)
{
    return view('users.show', compact('user'));
}
```

### Create Method

Show form to create a resource:

```php
public function create()
{
    return view('users.create');
}
```

### Store Method

Store a newly created resource:

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8',
    ]);
    
    $user = User::create($validated);
    
    return redirect()->route('users.show', $user);
}
```

### Edit Method

Show form to edit a resource:

```php
public function edit(User $user)
{
    return view('users.edit', compact('user'));
}
```

### Update Method

Update a resource:

```php
public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'name' => 'required|max:255',
        'email' => 'required|email|unique:users,email,'.$user->id,
    ]);
    
    $user->update($validated);
    
    return redirect()->route('users.show', $user);
}
```

### Destroy Method

Delete a resource:

```php
public function destroy(User $user)
{
    $user->delete();
    
    return redirect()->route('users.index');
}
```

## Dependency Injection

### Constructor Injection

Inject dependencies through the constructor:

```php
class UserController extends Controller
{
    public function __construct(
        private UserRepository $users,
        private CacheInterface $cache
    ) {}
    
    public function index()
    {
        $users = $this->cache->remember('users.all', 3600, fn() =>
            $this->users->all()
        );
        
        return view('users.index', compact('users'));
    }
}
```

### Method Injection

Inject dependencies into controller methods:

```php
public function store(
    StoreUserRequest $request,
    UserRepository $users,
    EmailService $email
) {
    $user = $users->create($request->validated());
    
    $email->send($user, new WelcomeEmail());
    
    return redirect()->route('users.show', $user);
}
```

## Resource Controllers

### Creating Resource Controllers

```bash
php neo make:controller PostController --resource
```

This creates a controller with all resource methods.

### Using Form Requests

```php
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;

class PostController extends Controller
{
    public function store(StorePostRequest $request)
    {
        $post = Post::create($request->validated());
        
        return new PostResource($post);
    }
    
    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->validated());
        
        return new PostResource($post);
    }
}
```

## API Controllers

### Creating API Controllers

```bash
php neo make:controller Api/UserController --api
```

### Returning JSON Responses

```php
namespace App\Controllers\Api;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(15);
        
        return new UserCollection($users);
    }
    
    public function show(User $user)
    {
        return new UserResource($user);
    }
    
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());
        
        return new UserResource($user);
    }
    
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());
        
        return new UserResource($user);
    }
    
    public function destroy(User $user)
    {
        $user->delete();
        
        return response()->json(null, 204);
    }
}
```

## Controller Middleware

### Assigning Middleware in Constructor

```php
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified')->only('store', 'update');
        $this->middleware('admin')->except('index', 'show');
    }
}
```

### Closure Middleware

```php
public function __construct()
{
    $this->middleware(function ($request, $next) {
        // Custom middleware logic
        return $next($request);
    });
}
```

## Practical Examples

### Example 1: Blog Post Controller

```php
<?php

namespace App\Controllers;

use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use NeoPhp\Http\Request;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
        $this->middleware('can:update,post')->only(['edit', 'update']);
        $this->middleware('can:delete,post')->only('destroy');
    }
    
    public function index(Request $request)
    {
        $query = Post::with('author', 'tags')
            ->published();
        
        if ($request->has('tag')) {
            $query->whereHas('tags', fn($q) => 
                $q->where('slug', $request->tag)
            );
        }
        
        if ($request->has('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        
        $posts = $query->latest()->paginate(15);
        
        return view('posts.index', compact('posts'));
    }
    
    public function show(Post $post)
    {
        $post->load('author', 'comments.author', 'tags');
        $post->increment('views');
        
        $relatedPosts = Post::published()
            ->whereHas('tags', fn($q) => 
                $q->whereIn('id', $post->tags->pluck('id'))
            )
            ->where('id', '!=', $post->id)
            ->limit(4)
            ->get();
        
        return view('posts.show', compact('post', 'relatedPosts'));
    }
    
    public function create()
    {
        $tags = Tag::all();
        
        return view('posts.create', compact('tags'));
    }
    
    public function store(StorePostRequest $request)
    {
        $post = auth()->user()->posts()->create([
            'title' => $request->title,
            'slug' => str_slug($request->title),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'published_at' => $request->publish ? now() : null,
        ]);
        
        $post->tags()->sync($request->tags);
        
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('posts');
            $post->update(['featured_image' => $path]);
        }
        
        return redirect()->route('posts.show', $post)
            ->with('success', 'Post created successfully!');
    }
    
    public function edit(Post $post)
    {
        $tags = Tag::all();
        
        return view('posts.edit', compact('post', 'tags'));
    }
    
    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update([
            'title' => $request->title,
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'published_at' => $request->publish ? ($post->published_at ?? now()) : null,
        ]);
        
        $post->tags()->sync($request->tags);
        
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($post->featured_image) {
                Storage::delete($post->featured_image);
            }
            
            $path = $request->file('featured_image')->store('posts');
            $post->update(['featured_image' => $path]);
        }
        
        return redirect()->route('posts.show', $post)
            ->with('success', 'Post updated successfully!');
    }
    
    public function destroy(Post $post)
    {
        if ($post->featured_image) {
            Storage::delete($post->featured_image);
        }
        
        $post->delete();
        
        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully!');
    }
}
```

### Example 2: E-commerce Product Controller

```php
<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use NeoPhp\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin')->except(['index', 'show']);
    }
    
    public function index(Request $request)
    {
        $query = Product::with('category', 'images')
            ->active();
        
        // Category filter
        if ($request->has('category')) {
            $query->whereHas('category', fn($q) => 
                $q->where('slug', $request->category)
            );
        }
        
        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Sort
        $sort = $request->get('sort', 'newest');
        match($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'name' => $query->orderBy('name', 'asc'),
            default => $query->latest(),
        };
        
        $products = $query->paginate(24);
        $categories = Category::withCount('products')->get();
        
        return view('products.index', compact('products', 'categories'));
    }
    
    public function show(Product $product)
    {
        $product->load('category', 'images', 'reviews.user');
        
        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();
        
        return view('products.show', compact('product', 'relatedProducts'));
    }
    
    public function store(StoreProductRequest $request)
    {
        $product = Product::create([
            'name' => $request->name,
            'slug' => str_slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'compare_price' => $request->compare_price,
            'cost' => $request->cost,
            'sku' => $request->sku,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'is_active' => $request->is_active,
        ]);
        
        // Handle images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products');
                $product->images()->create(['path' => $path]);
            }
        }
        
        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product created successfully!');
    }
    
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'compare_price' => $request->compare_price,
            'cost' => $request->cost,
            'sku' => $request->sku,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'is_active' => $request->is_active,
        ]);
        
        // Handle new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products');
                $product->images()->create(['path' => $path]);
            }
        }
        
        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product updated successfully!');
    }
    
    public function destroy(Product $product)
    {
        // Delete all images
        foreach ($product->images as $image) {
            Storage::delete($image->path);
            $image->delete();
        }
        
        $product->delete();
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully!');
    }
}
```

### Example 3: API Authentication Controller

```php
<?php

namespace App\Controllers\Api;

use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use NeoPhp\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }
    
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }
        
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }
    
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }
    
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
    
    public function refresh(Request $request)
    {
        $request->user()->tokens()->delete();
        
        $token = $request->user()->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'token' => $token
        ]);
    }
}
```

## Best Practices

### 1. Keep Controllers Thin

Move complex logic to services, repositories, or actions:

**Bad:**
```php
public function store(Request $request)
{
    // 100 lines of complex logic
}
```

**Good:**
```php
public function store(StoreUserRequest $request, UserService $service)
{
    $user = $service->createUser($request->validated());
    
    return new UserResource($user);
}
```

### 2. Use Form Requests for Validation

**Bad:**
```php
public function store(Request $request)
{
    $validated = $request->validate([...]);
}
```

**Good:**
```php
public function store(StoreUserRequest $request)
{
    $user = User::create($request->validated());
}
```

### 3. Use Route Model Binding

**Bad:**
```php
public function show($id)
{
    $user = User::findOrFail($id);
}
```

**Good:**
```php
public function show(User $user)
{
    // $user is already loaded
}
```

### 4. Return Consistent Responses

For APIs, always use resources:

```php
public function show(User $user)
{
    return new UserResource($user);
}

public function index()
{
    return new UserCollection(User::paginate());
}
```

### 5. Use Dependency Injection

```php
public function __construct(
    private UserRepository $users,
    private EmailService $email
) {}
```

## Testing Controllers

```php
class UserControllerTest extends TestCase
{
    public function test_can_list_users()
    {
        $users = User::factory()->count(3)->create();
        
        $response = $this->get('/users');
        
        $response->assertStatus(200)
                 ->assertViewHas('users');
    }
    
    public function test_can_create_user()
    {
        $this->actingAs(User::factory()->admin()->create());
        
        $response = $this->post('/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }
}
```

## Next Steps

- [Requests](requests.md) - Working with HTTP requests
- [Responses](responses.md) - Returning HTTP responses
- [Validation](validation.md) - Validating request data
- [Middleware](middleware.md) - Filtering HTTP requests
