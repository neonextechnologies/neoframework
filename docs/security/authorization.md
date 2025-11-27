# Authorization

## Introduction

In addition to providing authentication services, NeoFramework provides a simple way to authorize user actions against resources. Authorization in NeoFramework is primarily done via gates and policies.

Think of gates and policies like routes and controllers. Gates provide a simple, closure-based approach to authorization, while policies group authorization logic around a particular model or resource.

## Gates

### Defining Gates

Gates are closures that determine if a user is authorized to perform a given action. Gates are defined in the `boot` method of the `AuthServiceProvider`:

```php
<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\User;
use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('update-post', function (User $user, Post $post) {
            return $user->id === $post->user_id;
        });
        
        Gate::define('delete-post', function (User $user, Post $post) {
            return $user->id === $post->user_id;
        });
        
        Gate::define('publish-post', function (User $user) {
            return $user->is_admin;
        });
    }
}
```

### Authorizing Actions with Gates

```php
if (Gate::allows('update-post', $post)) {
    // User can update the post
}

if (Gate::denies('update-post', $post)) {
    // User cannot update the post
}

// Check for any ability
if (Gate::any(['update-post', 'delete-post'], $post)) {
    // User can either update or delete
}

// Check for all abilities
if (Gate::all(['update-post', 'publish-post'], $post)) {
    // User can both update and publish
}
```

### Authorizing or Throwing Exception

```php
Gate::authorize('update-post', $post);
// Throws AuthorizationException if user cannot update

Gate::inspect('update-post', $post);
// Returns Response object with detailed info
```

### Gates Without Models

```php
Gate::define('view-dashboard', function (User $user) {
    return $user->is_admin;
});

if (Gate::allows('view-dashboard')) {
    // User can view dashboard
}
```

### Before & After Hooks

```php
Gate::before(function (User $user, string $ability) {
    if ($user->is_super_admin) {
        return true; // Super admin can do anything
    }
});

Gate::after(function (User $user, string $ability, $result) {
    if ($user->is_super_admin) {
        return true;
    }
});
```

## Policies

Policies are classes that organize authorization logic around a particular model or resource.

### Creating Policies

```bash
php neo make:policy PostPolicy
php neo make:policy PostPolicy --model=Post
```

This creates `app/Policies/PostPolicy.php`:

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine if the user can view any posts.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the post.
     */
    public function view(?User $user, Post $post): bool
    {
        return $post->is_published || $user?->id === $post->user_id;
    }

    /**
     * Determine if the user can create posts.
     */
    public function create(User $user): bool
    {
        return $user->is_verified;
    }

    /**
     * Determine if the user can update the post.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * Determine if the user can delete the post.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * Determine if the user can restore the post.
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * Determine if the user can permanently delete the post.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->is_admin;
    }
}
```

### Registering Policies

Register policies in `AuthServiceProvider`:

```php
<?php

namespace App\Providers;

use App\Models\Post;
use App\Policies\PostPolicy;
use NeoPhp\Foundation\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected array $policies = [
        Post::class => PostPolicy::class,
    ];

    public function boot(): void
    {
        Gate::guessPolicyNamesUsing(function ($modelClass) {
            return 'App\\Policies\\' . class_basename($modelClass) . 'Policy';
        });
    }
}
```

### Authorizing Actions via Models

```php
if ($user->can('update', $post)) {
    // User can update the post
}

if ($user->cannot('update', $post)) {
    // User cannot update the post
}
```

### Authorizing in Controllers

```php
public function update(Request $request, Post $post)
{
    $this->authorize('update', $post);
    
    // User is authorized...
    
    $post->update($request->validated());
    
    return redirect()->route('posts.show', $post);
}
```

### Authorizing Resource Controllers

```php
public function __construct()
{
    $this->authorizeResource(Post::class, 'post');
}
```

This automatically authorizes:
- `index` → `viewAny`
- `show` → `view`
- `create` → `create`
- `store` → `create`
- `edit` → `update`
- `update` → `update`
- `destroy` → `delete`

### Policy Filters

```php
class PostPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_admin) {
            return true; // Admin can do anything
        }

        return null; // Continue to other checks
    }
}
```

## Authorizing in Blade Views

### Using @can Directive

```php
@can('update', $post)
    <a href="<?= route('posts.edit', $post) ?>">Edit</a>
@endcan

@cannot('update', $post)
    <p>You cannot edit this post</p>
@endcannot

@canany(['update', 'delete'], $post)
    <!-- User can update or delete -->
@endcanany
```

### Without Models

```php
@can('create-post')
    <a href="<?= route('posts.create') ?>">Create Post</a>
@endcan
```

## Practical Examples

### Example 1: Blog Authorization

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Anyone can view published posts.
     */
    public function view(?User $user, Post $post): bool
    {
        // Published posts are public
        if ($post->is_published) {
            return true;
        }

        // Authors can view their own drafts
        return $user && $user->id === $post->user_id;
    }

    /**
     * Only verified users can create posts.
     */
    public function create(User $user): bool
    {
        return $user->email_verified_at !== null;
    }

    /**
     * Authors and editors can update posts.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->hasRole('editor');
    }

    /**
     * Only authors can delete their posts.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * Only admins can publish posts.
     */
    public function publish(User $user, Post $post): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Only admins can feature posts.
     */
    public function feature(User $user, Post $post): bool
    {
        return $user->hasRole('admin');
    }
}
```

Controller usage:

```php
public function update(Request $request, Post $post)
{
    $this->authorize('update', $post);
    
    $post->update($request->validated());
    
    return redirect()->route('posts.show', $post);
}

