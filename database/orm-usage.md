# NeoFramework - Advanced ORM Usage Examples

## Table of Contents
- [Relationships](#relationships)
- [Eager Loading](#eager-loading)
- [Query Scopes](#query-scopes)
- [Model Events](#model-events)
- [Soft Deletes](#soft-deletes)

---

## Relationships

### Defining Relationships

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;

class User extends Model
{
    protected string $table = 'users';
    
    // One-to-Many: User has many posts
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }
    
    // One-to-One: User has one profile
    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id');
    }
}

class Post extends Model
{
    protected string $table = 'posts';
    
    // Inverse: Post belongs to user
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    // One-to-Many: Post has many comments
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }
    
    // Many-to-Many: Post belongs to many categories
    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'post_category',  // Pivot table
            'post_id',        // Foreign key on pivot
            'category_id'     // Related key on pivot
        );
    }
}
```

### Using Relationships

```php
// Access relationships
$user = User::find(1);
$posts = $user->posts;  // Get all user's posts

// Access inverse relationship
$post = Post::find(1);
$author = $post->author;  // Get post's author

// Many-to-Many
$categories = $post->categories;  // Get all post's categories

// Create through relationship
$user->posts()->create([
    'title' => 'New Post',
    'content' => 'Post content...'
]);

// Associate/Dissociate
$post->author()->associate($user);
$post->save();

$post->author()->dissociate();
$post->save();

// Attach/Detach (Many-to-Many)
$post->categories()->attach([1, 2, 3]);
$post->categories()->detach([2]);
$post->categories()->sync([1, 3, 4]);  // Only keep these IDs
```

---

## Eager Loading

Prevent N+1 query problems by eager loading relationships:

```php
// Bad: N+1 queries
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name;  // Query for each post!
}

// Good: Eager loading (2 queries total)
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name;  // No additional queries
}

// Multiple relationships
$posts = Post::with(['author', 'comments', 'categories'])->get();

// Nested relationships
$posts = Post::with(['comments.author'])->get();

// Conditional eager loading
$posts = Post::with(['comments' => function($query) {
    $query->where('status', 'approved');
}])->get();
```

---

## Query Scopes

### Local Scopes

Define reusable query constraints:

```php
class Post extends Model
{
    // Local scope
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
    
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'DESC')->limit($limit);
    }
    
    public function scopeSearch($query, $keyword)
    {
        return $query->where('title', 'LIKE', "%{$keyword}%")
                     ->orWhere('content', 'LIKE', "%{$keyword}%");
    }
}

// Usage
$posts = Post::published()->recent(5)->get();
$posts = Post::search('laravel')->published()->get();
```

### Global Scopes

Apply constraints to all queries automatically:

```php
use NeoPhp\Database\Model;
use NeoPhp\Database\Concerns\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;  // Automatically adds SoftDeletingScope
    
    protected $dates = ['deleted_at'];
}

// All queries automatically exclude soft deleted records
$posts = Post::all();  // WHERE deleted_at IS NULL

// Include soft deleted
$posts = Post::withTrashed()->get();

// Only soft deleted
$posts = Post::onlyTrashed()->get();
```

---

## Model Events

Hook into model lifecycle:

```php
class Post extends Model
{
    use NeoPhp\Database\Concerns\HasEvents;
    
    protected static function boot()
    {
        parent::boot();
        
        // Before creating
        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = strtolower(str_replace(' ', '-', $post->title));
            }
        });
        
        // After creating
        static::created(function ($post) {
            logger()->info("New post created: {$post->title}");
        });
        
        // Before updating
        static::updating(function ($post) {
            $post->updated_by = auth()->id();
        });
        
        // Before deleting
        static::deleting(function ($post) {
            // Cascade delete comments
            $post->comments()->delete();
        });
        
        // After saving (create or update)
        static::saved(function ($post) {
            cache()->forget("post:{$post->id}");
        });
        
        // When retrieved from database
        static::retrieved(function ($post) {
            // Increment view count in background
        });
    }
}
```

### Available Events

- `retrieved` - After model retrieved from database
- `creating` - Before model created
- `created` - After model created
- `updating` - Before model updated
- `updated` - After model updated
- `saving` - Before model saved (create or update)
- `saved` - After model saved (create or update)
- `deleting` - Before model deleted
- `deleted` - After model deleted
- `restoring` - Before soft deleted model restored
- `restored` - After soft deleted model restored

---

## Soft Deletes

Instead of permanently deleting records, mark them as deleted:

```php
use NeoPhp\Database\Model;
use NeoPhp\Database\Concerns\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
    
    protected array $dates = ['deleted_at'];
}
```

### Migration

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->timestamp('deleted_at')->nullable();
    $table->timestamps();
});
```

### Usage

```php
// Soft delete (sets deleted_at timestamp)
$post = Post::find(1);
$post->delete();

// Check if soft deleted
if ($post->trashed()) {
    echo "Post is soft deleted";
}

// Restore soft deleted
$post->restore();

// Permanently delete
$post->forceDelete();

// Query with soft deleted
$posts = Post::withTrashed()->get();

// Query only soft deleted
$posts = Post::onlyTrashed()->get();

// Restore all soft deleted
Post::onlyTrashed()->restore();
```

---

## Complete Example

```php
use Modules\Blog\Models\Post;
use Modules\Blog\Models\Comment;
use Modules\Blog\Models\Category;
use App\Models\User;

// Create post with relationships
$user = User::find(1);
$post = $user->posts()->create([
    'title' => 'My First Post',
    'content' => 'This is my first blog post...',
    'status' => 'draft'
]);

// Attach categories
$post->categories()->attach([1, 2, 3]);

// Add comments
$post->comments()->create([
    'user_id' => 2,
    'content' => 'Great post!',
    'status' => 'approved'
]);

// Publish post
$post->publish();

// Query with eager loading and scopes
$recentPosts = Post::with(['author', 'comments.author', 'categories'])
    ->published()
    ->recent(10)
    ->get();

foreach ($recentPosts as $post) {
    echo $post->title;
    echo $post->author->name;
    echo $post->comments->count() . ' comments';
    
    foreach ($post->categories as $category) {
        echo $category->name;
    }
}

// Search published posts
$results = Post::search('laravel')
    ->published()
    ->with('author')
    ->get();

// Soft delete
$post->delete();

// Get trashed posts
$trashed = Post::onlyTrashed()->get();

// Restore
$post->restore();
```

---

## Performance Tips

1. **Always use eager loading** when accessing relationships in loops
2. **Use scopes** instead of repeating query logic
3. **Cache frequent queries** using model events
4. **Use soft deletes** instead of hard deletes when audit trail needed
5. **Index foreign keys** for better relationship query performance

---

## Next Steps

- [Database Migrations](../database/migrations.md)
- [Query Builder](../database/query-builder.md)
- [Validation](../api-reference/validation.md)
