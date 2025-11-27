# Eloquent ORM

## Introduction

Eloquent is NeoFramework's powerful object-relational mapper (ORM) that makes it enjoyable to interact with your database. When using Eloquent, each database table has a corresponding "Model" that is used to interact with that table.

## Defining Models

### Creating Models

Generate a model using the CLI:

```bash
php neo make:model User
```

This creates `app/Models/User.php`:

```php
<?php

namespace App\Models;

use NeoPhp\Database\Eloquent\Model;

class User extends Model
{
    //
}
```

### Table Names

By convention, the "snake case", plural name of the class will be used as the table name:

```php
class User extends Model {}        // users table
class BlogPost extends Model {}    // blog_posts table
```

Specify a custom table name:

```php
class User extends Model
{
    protected string $table = 'my_users';
}
```

### Primary Keys

Eloquent assumes each table has a primary key column named `id`:

```php
class User extends Model
{
    protected string $primaryKey = 'user_id';
    
    // If primary key is not an incrementing integer
    public $incrementing = false;
    protected string $keyType = 'string';
}
```

### Timestamps

By default, Eloquent expects `created_at` and `updated_at` columns:

```php
class User extends Model
{
    // Disable timestamps
    public $timestamps = false;
    
    // Customize timestamp format
    protected string $dateFormat = 'U';
    
    // Customize column names
    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';
}
```

### Database Connection

Specify which connection to use:

```php
class User extends Model
{
    protected string $connection = 'mysql';
}
```

### Default Attribute Values

```php
class User extends Model
{
    protected array $attributes = [
        'active' => true,
        'role' => 'user',
    ];
}
```

## Retrieving Models

### Retrieving All Models

```php
use App\Models\User;

$users = User::all();

foreach ($users as $user) {
    echo $user->name;
}
```

### Adding Constraints

```php
$users = User::where('active', 1)
    ->orderBy('name')
    ->take(10)
    ->get();
```

### Retrieving Single Models

```php
// Retrieve by primary key
$user = User::find(1);

// Find or throw exception
$user = User::findOrFail(1);

// Find or execute callback
$user = User::findOr(1, function () {
    // Not found
});

// First matching model
$user = User::where('active', 1)->first();

// First or throw exception
$user = User::where('active', 1)->firstOrFail();

// First or create
$user = User::firstOrCreate(
    ['email' => 'john@example.com'],
    ['name' => 'John Doe']
);

// First or new
$user = User::firstOrNew(
    ['email' => 'john@example.com'],
    ['name' => 'John Doe']
);
```

### Retrieving Aggregates

```php
$count = User::where('active', 1)->count();
$max = User::max('votes');
$avg = User::avg('age');
$sum = User::sum('votes');
```

### Chunking Results

```php
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        // Process user
    }
});
```

## Inserting & Updating Models

### Inserts

```php
$user = new User;
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->save();

// Or use create method
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

### Mass Assignment

Protect against mass assignment vulnerabilities:

```php
class User extends Model
{
    // Allow mass assignment for these attributes
    protected array $fillable = ['name', 'email'];
    
    // Or protect specific attributes
    protected array $guarded = ['id', 'is_admin'];
    
    // Allow all attributes (not recommended)
    protected array $guarded = [];
}
```

### Updates

```php
$user = User::find(1);
$user->name = 'Jane Doe';
$user->save();

// Mass update
User::where('active', 1)->update(['status' => 'verified']);

// Update or create
User::updateOrCreate(
    ['email' => 'john@example.com'],
    ['name' => 'John Doe', 'active' => 1]
);
```

## Deleting Models

### Soft Deletes

Enable soft deletes:

```php
use NeoPhp\Database\Eloquent\Model;
use NeoPhp\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
}
```

Add `deleted_at` column to your migration:

```php
$table->softDeletes();
```

Delete a model:

```php
$user = User::find(1);
$user->delete();

// Soft deleted models are excluded from queries
$users = User::all(); // Won't include soft deleted

// Include soft deleted
$users = User::withTrashed()->get();

// Only soft deleted
$users = User::onlyTrashed()->get();

// Restore soft deleted
$user->restore();

// Permanently delete
$user->forceDelete();
```

### Permanent Deletes

```php
$user = User::find(1);
$user->delete();

// Delete by query
User::where('active', 0)->delete();

// Delete by primary key
User::destroy(1);
User::destroy([1, 2, 3]);
User::destroy(1, 2, 3);
```

## Query Scopes

### Local Scopes

Define reusable query constraints:

```php
class User extends Model
{
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
    
