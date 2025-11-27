# Relationship Attributes

## Introduction

NeoFramework's relationship attributes provide a declarative way to define Eloquent relationships using PHP 8 attributes. This approach makes relationships more explicit, enables automatic eager loading optimization, and supports advanced relationship configurations.

## Core Relationship Types

### HasOne Attribute

Define one-to-one relationships:

```php
<?php

namespace App\Models;

use Neo\Database\Model;
use Neo\Metadata\Attributes\HasOne;

class User extends Model
{
    #[HasOne(
        target: Profile::class,
        foreignKey: 'user_id',
        localKey: 'id'
    )]
    public ?Profile $profile;
}

class Profile extends Model
{
    public int $user_id;
    public string $bio;
    public string $avatar;
}

// Usage
$user = User::find(1);
echo $user->profile->bio;
```

#### Advanced HasOne

```php
<?php

class User extends Model
{
    // With custom foreign key
    #[HasOne(
        target: Profile::class,
        foreignKey: 'owner_id',
        localKey: 'id'
    )]
    public ?Profile $profile;
    
    // With default values
    #[HasOne(
        target: Settings::class,
        foreignKey: 'user_id',
        default: ['theme' => 'light', 'language' => 'en']
    )]
    public Settings $settings;
    
    // With constraints
    #[HasOne(
        target: Subscription::class,
        foreignKey: 'user_id',
        where: ['status' => 'active']
    )]
    public ?Subscription $activeSubscription;
}
```

### HasMany Attribute

Define one-to-many relationships:

```php
<?php

use Neo\Metadata\Attributes\HasMany;
use Neo\Database\Collection;

class User extends Model
{
    #[HasMany(
        target: Post::class,
        foreignKey: 'user_id',
        localKey: 'id'
    )]
    public Collection $posts;
    
    #[HasMany(
        target: Comment::class,
        foreignKey: 'user_id'
    )]
    public Collection $comments;
}

class Post extends Model
{
    public int $user_id;
    public string $title;
    public string $content;
}

// Usage
$user = User::find(1);
foreach ($user->posts as $post) {
    echo $post->title;
}

// Add new related model
$post = new Post();
$post->title = 'New Post';
$user->posts()->save($post);
```

#### Advanced HasMany

```php
<?php

class User extends Model
{
    // Ordered relationship
    #[HasMany(
        target: Post::class,
        foreignKey: 'user_id',
        orderBy: ['published_at' => 'desc']
    )]
    public Collection $posts;
    
    // With conditions
    #[HasMany(
        target: Post::class,
        foreignKey: 'user_id',
        where: ['status' => 'published']
    )]
    public Collection $publishedPosts;
    
    // Limited results
    #[HasMany(
        target: Post::class,
        foreignKey: 'user_id',
        limit: 10,
        orderBy: ['created_at' => 'desc']
    )]
    public Collection $recentPosts;
    
    // With eager loading
    #[HasMany(
        target: Comment::class,
        foreignKey: 'user_id',
        eagerLoad: ['post', 'likes']
    )]
    public Collection $comments;
}
```

### BelongsTo Attribute

Define inverse one-to-many relationships:

```php
<?php

use Neo\Metadata\Attributes\BelongsTo;

class Post extends Model
{
    public int $user_id;
    public int $category_id;
    
    #[BelongsTo(
        target: User::class,
        foreignKey: 'user_id',
        ownerKey: 'id'
    )]
    public User $author;
    
    #[BelongsTo(
        target: Category::class,
        foreignKey: 'category_id',
        ownerKey: 'id'
    )]
    public Category $category;
}

// Usage
$post = Post::find(1);
echo $post->author->name;
echo $post->category->name;
```

#### Advanced BelongsTo

```php
<?php

class Post extends Model
{
    // Custom relationship name
    #[BelongsTo(
        target: User::class,
        foreignKey: 'user_id',
        ownerKey: 'id',
        relation: 'author'
    )]
    public User $author;
    
    // Optional relationship
    #[BelongsTo(
        target: User::class,
        foreignKey: 'editor_id',
        ownerKey: 'id',
        nullable: true
    )]
    public ?User $editor;
    
    // With default
    #[BelongsTo(
        target: Category::class,
        foreignKey: 'category_id',
        default: ['name' => 'Uncategorized']
    )]
    public Category $category;
}
```

### BelongsToMany Attribute

Define many-to-many relationships:

