# Database Relationships

## Introduction

Database tables are often related to one another. For example, a blog post may have many comments, or an order may have many products. Eloquent makes managing and working with these relationships easy and supports several different types of relationships.

## One To One

A one-to-one relationship is a very basic relation. For example, a `User` model might have one `Phone` model.

### Defining the Relationship

```php
<?php

namespace App\Models;

use NeoPhp\Database\Eloquent\Model;

class User extends Model
{
    /**
     * Get the phone record associated with the user.
     */
    public function phone()
    {
        return $this->hasOne(Phone::class);
    }
}
```

### Inverse Relationship

```php
class Phone extends Model
{
    /**
     * Get the user that owns the phone.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Accessing the Relationship

```php
$user = User::find(1);
$phone = $user->phone;

echo $phone->number;

// Inverse
$phone = Phone::find(1);
$user = $phone->user;

echo $user->name;
```

### Custom Keys

```php
return $this->hasOne(Phone::class, 'user_id', 'id');
return $this->belongsTo(User::class, 'user_id', 'id');
```

## One To Many

A one-to-many relationship is used to define relationships where a single model owns any amount of other models. For example, a blog post may have many comments.

### Defining the Relationship

```php
class Post extends Model
{
    /**
     * Get the comments for the blog post.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

### Inverse Relationship

```php
class Comment extends Model
{
    /**
     * Get the post that owns the comment.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
```

### Accessing the Relationship

```php
$post = Post::find(1);

foreach ($post->comments as $comment) {
    echo $comment->body;
}

// Inverse
$comment = Comment::find(1);
$post = $comment->post;
```

### Querying Relationships

```php
// Get posts with at least one comment
$posts = Post::has('comments')->get();

// Get posts with more than 3 comments
$posts = Post::has('comments', '>', 3)->get();

// Get posts with comments containing "foo"
$posts = Post::whereHas('comments', function ($query) {
    $query->where('body', 'like', '%foo%');
})->get();

// Get posts without comments
$posts = Post::doesntHave('comments')->get();
```

## Many To Many

Many-to-many relations are slightly more complicated. An example is a user with many roles, where the roles are also shared by other users.

### Table Structure

```
users
    id
    name

roles
    id
    name

role_user (pivot table)
    user_id
    role_id
```

### Defining the Relationship

```php
class User extends Model
{
    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
```

### Inverse Relationship

```php
class Role extends Model
{
    /**
     * The users that belong to the role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
```

### Accessing the Relationship

```php
$user = User::find(1);

foreach ($user->roles as $role) {
    echo $role->name;
}
```

### Custom Pivot Table

```php
return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
```

### Retrieving Pivot Data

```php
$user = User::find(1);

foreach ($user->roles as $role) {
    echo $role->pivot->created_at;
}
```

### Custom Pivot Columns

```php
return $this->belongsToMany(Role::class)
    ->withPivot('active', 'created_by');

// Access
$role->pivot->active;
```

### Timestamps on Pivot

```php
return $this->belongsToMany(Role::class)
    ->withTimestamps();
```

### Attaching / Detaching

```php
$user = User::find(1);

// Attach single role
$user->roles()->attach($roleId);

// Attach with pivot data
$user->roles()->attach($roleId, ['active' => true]);

// Attach multiple
$user->roles()->attach([1, 2, 3]);

// Detach single
$user->roles()->detach($roleId);

// Detach all
$user->roles()->detach();

// Sync (attach/detach to match array)
$user->roles()->sync([1, 2, 3]);

// Sync without detaching
$user->roles()->syncWithoutDetaching([1, 2, 3]);

// Toggle
$user->roles()->toggle([1, 2, 3]);
```

## Has One Through

The "has-one-through" relationship links models through a single intermediate model.

```
users
    id
    name

accounts
    id
    user_id
    account_number

account_histories
    id
    account_id
    action
```

```php
class User extends Model
{
    /**
     * Get the account history for the user.
     */
    public function accountHistory()
    {
        return $this->hasOneThrough(
            AccountHistory::class,
            Account::class,
            'user_id',        // Foreign key on accounts table
            'account_id',     // Foreign key on account_histories table
            'id',             // Local key on users table
            'id'              // Local key on accounts table
        );
    }
}
```

## Has Many Through

The "has-many-through" relationship provides a convenient shortcut for accessing distant relations via an intermediate relation.

```
countries
    id
    name

