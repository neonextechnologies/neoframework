# Quick Start Tutorial

## Introduction

This tutorial will guide you through building your first application with NeoFramework. We'll create a simple blog system with posts, comments, and user authentication.

## What We'll Build

- User authentication (login/register)
- Create, read, update, delete posts
- Comment on posts
- REST API endpoints
- Testing

## Prerequisites

Make sure you have NeoFramework installed. If not, see the [Installation Guide](installation.md).

## Step 1: Create the Database

Create a new MySQL database:

```sql
CREATE DATABASE blog_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Update your `.env` file:

```env
DB_DATABASE=blog_app
DB_USERNAME=root
DB_PASSWORD=your_password
```

## Step 2: Create Models and Migrations

### User Model

The User model is already created. Let's create Post and Comment models:

```bash
php neo make:model Post -m -c -f
php neo make:model Comment -m -c -f
```

This creates:
- Models: `app/Models/Post.php`, `app/Models/Comment.php`
- Migrations: `database/migrations/*_create_posts_table.php`
- Controllers: `app/Controllers/PostController.php`
- Factories: `database/factories/PostFactory.php`

### Define Migrations

**database/migrations/xxx_create_posts_table.php:**
```php
<?php

use NeoPhp\Database\Migration;
use NeoPhp\Database\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function($table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('body');
            $table->boolean('published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

**database/migrations/xxx_create_comments_table.php:**
```php
<?php

use NeoPhp\Database\Migration;
use NeoPhp\Database\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function($table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
```

Run the migrations:

```bash
php neo migrate
```

## Step 3: Define Model Relationships

### Post Model

**app/Models/Post.php:**
```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;
use NeoPhp\Testing\HasFactory;
use NeoPhp\Database\Concerns\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'body',
        'published',
        'published_at',
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected $hidden = [];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('published', true)
                    ->whereNotNull('published_at');
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    // Mutator
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = \str_replace(' ', '-', strtolower($value));
    }
}
```

### Comment Model

**app/Models/Comment.php:**
```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;
use NeoPhp\Testing\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'user_id', 'body'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Update User Model

**app/Models/User.php:**
```php
public function posts()
{
    return $this->hasMany(Post::class);
}

public function comments()
{
    return $this->hasMany(Comment::class);
}
```

## Step 4: Create Form Requests

```bash
php neo make:request StorePostRequest
php neo make:request UpdatePostRequest
php neo make:request StoreCommentRequest
```

**app/Http/Requests/StorePostRequest.php:**
```php
<?php

namespace App\Http\Requests;

use NeoPhp\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'published' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a post title',
            'body.required' => 'Please enter post content',
        ];
    }
}
```

## Step 5: Create API Resources

```bash
php neo make:resource PostResource
php neo make:resource CommentResource
```

**app/Http/Resources/PostResource.php:**
```php
<?php

namespace App\Http\Resources;

use NeoPhp\Http\Resources\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => substr($this->body, 0, 100) . '...',
            'body' => $this->body,
            'published' => $this->published,
            'author' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'comments_count' => $this->comments()->count(),
            'comments' => CommentResource::collection(
                $this->whenLoaded('comments')
            ),
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
```

## Step 6: Create Controllers

**app/Controllers/PostController.php:**
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
    public function index()
    {
        $posts = Post::with('user')
            ->published()
            ->latest()
            ->paginate(15);

        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request)
    {
        $post = auth()->user()->posts()->create([
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'published' => $request->input('published', false),
            'published_at' => $request->input('published') ? now() : null,
        ]);

        return new PostResource($post);
    }

    public function show($id)
    {
        $post = Post::with(['user', 'comments.user'])->findOrFail($id);

        return new PostResource($post);
    }

    public function update(UpdatePostRequest $request, $id)
    {
        $post = Post::findOrFail($id);

        $this->authorize('update', $post);

        $post->update($request->validated());

        return new PostResource($post);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        $this->authorize('delete', $post);

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
```

## Step 7: Create Policies

```bash
php neo make:policy PostPolicy --model=Post
```

**app/Policies/PostPolicy.php:**
```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Post;
use NeoPhp\Auth\Access\Policy;

class PostPolicy extends Policy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
```

Register the policy in `app/Providers/AppServiceProvider.php`:

```php
use App\Models\Post;
use App\Policies\PostPolicy;

public function boot()
{
    Gate::policy(Post::class, PostPolicy::class);
}
```

## Step 8: Define Routes

**routes/web.php:**
```php
<?php

use App\Controllers\PostController;
use App\Controllers\CommentController;

// API Routes
Route::group(['prefix' => 'api', 'middleware' => 'auth'], function() {
    // Posts
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);

    // Comments
    Route::post('/posts/{id}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
});

// Public Routes
Route::get('/api/posts', [PostController::class, 'index']);
Route::get('/api/posts/{id}', [PostController::class, 'show']);
```

## Step 9: Create Factories for Testing

**database/factories/PostFactory.php:**
```php
<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use NeoPhp\Testing\Factory;

class PostFactory extends Factory
{
    protected string $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->randomString(50),
            'body' => $this->randomString(500),
            'published' => $this->randomBoolean(),
            'published_at' => $this->randomDate('-1 month', 'now'),
        ];
    }

    public function published(): static
    {
        return $this->state([
            'published' => true,
            'published_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state([
            'published' => false,
            'published_at' => null,
        ]);
    }
}
```

## Step 10: Write Tests

```bash
php neo make:test PostTest
```

**tests/Feature/PostTest.php:**
```php
<?php

use NeoPhp\Testing\TestCase;
use App\Models\User;
use App\Models\Post;

class PostTest extends TestCase
{
    public function test_user_can_create_post()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->json('POST', '/api/posts', [
                'title' => 'My First Post',
                'body' => 'This is the content of my post.',
                'published' => true,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('posts', [
            'title' => 'My First Post',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_update_own_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user)
            ->json('PUT', "/api/posts/{$post->id}", [
                'title' => 'Updated Title',
                'body' => $post->body,
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_cannot_update_others_post()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $post = Post::factory()->for($user1)->create();

        $response = $this->actingAs($user2)
            ->json('PUT', "/api/posts/{$post->id}", [
                'title' => 'Hacked Title',
            ]);

        $response->assertForbidden();
    }

    public function test_can_list_published_posts()
    {
        Post::factory()->published()->count(5)->create();
        Post::factory()->draft()->count(3)->create();

        $response = $this->json('GET', '/api/posts');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }
}
```

Run the tests:

```bash
php neo test
```

## Step 11: Test the API

### Create a Post

```bash
curl -X POST http://localhost:8000/api/posts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "My First Post",
    "body": "This is my first blog post!",
    "published": true
  }'
```

### Get All Posts

```bash
curl http://localhost:8000/api/posts
```

### Get Single Post

```bash
curl http://localhost:8000/api/posts/1
```

### Update Post

```bash
curl -X PUT http://localhost:8000/api/posts/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "Updated Title",
    "body": "Updated content"
  }'
```

### Delete Post

```bash
curl -X DELETE http://localhost:8000/api/posts/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Next Steps

Congratulations! You've built a complete blog API with NeoFramework. Here's what to explore next:

- [Database & ORM](../database/getting-started.md) - Learn more about the ORM
- [Authentication](../security/authentication.md) - Add JWT authentication
- [Testing](../testing/getting-started.md) - Write comprehensive tests
- [API Resources](../api/resources.md) - Advanced resource transformations
- [Queue & Jobs](../advanced/queue.md) - Background processing

## Full Example Repository

You can find the complete code for this tutorial on GitHub:

```bash
git clone https://github.com/neonextechnologies/neoframework-blog-example
```

Happy coding! 🚀