```php
<?php

use Neo\Metadata\Attributes\BelongsToMany;

class User extends Model
{
    #[BelongsToMany(
        target: Role::class,
        pivotTable: 'role_user',
        foreignKey: 'user_id',
        relatedKey: 'role_id'
    )]
    public Collection $roles;
}

class Role extends Model
{
    #[BelongsToMany(
        target: User::class,
        pivotTable: 'role_user',
        foreignKey: 'role_id',
        relatedKey: 'user_id'
    )]
    public Collection $users;
}

// Usage
$user = User::find(1);
foreach ($user->roles as $role) {
    echo $role->name;
}

// Attach role
$user->roles()->attach($roleId);

// Detach role
$user->roles()->detach($roleId);

// Sync roles
$user->roles()->sync([1, 2, 3]);
```

#### Advanced BelongsToMany

```php
<?php

class User extends Model
{
    // With pivot columns
    #[BelongsToMany(
        target: Role::class,
        pivotTable: 'role_user',
        foreignKey: 'user_id',
        relatedKey: 'role_id',
        pivotColumns: ['granted_at', 'granted_by']
    )]
    public Collection $roles;
    
    // With timestamps
    #[BelongsToMany(
        target: Team::class,
        pivotTable: 'team_user',
        foreignKey: 'user_id',
        relatedKey: 'team_id',
        withTimestamps: true
    )]
    public Collection $teams;
    
    // With pivot filtering
    #[BelongsToMany(
        target: Project::class,
        pivotTable: 'project_user',
        foreignKey: 'user_id',
        relatedKey: 'project_id',
        wherePivot: ['status' => 'active']
    )]
    public Collection $activeProjects;
    
    // Custom pivot model
    #[BelongsToMany(
        target: Course::class,
        pivotTable: 'enrollments',
        foreignKey: 'student_id',
        relatedKey: 'course_id',
        pivotModel: Enrollment::class
    )]
    public Collection $courses;
}

// Access pivot data
foreach ($user->roles as $role) {
    echo $role->pivot->granted_at;
    echo $role->pivot->granted_by;
}
```

### HasManyThrough Attribute

Define has-many-through relationships:

```php
<?php

use Neo\Metadata\Attributes\HasManyThrough;

class Country extends Model
{
    #[HasMany(target: User::class)]
    public Collection $users;
    
    #[HasManyThrough(
        target: Post::class,
        through: User::class,
        firstKey: 'country_id',
        secondKey: 'user_id',
        localKey: 'id',
        secondLocalKey: 'id'
    )]
    public Collection $posts;
}

class User extends Model
{
    public int $country_id;
    
    #[HasMany(target: Post::class)]
    public Collection $posts;
}

class Post extends Model
{
    public int $user_id;
}

// Usage
$country = Country::find(1);
foreach ($country->posts as $post) {
    echo $post->title;
}
```

### HasOneThrough Attribute

Define has-one-through relationships:

```php
<?php

use Neo\Metadata\Attributes\HasOneThrough;

class Supplier extends Model
{
    #[HasOne(target: Account::class)]
    public Account $account;
    
    #[HasOneThrough(
        target: AccountHistory::class,
        through: Account::class,
        firstKey: 'supplier_id',
        secondKey: 'account_id',
        localKey: 'id',
        secondLocalKey: 'id'
    )]
    public AccountHistory $accountHistory;
}
```

## Polymorphic Relationships

### MorphOne Attribute

Define polymorphic one-to-one relationships:

```php
<?php

use Neo\Metadata\Attributes\MorphOne;

class Post extends Model
{
    #[MorphOne(
        target: Image::class,
        name: 'imageable'
    )]
    public ?Image $image;
}

class User extends Model
{
    #[MorphOne(
        target: Image::class,
        name: 'imageable'
    )]
    public ?Image $avatar;
}

class Image extends Model
{
    public int $imageable_id;
    public string $imageable_type;
    public string $url;
}

// Usage
$post = Post::find(1);
echo $post->image->url;
```

### MorphMany Attribute

Define polymorphic one-to-many relationships:

```php
<?php

use Neo\Metadata\Attributes\MorphMany;

class Post extends Model
{
    #[MorphMany(
        target: Comment::class,
        name: 'commentable'
    )]
    public Collection $comments;
}

class Video extends Model
{
    #[MorphMany(
        target: Comment::class,
        name: 'commentable'
    )]
    public Collection $comments;
}

class Comment extends Model
{
    public int $commentable_id;
    public string $commentable_type;
    public string $content;
}

// Usage
$post = Post::find(1);
foreach ($post->comments as $comment) {
    echo $comment->content;
}
```

### MorphTo Attribute

Define inverse polymorphic relationships:

```php
<?php

use Neo\Metadata\Attributes\MorphTo;

class Comment extends Model
{
    public int $commentable_id;
    public string $commentable_type;
    
    #[MorphTo(name: 'commentable')]
    public Model $commentable;
}

// Usage
$comment = Comment::find(1);
echo get_class($comment->commentable);  // Post or Video
echo $comment->commentable->title;
```

### MorphToMany Attribute

Define polymorphic many-to-many relationships:

