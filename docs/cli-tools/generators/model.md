# 🗃️ Model Generator

Generate Eloquent model classes for database interaction in your NeoFramework application. The model generator supports creating models with migrations, factories, seeders, and metadata attributes.

## 📋 Table of Contents

- [Basic Usage](#basic-usage)
- [Command Options](#command-options)
- [Generated Code](#generated-code)
- [Model Features](#model-features)
- [Advanced Examples](#advanced-examples)
- [Best Practices](#best-practices)

## 🚀 Basic Usage

### Generate Basic Model

```bash
php neo make:model Post
```

**Generated:** `app/Models/Post.php`

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];
}
```

## ⚙️ Command Options

### Available Options

| Option | Shortcut | Description |
|--------|----------|-------------|
| `--migration` | `-m` | Create migration file |
| `--factory` | `-f` | Create factory file |
| `--seeder` | `-s` | Create seeder file |
| `--all` | `-a` | Create migration, factory, and seeder |
| `--pivot` | `-p` | Create pivot model |
| `--force` | | Overwrite existing model |

### Create Model with Migration

```bash
php neo make:model Product --migration
```

**Shorthand:**

```bash
php neo make:model Product -m
```

**Generated Files:**
- `app/Models/Product.php`
- `database/migrations/2024_01_01_000000_create_products_table.php`

### Create Model with Factory

```bash
php neo make:model User --factory
```

**Shorthand:**

```bash
php neo make:model User -f
```

**Generated Files:**
- `app/Models/User.php`
- `database/factories/UserFactory.php`

### Create Model with Seeder

```bash
php neo make:model Category --seeder
```

**Shorthand:**

```bash
php neo make:model Category -s
```

**Generated Files:**
- `app/Models/Category.php`
- `database/seeders/CategorySeeder.php`

### Create Model with Everything

```bash
php neo make:model Article --all
```

**Shorthand:**

```bash
php neo make:model Article -a
```

**Generated Files:**
- `app/Models/Article.php`
- `database/migrations/2024_01_01_000000_create_articles_table.php`
- `database/factories/ArticleFactory.php`
- `database/seeders/ArticleSeeder.php`

### Create Pivot Model

```bash
php neo make:model PostTag --pivot
```

**Generated:** `app/Models/PostTag.php`

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Relations\Pivot;

class PostTag extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [];
}
```

## 📝 Generated Code Examples

### Complete Model Example

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;
use Neo\Database\Eloquent\SoftDeletes;
use Neo\Metadata\Attributes\Table;
use Neo\Metadata\Attributes\Field;

#[Table(name: 'users', timestamps: true)]
class User extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'full_name',
    ];

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the posts for the user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the user's role.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
```

### Model with Metadata Attributes

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;
use Neo\Metadata\Attributes\Table;
use Neo\Metadata\Attributes\Field;
use Neo\Metadata\Attributes\Relationship;

#[Table(
    name: 'posts',
    timestamps: true,
    softDeletes: true
)]
class Post extends Model
{
    #[Field(
        type: 'string',
        label: 'Title',
        required: true,
        maxLength: 255
    )]
    protected $title;

    #[Field(
        type: 'text',
        label: 'Content',
        required: true
    )]
    protected $content;

    #[Field(
        type: 'select',
        label: 'Status',
        options: ['draft', 'published', 'archived'],
        default: 'draft'
    )]
    protected $status;

    #[Field(
        type: 'number',
        label: 'View Count',
        default: 0
    )]
    protected $view_count;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title',
        'content',
        'status',
        'user_id',
        'category_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
        'view_count' => 'integer',
    ];

    #[Relationship(type: 'belongsTo', model: User::class)]
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    #[Relationship(type: 'belongsTo', model: Category::class)]
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    #[Relationship(type: 'hasMany', model: Comment::class)]
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    #[Relationship(type: 'belongsToMany', model: Tag::class)]
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include recent posts.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
```

## 🔧 Model Features

### Mass Assignment

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
    ];

    // Or use guarded to protect specific attributes
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
```

### Type Casting

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order_date' => 'datetime',
        'shipped_at' => 'datetime:Y-m-d',
        'total' => 'decimal:2',
        'items' => 'array',
        'metadata' => 'json',
        'is_paid' => 'boolean',
        'quantity' => 'integer',
    ];
}
```

### Hidden Attributes

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;

class User extends Model
{
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /**
     * The attributes that should be visible for serialization.
     *
     * @var array<string>
     */
    protected $visible = [
        'id',
        'name',
        'email',
    ];
}
```

### Appended Attributes

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;

class User extends Model
{
    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'full_name',
        'profile_url',
    ];

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user's profile URL.
     */
    public function getProfileUrlAttribute(): string
    {
        return url("/profile/{$this->id}");
    }
}
```

### Soft Deletes

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;
use Neo\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'deleted_at',
        'published_at',
    ];
}
```

## 🔗 Relationships

### One-to-Many

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;

class User extends Model
{
    /**
     * Get the posts for the user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends Model
{
    /**
     * Get the user that owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Many-to-Many

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * Get the tags for the post.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class)
            ->withTimestamps()
            ->withPivot(['order', 'featured']);
    }
}

class Tag extends Model
{
    /**
     * Get the posts for the tag.
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class)
            ->withTimestamps();
    }
}
```

