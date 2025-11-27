# 🚀 CRUD Generator

Generate complete CRUD (Create, Read, Update, Delete) functionality for your NeoFramework application. The CRUD generator creates models, migrations, controllers, views, and routes in one command.

## 📋 Table of Contents

- [Basic Usage](#basic-usage)
- [Command Options](#command-options)
- [Generated Files](#generated-files)
- [Complete Examples](#complete-examples)
- [Customization](#customization)
- [Best Practices](#best-practices)

## 🚀 Basic Usage

### Generate Complete CRUD

```bash
php neo make:crud Post
```

**Generated Files:**
- `app/Models/Post.php` - Eloquent model
- `app/Controllers/PostController.php` - Resource controller
- `database/migrations/xxxx_create_posts_table.php` - Migration
- `database/factories/PostFactory.php` - Model factory
- `database/seeders/PostSeeder.php` - Database seeder
- `resources/views/posts/` - View templates
  - `index.blade.php` - List view
  - `create.blade.php` - Create form
  - `edit.blade.php` - Edit form
  - `show.blade.php` - Detail view

**Route Registration:** Automatically added to `routes/web.php`

```php
Route::resource('posts', PostController::class);
```

## ⚙️ Command Options

### Available Options

| Option | Description |
|--------|-------------|
| `--fields=<list>` | Specify model fields |
| `--api` | Generate API-only CRUD |
| `--soft-deletes` | Add soft deletes |
| `--timestamps` | Add timestamps (default: true) |
| `--force` | Overwrite existing files |

### Generate with Fields

```bash
php neo make:crud Post --fields="title:string,content:text,status:enum:draft|published,user_id:foreignId"
```

### Generate API CRUD

```bash
php neo make:crud Post --api
```

Generates API controller and excludes web views.

### Generate with Soft Deletes

```bash
php neo make:crud Post --soft-deletes
```

Adds soft delete functionality to model and migration.

## 📝 Generated Code Examples

### Generated Model

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;
use Neo\Database\Eloquent\SoftDeletes;
use Neo\Metadata\Attributes\Table;
use Neo\Metadata\Attributes\Field;

#[Table(name: 'posts', timestamps: true, softDeletes: true)]
class Post extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'user_id',
        'category_id',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    /**
     * Get the user that owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the post.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the comments for the post.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the tags for the post.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }
}
```

### Generated Controller

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
        $posts = Post::with(['user', 'category'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15);

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:posts',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,archived',
            'category_id' => 'required|exists:categories,id',
            'published_at' => 'nullable|date',
        ]);

        $validated['user_id'] = auth()->id();

        $post = Post::create($validated);

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post): Response
    {
        $post->load(['user', 'category', 'comments', 'tags']);

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
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:posts,slug,' . $post->id,
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,archived',
            'category_id' => 'required|exists:categories,id',
            'published_at' => 'nullable|date',
        ]);

        $post->update($validated);

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Post $post)
    {
        $post->delete();

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post deleted successfully!');
    }
}
```

### Generated Migration

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->integer('view_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('category_id');
            $table->index('status');
            $table->index('published_at');
            $table->fullText(['title', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

### Generated Views

**Index View:** `resources/views/posts/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Posts')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Posts</h1>
        <a href="{{ route('posts.create') }}" class="btn btn-primary">
            Create New Post
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('posts.index') }}">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Search posts..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>
                                Draft
                            </option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>
                                Published
                            </option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>
                                Archived
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Posts Table -->
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Published</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        <tr>
                            <td>
                                <a href="{{ route('posts.show', $post) }}">
                                    {{ $post->title }}
                                </a>
                            </td>
                            <td>{{ $post->user->name }}</td>
                            <td>{{ $post->category->name }}</td>
                            <td>
                                <span class="badge badge-{{ $post->status == 'published' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($post->status) }}
                                </span>
                            </td>
                            <td>{{ $post->published_at?->format('M d, Y') ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('posts.edit', $post) }}" 
                                   class="btn btn-sm btn-primary">
                                    Edit
                                </a>
                                <form action="{{ route('posts.destroy', $post) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No posts found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $posts->links() }}
        </div>
    </div>
</div>
@endsection
```

**Create View:** `resources/views/posts/create.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Create Post')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Create New Post</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('posts.store') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="title">Title</label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required>
                            @error('title')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="slug">Slug</label>
                            <input type="text" 
                                   class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" 
                                   name="slug" 
                                   value="{{ old('slug') }}" 
                                   required>
                            @error('slug')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="category_id">Category</label>
                            <select class="form-control @error('category_id') is-invalid @enderror" 
                                    id="category_id" 
                                    name="category_id" 
                                    required>
                                <option value="">Select Category</option>
                                @foreach(\App\Models\Category::all() as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="excerpt">Excerpt</label>
                            <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                      id="excerpt" 
                                      name="excerpt" 
                                      rows="3">{{ old('excerpt') }}</textarea>
                            @error('excerpt')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="content">Content</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" 
                                      name="content" 
                                      rows="10" 
                                      required>{{ old('content') }}</textarea>
                            @error('content')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="status">Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>
                                    Draft
                                </option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>
                                    Published
                                </option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="published_at">Published Date</label>
                            <input type="datetime-local" 
                                   class="form-control @error('published_at') is-invalid @enderror" 
                                   id="published_at" 
                                   name="published_at" 
                                   value="{{ old('published_at') }}">
                            @error('published_at')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">Create Post</button>
                            <a href="{{ route('posts.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

## 🎯 Complete Examples

### Blog CRUD

```bash
php neo make:crud Post --fields="title:string,slug:string:unique,content:text,excerpt:text:nullable,status:enum:draft|published,user_id:foreignId,category_id:foreignId,published_at:timestamp:nullable" --soft-deletes
```

### E-commerce Product CRUD

```bash
php neo make:crud Product --fields="name:string,sku:string:unique,description:text,price:decimal:10,2,sale_price:decimal:10,2:nullable,quantity:integer,category_id:foreignId,brand_id:foreignId:nullable,status:enum:active|inactive" --soft-deletes
```

### User Management CRUD

```bash
php neo make:crud User --fields="name:string,email:string:unique,password:string,phone:string:nullable,role:enum:user|admin|moderator,is_active:boolean,email_verified_at:timestamp:nullable" --soft-deletes
```

### Task Management CRUD

```bash
php neo make:crud Task --fields="title:string,description:text,status:enum:pending|in_progress|completed,priority:enum:low|medium|high,user_id:foreignId,project_id:foreignId,due_date:date:nullable,completed_at:timestamp:nullable"
```

## 🔧 Customization

### Customize Generated Files

After generation, you can customize:

**Model Relationships:**

```php
public function comments()
{
    return $this->hasMany(Comment::class);
}

public function tags()
{
    return $this->belongsToMany(Tag::class);
}
```

**Controller Logic:**

```php
public function index(Request $request): Response
{
    $posts = Post::with(['user', 'category'])
        ->filter($request->all())
        ->latest()
        ->paginate(15);

    return view('posts.index', compact('posts'));
}
```

**Views Styling:**

Customize the generated views to match your application's design system.

### Add Additional Features

**Search Functionality:**

```php
public function index(Request $request): Response
{
    $posts = Post::query()
        ->when($request->search, function ($query, $search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%");
        })
        ->paginate(15);

    return view('posts.index', compact('posts'));
}
```

**Bulk Actions:**

```php
public function bulkDelete(Request $request)
{
    $ids = $request->input('ids', []);
    Post::whereIn('id', $ids)->delete();
    
    return redirect()->route('posts.index')
        ->with('success', 'Posts deleted successfully!');
}
```

## 🎯 Best Practices

### Plan Your Fields

```bash
# Good: Well-defined fields
php neo make:crud Product --fields="name:string,sku:string:unique,price:decimal:10,2,quantity:integer"

# Add more fields later with migration
php neo make:migration add_description_to_products_table --table=products
```

### Use Appropriate Field Types

```bash
# String fields
title:string
email:string:unique

# Text fields
description:text
content:longText

# Numeric fields
price:decimal:10,2
quantity:integer

# Enums
status:enum:draft|published|archived

# Foreign keys
user_id:foreignId
category_id:foreignId:nullable

# Dates
published_at:timestamp:nullable
created_at:timestamps
```

### Add Indexes

In generated migration:

```php
$table->index('user_id');
$table->index('category_id');
$table->index('status');
$table->fullText(['title', 'content']);
```

### Implement Soft Deletes

```bash
php neo make:crud Post --soft-deletes
```

### Add Authorization

In controller:

```php
public function __construct()
{
    $this->middleware('auth');
    $this->middleware('can:manage-posts')->except(['index', 'show']);
}
```

## 📚 Related Documentation

- [Controllers](controller.md) - Controller generator
- [Models](model.md) - Model generator
- [Migrations](migration.md) - Migration generator
- [Views](../../basics/views.md) - View templates
- [Routing](../../basics/routing.md) - Route definitions

## 🔗 Quick Reference

```bash
# Basic CRUD
php neo make:crud Post

# With fields
php neo make:crud Post --fields="title:string,content:text"

# API only
php neo make:crud Post --api

# With soft deletes
php neo make:crud Post --soft-deletes

# Force overwrite
php neo make:crud Post --force
```

**Field Syntax:**

```
name:type:modifier:modifier
name:string
email:string:unique
price:decimal:10,2
status:enum:draft|published|archived
user_id:foreignId
published_at:timestamp:nullable
```

**Generated Routes:**

```php
GET    /posts              posts.index     List all
GET    /posts/create       posts.create    Show create form
POST   /posts              posts.store     Store new
GET    /posts/{id}         posts.show      Show single
GET    /posts/{id}/edit    posts.edit      Show edit form
PUT    /posts/{id}         posts.update    Update
DELETE /posts/{id}         posts.destroy   Delete
```