users
    id
    country_id
    name

posts
    id
    user_id
    title
```

```php
class Country extends Model
{
    /**
     * Get all posts for the country.
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

Access:

```php
$country = Country::find(1);

foreach ($country->posts as $post) {
    echo $post->title;
}
```

## Polymorphic Relations

Polymorphic relations allow a model to belong to more than one other model on a single association.

### One To One (Polymorphic)

```
posts
    id
    title

videos
    id
    name

images
    id
    url
    imageable_id
    imageable_type
```

```php
class Image extends Model
{
    /**
     * Get the parent imageable model (post or video).
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}

class Post extends Model
{
    /**
     * Get the post's image.
     */
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}

class Video extends Model
{
    /**
     * Get the video's image.
     */
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
```

Access:

```php
$post = Post::find(1);
$image = $post->image;

$image = Image::find(1);
$imageable = $image->imageable; // Post or Video
```

### One To Many (Polymorphic)

```php
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
```

### Many To Many (Polymorphic)

```
posts
    id
    name

videos
    id
    name

tags
    id
    name

taggables
    tag_id
    taggable_id
    taggable_type
```

```php
class Post extends Model
{
    /**
     * Get all of the tags for the post.
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}

class Video extends Model
{
    /**
     * Get all of the tags for the video.
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}

class Tag extends Model
{
    /**
     * Get all of the posts that are assigned this tag.
     */
    public function posts()
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }

    /**
     * Get all of the videos that are assigned this tag.
     */
    public function videos()
    {
        return $this->morphedByMany(Video::class, 'taggable');
    }
}
```

## Eager Loading

Eager loading alleviates the N+1 query problem:

```php
// Bad: N+1 queries
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // New query each iteration
}

// Good: 2 queries
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name;
}
```

### Multiple Relationships

```php
$posts = Post::with(['author', 'comments'])->get();
```

### Nested Eager Loading

```php
$posts = Post::with('comments.author')->get();
```

### Constraining Eager Loads

```php
$posts = Post::with(['comments' => function ($query) {
    $query->where('approved', 1)
          ->orderBy('created_at', 'desc');
}])->get();
```

### Lazy Eager Loading

```php
$posts = Post::all();

if ($someCondition) {
    $posts->load('comments');
}
```

## Practical Examples

### Example 1: Blog System

```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class Post extends Model
{
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

class Comment extends Model
{
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
    
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
}

// Usage
$post = Post::with(['author', 'comments.author', 'tags'])->find(1);

echo $post->author->name;
echo $post->comments->count();
foreach ($post->tags as $tag) {
    echo $tag->name;
}
```

### Example 2: E-commerce System

```php
class Order extends Model
{
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
    
    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }
}

class OrderItem extends Model
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

class Product extends Model
{
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
    
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}

// Usage
$order = Order::with([
    'customer',
    'items.product.images',
    'payment',
    'shipment'
])->find(1);

foreach ($order->items as $item) {
    echo $item->product->name;
    echo $item->product->images->first()->url;
}
```

## Best Practices

### 1. Always Eager Load to Avoid N+1

```php
// Good
$posts = Post::with('author')->get();

// Bad
$posts = Post::all(); // N+1 when accessing author
```

### 2. Use Constraints on Eager Loading

```php
$posts = Post::with(['comments' => function ($query) {
    $query->where('approved', 1)->latest();
}])->get();
```

### 3. Define Inverse Relationships

```php
class Post extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class Comment extends Model
{
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
```

### 4. Use Relationship Methods for Queries

```php
// Get posts with comments
$posts = Post::has('comments')->get();

// Count comments
$post->comments()->count();
```

### 5. Use Sync for Many-to-Many

```php
$user->roles()->sync([1, 2, 3]);
```

## Next Steps

- [Eloquent ORM](eloquent.md) - Basic Eloquent usage
- [Query Builder](query-builder.md) - Advanced queries
- [Migrations](migrations.md) - Database schema
- [Factories](../testing/factories.md) - Generate test data