### Has-Many-Through

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;

class Country extends Model
{
    /**
     * Get all of the posts for the country.
     */
    public function posts()
    {
        return $this->hasManyThrough(
            Post::class,
            User::class,
            'country_id', // Foreign key on users table
            'user_id',    // Foreign key on posts table
            'id',         // Local key on countries table
            'id'          // Local key on users table
        );
    }
}
```

### Polymorphic

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;

class Comment extends Model
{
    /**
     * Get the parent commentable model (post or video).
     */
    public function commentable()
    {
        return $this->morphTo();
    }
}

class Post extends Model
{
    /**
     * Get all of the post's comments.
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

class Video extends Model
{
    /**
     * Get all of the video's comments.
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
```

## 🎨 Query Scopes

### Local Scopes

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include posts by a specific author.
     */
    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('user_id', $authorId);
    }

    /**
     * Scope a query to only include recent posts.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to order posts by popularity.
     */
    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc')
            ->orderBy('likes_count', 'desc');
    }
}

// Usage
$posts = Post::published()->recent(30)->popular()->get();
$userPosts = Post::byAuthor($userId)->published()->get();
```

### Global Scopes

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;
use Neo\Database\Eloquent\Scope;
use Neo\Database\Eloquent\Builder;

class PublishedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('status', 'published');
    }
}

class Post extends Model
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new PublishedScope);
        
        // Or use a closure
        static::addGlobalScope('status', function (Builder $builder) {
            $builder->where('status', '!=', 'deleted');
        });
    }
}

// Remove global scope
$allPosts = Post::withoutGlobalScope(PublishedScope::class)->get();
$allStatuses = Post::withoutGlobalScope('status')->get();
```

## 🎯 Advanced Examples

### Complete E-commerce Product Model

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;
use Neo\Database\Eloquent\SoftDeletes;
use Neo\Metadata\Attributes\Table;
use Neo\Metadata\Attributes\Field;