```php
<?php

use Neo\Metadata\Attributes\MorphToMany;

class Post extends Model
{
    #[MorphToMany(
        target: Tag::class,
        name: 'taggable',
        pivotTable: 'taggables'
    )]
    public Collection $tags;
}

class Video extends Model
{
    #[MorphToMany(
        target: Tag::class,
        name: 'taggable',
        pivotTable: 'taggables'
    )]
    public Collection $tags;
}

class Tag extends Model
{
    #[MorphedByMany(
        target: Post::class,
        name: 'taggable'
    )]
    public Collection $posts;
    
    #[MorphedByMany(
        target: Video::class,
        name: 'taggable'
    )]
    public Collection $videos;
}

// Usage
$post = Post::find(1);
$post->tags()->attach($tagId);
```

## Eager Loading

### EagerLoad Attribute

Configure automatic eager loading:

```php
<?php

use Neo\Metadata\Attributes\EagerLoad;

#[EagerLoad(['author', 'category', 'tags'])]
class Post extends Model
{
    #[BelongsTo(target: User::class)]
    public User $author;
    
    #[BelongsTo(target: Category::class)]
    public Category $category;
    
    #[BelongsToMany(target: Tag::class)]
    public Collection $tags;
}

// Automatically eager loads relationships
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name;  // No N+1 query
}
```

### Nested Eager Loading

```php
<?php

#[EagerLoad([
    'author',
    'comments.author',
    'category.parent'
])]
class Post extends Model
{
    #[BelongsTo(target: User::class)]
    public User $author;
    
    #[HasMany(target: Comment::class)]
    public Collection $comments;
    
    #[BelongsTo(target: Category::class)]
    public Category $category;
}
```

### Conditional Eager Loading

```php
<?php

use Neo\Metadata\Attributes\EagerLoad;

class Post extends Model
{
    #[BelongsTo(target: User::class)]
    public User $author;
    
    #[HasMany(
        target: Comment::class,
        eagerLoad: true,
        eagerLoadWhen: ['approved' => true]
    )]
    public Collection $comments;
}
```

## Relationship Constraints

### WithCount Attribute

Automatically include relationship counts:

```php
<?php

use Neo\Metadata\Attributes\WithCount;

#[WithCount(['posts', 'comments'])]
class User extends Model
{
    #[HasMany(target: Post::class)]
    public Collection $posts;
    
    #[HasMany(target: Comment::class)]
    public Collection $comments;
}

// Usage
$users = User::all();
foreach ($users as $user) {
    echo $user->posts_count;
    echo $user->comments_count;
}
```

### WithExists Attribute

Check relationship existence:

```php
<?php

use Neo\Metadata\Attributes\WithExists;

#[WithExists(['subscription', 'profile'])]
class User extends Model
{
    #[HasOne(target: Subscription::class)]
    public ?Subscription $subscription;
    
    #[HasOne(target: Profile::class)]
    public ?Profile $profile;
}

// Usage
$users = User::all();
foreach ($users as $user) {
    if ($user->subscription_exists) {
        echo "Has subscription";
    }
}
```

## Relationship Caching

### CachedRelation Attribute

Cache relationship queries:

```php
<?php

use Neo\Metadata\Attributes\CachedRelation;

class User extends Model
{
    #[HasMany(target: Post::class)]
    #[CachedRelation(ttl: 3600)]
    public Collection $posts;
    
    #[BelongsToMany(target: Role::class)]
    #[CachedRelation(
        ttl: 86400,
        tags: ['user-roles']
    )]
    public Collection $roles;
}

// First call queries database
$posts = $user->posts;

// Subsequent calls use cache
$posts = $user->posts;  // From cache

// Clear relationship cache
$user->clearRelationshipCache('posts');
```

## Advanced Relationship Features

### Touching Parent Timestamps

```php
<?php

use Neo\Metadata\Attributes\TouchesParent;

class Comment extends Model
{
    #[BelongsTo(target: Post::class)]
    #[TouchesParent]
    public Post $post;
}

// When comment is saved, post's updated_at is touched
$comment->content = 'Updated';
$comment->save();  // Also updates post.updated_at
```

### Relationship Events

```php
<?php

use Neo\Metadata\Attributes\RelationshipEvents;

class User extends Model
{
    #[HasMany(target: Post::class)]
    #[RelationshipEvents(
        attaching: [PostObserver::class, 'onAttaching'],
        attached: [PostObserver::class, 'onAttached'],
        detaching: [PostObserver::class, 'onDetaching'],
        detached: [PostObserver::class, 'onDetached']
    )]
    public Collection $posts;
}
```

### Custom Relationship Methods