public function publish(Post $post)
{
    $this->authorize('publish', $post);
    
    $post->update(['is_published' => true, 'published_at' => now()]);
    
    return back()->with('success', 'Post published!');
}
```

### Example 2: Team-Based Authorization

```php
<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Users can only view projects in their teams.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->teams->contains($project->team_id);
    }

    /**
     * Team owners can create projects.
     */
    public function create(User $user): bool
    {
        return $user->ownedTeams()->exists();
    }

    /**
     * Project owners and team admins can update.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->user_id 
            || $user->isTeamAdmin($project->team_id);
    }

    /**
     * Only project owners can delete.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * Team members can add tasks.
     */
    public function addTask(User $user, Project $project): bool
    {
        return $project->team->members->contains($user);
    }
}
```

### Example 3: Subscription-Based Authorization

```php
<?php

namespace App\Policies;

use App\Models\User;

class FeaturePolicy
{
    /**
     * Check if user can export data.
     */
    public function exportData(User $user): bool
    {
        return $user->subscription?->plan === 'premium';
    }

    /**
     * Check if user can create API keys.
     */
    public function createApiKey(User $user): bool
    {
        $plan = $user->subscription?->plan;
        
        return in_array($plan, ['pro', 'premium']);
    }

    /**
     * Check if user can access analytics.
     */
    public function viewAnalytics(User $user): bool
    {
        return $user->hasActiveSubscription();
    }

    /**
     * Check if user can add team members.
     */
    public function addTeamMembers(User $user): bool
    {
        $subscription = $user->subscription;
        
        if (!$subscription) {
            return false;
        }

        if ($subscription->plan === 'premium') {
            return true; // Unlimited
        }

        if ($subscription->plan === 'pro') {
            return $user->team->members->count() < 10;
        }

        return false;
    }
}
```

### Example 4: Role-Based Authorization

```php
<?php

namespace App\Providers;

use App\Models\User;
use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Super admin can do everything
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });

        // Admin gates
        Gate::define('manage-users', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('manage-settings', function (User $user) {
            return $user->hasRole('admin');
        });

        // Editor gates
        Gate::define('publish-posts', function (User $user) {
            return $user->hasAnyRole(['admin', 'editor']);
        });

        Gate::define('moderate-comments', function (User $user) {
            return $user->hasAnyRole(['admin', 'editor', 'moderator']);
        });

        // Permission-based gates
        Gate::define('delete-users', function (User $user) {
            return $user->hasPermission('delete-users');
        });
    }
}
```

## Middleware Authorization

### Authorize Middleware

```php
Route::put('/posts/{post}', function (Post $post) {
    // User can update the post
})->middleware('can:update,post');
```

### Multiple Abilities

```php
Route::delete('/posts/{post}', function (Post $post) {
    // User can delete or force delete
})->middleware('can:delete,post|force-delete,post');
```

## Guest Users

Allow guest users to pass through policies:

```php
public function view(?User $user, Post $post): bool
{
    if ($post->is_published) {
        return true; // Anyone can view
    }

    return $user && $user->id === $post->user_id;
}
```

## Best Practices

### 1. Use Policies for Model Authorization

```php
// Good
$this->authorize('update', $post);

// Bad
if ($user->id !== $post->user_id) {
    abort(403);
}
```

### 2. Use Gates for Simple Checks

```php
Gate::define('view-dashboard', function (User $user) {
    return $user->is_admin;
});
```

### 3. Implement Before Hook for Admins

```php
public function before(User $user, string $ability): ?bool
{
    if ($user->is_admin) {
        return true;
    }
}
```

### 4. Type-hint Optional Users

```php
public function view(?User $user, Post $post): bool
{
    // Allows guest access
}
```

### 5. Return Boolean Values

```php
public function update(User $user, Post $post): bool
{
    return $user->id === $post->user_id;
}
```

## Testing Authorization

```php
class PostPolicyTest extends TestCase
{
    public function test_author_can_update_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        
        $this->assertTrue($user->can('update', $post));
    }
    
    public function test_non_author_cannot_update_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        
        $this->assertFalse($user->can('update', $post));
    }
    
    public function test_admin_can_delete_any_post()
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create();
        
        $this->assertTrue($admin->can('delete', $post));
    }
}
```

## Next Steps

- [Authentication](authentication.md) - User authentication
- [Policies](policies.md) - Deep dive into policies
- [Gates](gates.md) - Advanced gate usage
- [Middleware](../basics/middleware.md) - Authorization middleware