#[Table(name: 'products', timestamps: true, softDeletes: true)]
class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'sale_price',
        'quantity',
        'sku',
        'category_id',
        'brand_id',
        'status',
    ];

    protected $hidden = [
        'cost_price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'quantity' => 'integer',
        'is_featured' => 'boolean',
        'specifications' => 'json',
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'discount_percentage',
        'in_stock',
        'formatted_price',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class)
            ->withPivot(['quantity', 'price'])
            ->withTimestamps();
    }

    // Accessors
    public function getDiscountPercentageAttribute(): ?float
    {
        if (!$this->sale_price || $this->sale_price >= $this->price) {
            return null;
        }

        return round((($this->price - $this->sale_price) / $this->price) * 100, 2);
    }

    public function getInStockAttribute(): bool
    {
        return $this->quantity > 0;
    }

    public function getFormattedPriceAttribute(): string
    {
        $price = $this->sale_price ?? $this->price;
        return '$' . number_format($price, 2);
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = str_slug($value);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOnSale($query)
    {
        return $query->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    // Methods
    public function decrementStock(int $quantity): bool
    {
        if ($this->quantity < $quantity) {
            return false;
        }

        $this->decrement('quantity', $quantity);
        return true;
    }

    public function incrementStock(int $quantity): void
    {
        $this->increment('quantity', $quantity);
    }

    public function averageRating(): float
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function totalReviews(): int
    {
        return $this->reviews()->count();
    }
}
```

### Blog Post Model with Features

```php
<?php

namespace App\Models;

use Neo\Database\Eloquent\Model;
use Neo\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'user_id',
        'category_id',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'likes_count' => 'integer',
    ];

    protected $appends = [
        'reading_time',
        'is_published',
    ];

    protected static function booted(): void
    {
        // Auto-generate slug on creating
        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = str_slug($post->title);
            }
        });

        // Increment view count on retrieving
        static::retrieved(function ($post) {
            if (request()->route() && request()->route()->getName() === 'posts.show') {
                $post->increment('view_count');
            }
        });
    }

    // Relationships
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    public function allComments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    // Accessors
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return (int) ceil($wordCount / 200); // Assuming 200 words per minute
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published' 
            && $this->published_at 
            && $this->published_at->isPast();
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '>', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('user_id', $authorId);
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeWithTag($query, $tagId)
    {
        return $query->whereHas('tags', function ($query) use ($tagId) {
            $query->where('tags.id', $tagId);
        });
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%");
        });
    }

    public function scopePopular($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days))
            ->orderBy('view_count', 'desc')
            ->orderBy('likes_count', 'desc');
    }

    // Methods
    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update(['status' => 'draft']);
    }

    public function isLikedBy($user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function like($user): void
    {
        if (!$this->isLikedBy($user)) {
            $this->likes()->create(['user_id' => $user->id]);
            $this->increment('likes_count');
        }
    }

    public function unlike($user): void
    {
        if ($this->isLikedBy($user)) {
            $this->likes()->where('user_id', $user->id)->delete();
            $this->decrement('likes_count');
        }
    }
}
```

## 🎯 Best Practices

### Naming Conventions

```php
// Model names: Singular, PascalCase
User, Post, OrderItem, ProductCategory

// Table names: Plural, snake_case
users, posts, order_items, product_categories

// Pivot tables: Singular, alphabetical order
post_tag (not tag_post)
```

### Mass Assignment Protection

```php
// Use fillable for allowed attributes
protected $fillable = ['name', 'email'];

// Or use guarded for protected attributes
protected $guarded = ['id', 'password'];

// Never use this in production!
protected $guarded = [];
```

### Type Casting

```php
// Always cast attributes to their proper types
protected $casts = [
    'is_active' => 'boolean',
    'price' => 'decimal:2',
    'metadata' => 'json',
    'published_at' => 'datetime',
];
```

### Query Optimization

```php
// Bad: N+1 problem
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // Executes query for each post
}

// Good: Eager loading
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name; // Uses cached data
}

// Even better: Select only needed columns
$posts = Post::with('author:id,name')->select('id', 'title', 'user_id')->get();
```

## 📚 Related Documentation

- [Eloquent ORM](../database/eloquent.md) - Complete Eloquent documentation
- [Relationships](../database/relationships.md) - Model relationships
- [Migrations](../database/migrations.md) - Database migrations
- [Metadata](../metadata/introduction.md) - Model metadata attributes

## 🔗 Quick Reference

```bash
# Basic model
php neo make:model Post

# Model with migration
php neo make:model Product -m

# Model with factory
php neo make:model User -f

# Model with seeder
php neo make:model Category -s

# Model with everything
php neo make:model Article -a

# Pivot model
php neo make:model PostTag --pivot

# Force overwrite
php neo make:model Post --force
```