    public function scopePopular($query)
    {
        return $query->where('votes', '>', 100);
    }
    
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
```

Use scopes:

```php
$users = User::active()->popular()->get();
$users = User::ofType('admin')->get();
```

### Global Scopes

Apply scopes to all queries:

```php
use NeoPhp\Database\Eloquent\Scope;
use NeoPhp\Database\Eloquent\Builder;

class ActiveScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('active', 1);
    }
}
```

Register global scope:

```php
class User extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new ActiveScope);
        
        // Or anonymous
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('active', 1);
        });
    }
}
```

Remove global scopes:

```php
User::withoutGlobalScope(ActiveScope::class)->get();
User::withoutGlobalScope('active')->get();
User::withoutGlobalScopes()->get();
```

## Relationships

### One To One

```php
class User extends Model
{
    public function phone()
    {
        return $this->hasOne(Phone::class);
    }
}

class Phone extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

Access relationship:

```php
$phone = User::find(1)->phone;
$user = Phone::find(1)->user;
```

### One To Many

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

Access relationship:

```php
$comments = Post::find(1)->comments;
$post = Comment::find(1)->post;
```

### Many To Many

```php
class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

class Role extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
```

Access relationship:

```php
$roles = User::find(1)->roles;
$users = Role::find(1)->users;

// Attach
$user->roles()->attach($roleId);
$user->roles()->attach([1, 2, 3]);

// Detach
$user->roles()->detach($roleId);
$user->roles()->detach();

// Sync
$user->roles()->sync([1, 2, 3]);
```

### Has Many Through

```php
class Country extends Model
{
    public function posts()
    {
        return $this->hasManyThrough(Post::class, User::class);
    }
}
```

### Polymorphic Relations

```php
class Post extends Model
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

class Video extends Model
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

class Comment extends Model
{
    public function commentable()
    {
        return $this->morphTo();
    }
}
```

Access:

```php
$comments = Post::find(1)->comments;
$commentable = Comment::find(1)->commentable; // Post or Video
```

## Eager Loading

Avoid N+1 query problem:

```php
// Bad: N+1 queries
$users = User::all();
foreach ($users as $user) {
    echo $user->posts->title; // New query for each user
}

// Good: 2 queries
$users = User::with('posts')->get();
foreach ($users as $user) {
    echo $user->posts->title;
}
```

### Multiple Relationships

```php
$users = User::with(['posts', 'comments'])->get();
```

### Nested Eager Loading

```php
$users = User::with('posts.comments')->get();
```

### Lazy Eager Loading

```php
$users = User::all();

if ($someCondition) {
    $users->load('posts');
}
```

### Constraining Eager Loads

```php
$users = User::with(['posts' => function ($query) {
    $query->where('published', 1)
          ->orderBy('created_at', 'desc');
}])->get();
```

## Accessors & Mutators

### Accessors

Transform attribute values when retrieving:

```php
class User extends Model
{
    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);
    }
    
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}

$user = User::find(1);
echo $user->first_name; // John (capitalized)
echo $user->full_name;  // John Doe (computed)
```

### Mutators

Transform attribute values when setting:

```php
class User extends Model
{
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = strtolower($value);
    }
    
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}

$user = new User;
$user->first_name = 'JOHN'; // Stored as 'john'
$user->password = 'secret';  // Stored as hashed password
```

### Attribute Casting

Cast attributes to common data types:

```php
class User extends Model
{
    protected array $casts = [
        'is_admin' => 'boolean',
        'settings' => 'array',
        'birthday' => 'datetime',
        'amount' => 'decimal:2',
    ];
}

$user = User::find(1);
$user->is_admin;  // boolean
$user->settings;  // array
$user->birthday;  // DateTime instance
```

## Model Events

Models fire several events:

- `creating`, `created`
- `updating`, `updated`
- `saving`, `saved`
- `deleting`, `deleted`
- `restoring`, `restored`
- `retrieved`

### Register Event Listeners

```php
class User extends Model
{
    protected static function booted()
    {
        static::creating(function ($user) {
            $user->uuid = Str::uuid();
        });
        
        static::updated(function ($user) {
            Cache::forget("user.{$user->id}");
        });
        
        static::deleted(function ($user) {
            $user->posts()->delete();
        });
    }
}
```

### Observers

For complex event handling:

```bash
php neo make:observer UserObserver --model=User
```

```php
class UserObserver
{
    public function creating(User $user)
    {
        $user->uuid = Str::uuid();
    }
    
    public function created(User $user)
    {
        // Send welcome email
    }
    
    public function updated(User $user)
    {
        // Clear cache
    }
}
```

Register observer:

```php
// In service provider
User::observe(UserObserver::class);
```

## Serialization

### Array / JSON

```php
$user = User::with('posts')->find(1);

$array = $user->toArray();
$json = $user->toJson();
$json = (string) $user;
```

### Hiding Attributes

```php
class User extends Model
{
    protected array $hidden = ['password', 'remember_token'];
}
```

### Showing Attributes

```php
class User extends Model
{
    protected array $visible = ['name', 'email'];
}
```

### Appending Values

```php
class User extends Model
{
    protected array $appends = ['full_name'];
    
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
```

## Next Steps

- [Relationships](relationships.md) - Deep dive into relationships
- [Query Builder](query-builder.md) - Advanced queries
- [Migrations](migrations.md) - Database version control
- [Factories](factories.md) - Generate test data