```php
<?php

use Neo\Metadata\Attributes\RelationshipMethod;

class User extends Model
{
    #[HasMany(target: Post::class)]
    public Collection $posts;
    
    #[RelationshipMethod('posts')]
    public function publishedPosts()
    {
        return $this->posts()->where('status', 'published');
    }
    
    #[RelationshipMethod('posts')]
    public function recentPosts(int $limit = 10)
    {
        return $this->posts()
            ->orderBy('created_at', 'desc')
            ->limit($limit);
    }
}

// Usage
$publishedPosts = $user->publishedPosts()->get();
$recentPosts = $user->recentPosts(5)->get();
```

## Relationship Validation

```php
<?php

use Neo\Metadata\Attributes\ValidateRelation;

class Post extends Model
{
    #[BelongsTo(target: User::class)]
    #[ValidateRelation(rules: 'required|exists:users,id')]
    public User $author;
    
    #[BelongsTo(target: Category::class)]
    #[ValidateRelation(rules: 'required|exists:categories,id')]
    public Category $category;
}
```

## Complete Example

```php
<?php

namespace App\Models;

use Neo\Database\Model;
use Neo\Metadata\Attributes\*;

#[Table(name: 'users')]
#[Timestamps]
#[SoftDeletes]
#[EagerLoad(['profile', 'roles'])]
#[WithCount(['posts', 'comments'])]
class User extends Model
{
    #[Column(type: 'integer', autoIncrement: true)]
    #[PrimaryKey]
    public int $id;
    
    #[Column(type: 'string', length: 255)]
    public string $name;
    
    #[Column(type: 'string', length: 255)]
    #[Unique]
    public string $email;
    
    // One-to-One
    #[HasOne(
        target: Profile::class,
        foreignKey: 'user_id'
    )]
    public ?Profile $profile;
    
    // One-to-Many
    #[HasMany(
        target: Post::class,
        foreignKey: 'user_id',
        orderBy: ['created_at' => 'desc']
    )]
    #[CachedRelation(ttl: 1800)]
    public Collection $posts;
    
    #[HasMany(
        target: Comment::class,
        foreignKey: 'user_id'
    )]
    public Collection $comments;
    
    // Many-to-Many
    #[BelongsToMany(
        target: Role::class,
        pivotTable: 'role_user',
        foreignKey: 'user_id',
        relatedKey: 'role_id',
        withTimestamps: true
    )]
    #[CachedRelation(ttl: 86400, tags: ['user-roles'])]
    public Collection $roles;
    
    #[BelongsToMany(
        target: Team::class,
        pivotTable: 'team_user',
        foreignKey: 'user_id',
        relatedKey: 'team_id',
        pivotColumns: ['role', 'joined_at']
    )]
    public Collection $teams;
    
    // Polymorphic
    #[MorphMany(
        target: Image::class,
        name: 'imageable'
    )]
    public Collection $images;
}

#[Table(name: 'posts')]
#[Timestamps]
#[EagerLoad(['author', 'category', 'tags'])]
class Post extends Model
{
    #[Column(type: 'integer', autoIncrement: true)]
    #[PrimaryKey]
    public int $id;
    
    #[Column(type: 'string', length: 255)]
    public string $title;
    
    #[Column(type: 'text')]
    public string $content;
    
    #[Column(type: 'integer')]
    #[ForeignKey(references: 'id', on: User::class)]
    public int $user_id;
    
    #[Column(type: 'integer')]
    #[ForeignKey(references: 'id', on: Category::class)]
    public int $category_id;
    
    // Belongs To
    #[BelongsTo(
        target: User::class,
        foreignKey: 'user_id',
        relation: 'author'
    )]
    public User $author;
    
    #[BelongsTo(
        target: Category::class,
        foreignKey: 'category_id'
    )]
    public Category $category;
    
    // Has Many
    #[HasMany(
        target: Comment::class,
        foreignKey: 'post_id'
    )]
    public Collection $comments;
    
    // Many to Many
    #[BelongsToMany(
        target: Tag::class,
        pivotTable: 'post_tag',
        foreignKey: 'post_id',
        relatedKey: 'tag_id'
    )]
    public Collection $tags;
}
```

## Best Practices

1. **Use Type Declarations**: Always type-hint relationship properties
2. **Eager Load Strategically**: Use `#[EagerLoad]` to prevent N+1 queries
3. **Cache Stable Relationships**: Cache relationships that change infrequently
4. **Name Relationships Clearly**: Use descriptive relationship names
5. **Document Complex Relationships**: Add comments for intricate relationship logic
6. **Validate Foreign Keys**: Always validate relationship keys
7. **Touch Parent Timestamps**: Use `#[TouchesParent]` for denormalization
8. **Use Relationship Counts**: Prefer `#[WithCount]` over manual counting

## Next Steps

- Learn about [Form Generation](form-generation.md)
- Explore [Validation](validation.md)
- Return to [Metadata Introduction](introduction.md)
