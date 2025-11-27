# Views 👁️

## Introduction

NeoFramework's view layer provides a clean and powerful way to render HTML and other output formats. The view engine supports plain PHP templates, template inheritance, layouts, sections, and components, giving you the flexibility to build beautiful user interfaces.

Views separate your presentation logic from your business logic and are typically stored in the `resources/views` directory. They allow you to write clean, maintainable HTML with embedded PHP for dynamic content.

## Basic Usage

### Creating Views

Views are PHP files stored in `resources/views/`:

```php
<!-- resources/views/welcome.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
</head>
<body>
    <h1>Welcome to <?= $appName ?></h1>
    <p><?= $message ?></p>
</body>
</html>
```

### Rendering Views

Return a view from your controller:

```php
<?php

namespace App\Controllers;

use NeoPhp\Http\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome', [
            'appName' => 'NeoFramework',
            'message' => 'Build amazing applications!'
        ]);
    }
}
```

### Passing Data to Views

There are multiple ways to pass data to views:

```php
// Using an array
return view('profile', ['user' => $user]);

// Using with() method
return view('profile')->with('user', $user);

// Chaining multiple with() calls
return view('profile')
    ->with('user', $user)
    ->with('posts', $posts);

// Using compact()
$user = User::find(1);
$posts = Post::latest()->get();
return view('profile', compact('user', 'posts'));
```

### Sharing Data With All Views

Share data globally across all views using a service provider:

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Share with all views
        view()->share('appName', config('app.name'));
        view()->share('appVersion', config('app.version'));
        
        // Share authenticated user
        if (auth()->check()) {
            view()->share('currentUser', auth()->user());
        }
    }
}
```

## View Helpers 🛠️

### The view() Helper

The global `view()` helper creates view instances:

```php
// Simple view
view('welcome');

// With data
view('profile', ['user' => $user]);

// Get the View instance
$view = view('welcome');
$view->with('data', $value);
return $view;
```

### The e() Helper

The `e()` helper escapes HTML entities to prevent XSS:

```php
<h1><?= e($userInput) ?></h1>

<!-- Equivalent to -->
<h1><?= htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8') ?></h1>
```

Always escape user input:

```php
<!-- Safe -->
<div class="comment">
    <?= e($comment->body) ?>
</div>

<!-- Unsafe - XSS vulnerability! -->
<div class="comment">
    <?= $comment->body ?>
</div>
```

## Layouts & Template Inheritance 📐

### Creating a Layout

Create a base layout that other views can extend:

```php
<!-- resources/views/layouts/app.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'NeoFramework' ?></title>
    <link rel="stylesheet" href="/css/app.css">
    <?php $this->yield('styles') ?>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="/" class="brand">NeoFramework</a>
            <ul class="nav-links">
                <li><a href="/about">About</a></li>
                <li><a href="/contact">Contact</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <?= $content ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> NeoFramework</p>
    </footer>

    <script src="/js/app.js"></script>
    <?php $this->yield('scripts') ?>
</body>
</html>
```

### Extending Layouts

Use layouts in your views:

```php
<!-- resources/views/home.php -->
<?php $this->layout('layouts/app') ?>

<h1>Welcome Home</h1>
<p>This is the home page content.</p>

<div class="features">
    <div class="feature">
        <h3>Fast</h3>
        <p>Lightning fast performance</p>
    </div>
    <div class="feature">
        <h3>Secure</h3>
        <p>Built-in security features</p>
    </div>
    <div class="feature">
        <h3>Modern</h3>
        <p>Modern PHP 8+ features</p>
    </div>
</div>
```

### Using Sections

Define sections in child views:

```php
<!-- resources/views/dashboard.php -->
<?php $this->layout('layouts/app') ?>

<?php $this->section('styles') ?>
<link rel="stylesheet" href="/css/dashboard.css">
<style>
    .dashboard { background: #f5f5f5; }
</style>
<?php $this->endSection() ?>

<div class="dashboard">
    <h1>Dashboard</h1>
    
    <div class="stats">
        <div class="stat">
            <h3><?= $userCount ?></h3>
            <p>Total Users</p>
        </div>
        <div class="stat">
            <h3><?= $postCount ?></h3>
            <p>Total Posts</p>
        </div>
    </div>
</div>

<?php $this->section('scripts') ?>
<script src="/js/dashboard.js"></script>
<script>
    Dashboard.init();
</script>
<?php $this->endSection() ?>
```

## Including Subviews 🧩

### Basic Includes

Include other views within a view:

```php
<!-- resources/views/blog/post.php -->
<article class="post">
    <?php $this->include('partials/post-header', ['post' => $post]) ?>
    
    <div class="post-content">
        <?= $post->content ?>
    </div>
    
    <?php $this->include('partials/post-footer', ['post' => $post]) ?>
    
    <?php $this->include('partials/comments', ['comments' => $post->comments]) ?>
</article>

<!-- resources/views/partials/post-header.php -->
<header>
    <h1><?= e($post->title) ?></h1>
    <div class="meta">
        <span class="author"><?= e($post->author->name) ?></span>
        <span class="date"><?= $post->published_at->format('M d, Y') ?></span>
    </div>
</header>
```

### Conditional Includes

Include views conditionally:

```php
<?php if (auth()->check()): ?>
    <?php $this->include('partials/user-menu') ?>
<?php else: ?>
    <?php $this->include('partials/guest-menu') ?>
<?php endif ?>
```

## Components 🎨

### Creating Reusable Components

Create reusable view components:

```php
<!-- resources/views/components/alert.php -->
<div class="alert alert-<?= $type ?? 'info' ?> <?= $dismissible ? 'alert-dismissible' : '' ?>">
    <?php if ($dismissible): ?>
        <button type="button" class="close" data-dismiss="alert">×</button>
    <?php endif ?>
    
    <?php if (isset($title)): ?>
        <h4 class="alert-heading"><?= e($title) ?></h4>
    <?php endif ?>
    
    <div class="alert-body">
        <?= $message ?>
    </div>
</div>
```

### Using Components

```php
<!-- In your view -->
<?php $this->include('components/alert', [
    'type' => 'success',
    'title' => 'Success!',
    'message' => 'Your profile has been updated.',
    'dismissible' => true
]) ?>

<?php $this->include('components/alert', [
    'type' => 'danger',
    'message' => 'An error occurred. Please try again.',
    'dismissible' => false
]) ?>
```

### Card Component Example

```php
<!-- resources/views/components/card.php -->
<div class="card <?= $class ?? '' ?>">
    <?php if (isset($header)): ?>
        <div class="card-header">
            <?= $header ?>
        </div>
    <?php endif ?>
    
    <div class="card-body">
        <?= $content ?>
    </div>
    
    <?php if (isset($footer)): ?>
        <div class="card-footer">
            <?= $footer ?>
        </div>
    <?php endif ?>
</div>

<!-- Usage -->
<?php $this->include('components/card', [
    'header' => '<h3>User Profile</h3>',
    'content' => view('users/profile-content', ['user' => $user])->render(),
    'footer' => '<a href="/users/' . $user->id . '/edit">Edit Profile</a>',
    'class' => 'user-card'
]) ?>
```

## View Composers 🎼

View composers allow you to bind data to views whenever they're rendered:

### Creating a View Composer

```php
<?php

namespace App\View\Composers;

class NavigationComposer
{
    public function compose($view): void
    {
        $view->with('navItems', [
            ['title' => 'Home', 'url' => '/'],
            ['title' => 'About', 'url' => '/about'],
            ['title' => 'Services', 'url' => '/services'],
            ['title' => 'Contact', 'url' => '/contact'],
        ]);
    }
}
```

### Registering View Composers

Register composers in a service provider:

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use App\View\Composers\NavigationComposer;
use App\View\Composers\SidebarComposer;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Composer for specific view
        view()->composer('layouts/app', NavigationComposer::class);
        
        // Composer for multiple views
        view()->composer(
            ['dashboard', 'admin.*'],
            SidebarComposer::class
        );
        
        // Closure-based composer
        view()->composer('partials/footer', function ($view) {
            $view->with('year', date('Y'));
            $view->with('version', config('app.version'));
        });
        
        // Wildcard composer
        view()->composer('*', function ($view) {
            $view->with('currentUrl', request()->url());
        });
    }
}
```

### Advanced Composer Example

```php
<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Post;
use NeoPhp\Cache\CacheManager;

class BlogSidebarComposer
{
    public function __construct(
        protected CacheManager $cache
    ) {}
    
    public function compose($view): void
    {
        // Cache sidebar data for 1 hour
        $sidebarData = $this->cache->remember('blog.sidebar', 3600, function () {
            return [
                'categories' => Category::withCount('posts')
                    ->orderBy('name')
                    ->get(),
                'popularPosts' => Post::published()
                    ->orderByDesc('views')
                    ->take(5)
                    ->get(),
                'recentPosts' => Post::published()
                    ->latest()
                    ->take(5)
                    ->get(),
                'tags' => $this->getPopularTags(),
            ];
        });
        
        $view->with($sidebarData);
    }
    
    protected function getPopularTags(): array
    {
        return Post::published()
            ->get()
            ->pluck('tags')
            ->flatten()
            ->countBy()
            ->sortDesc()
            ->take(20)
            ->keys()
            ->toArray();
    }
}
```

## Real-World Examples 🌍

### Blog Post View

```php
<!-- resources/views/blog/show.php -->
<?php $this->layout('layouts/app') ?>

<?php $this->section('styles') ?>
<link rel="stylesheet" href="/css/blog.css">
<?php $this->endSection() ?>

<article class="blog-post">
    <header class="post-header">
        <h1><?= e($post->title) ?></h1>
        
        <div class="post-meta">
            <img src="<?= e($post->author->avatar) ?>" alt="<?= e($post->author->name) ?>">
            <div>
                <p class="author">By <?= e($post->author->name) ?></p>
                <p class="date"><?= $post->published_at->format('F j, Y') ?></p>
            </div>
        </div>
        
        <?php if (!empty($post->tags)): ?>
            <div class="tags">
                <?php foreach ($post->tags as $tag): ?>
                    <a href="/blog/tag/<?= urlencode($tag) ?>" class="tag">
                        <?= e($tag) ?>
                    </a>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </header>
    
    <?php if ($post->featured_image): ?>
        <div class="featured-image">
            <img src="<?= e($post->featured_image) ?>" alt="<?= e($post->title) ?>">
        </div>
    <?php endif ?>
    
    <div class="post-content">
        <?= $post->content ?>
    </div>
    
    <?php if ($post->author->bio): ?>
        <div class="author-bio">
            <h3>About the Author</h3>
            <img src="<?= e($post->author->avatar) ?>" alt="<?= e($post->author->name) ?>">
            <div>
                <h4><?= e($post->author->name) ?></h4>
                <p><?= e($post->author->bio) ?></p>
            </div>
        </div>
    <?php endif ?>
    
    <?php $this->include('blog/partials/share-buttons', ['post' => $post]) ?>
    
    <?php if ($relatedPosts->count() > 0): ?>
        <?php $this->include('blog/partials/related-posts', ['posts' => $relatedPosts]) ?>
    <?php endif ?>
    
    <?php $this->include('blog/partials/comments', ['post' => $post]) ?>
</article>

<?php $this->section('scripts') ?>
<script src="/js/blog.js"></script>
<?php $this->endSection() ?>
```

### User Dashboard

```php
<!-- resources/views/dashboard/index.php -->
<?php $this->layout('layouts/dashboard') ?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1>Welcome back, <?= e($user->name) ?>!</h1>
        <p class="subtitle">Here's what's happening with your account</p>
    </div>
    
    <div class="stats-grid">
        <?php 
        $stats = [
            ['label' => 'Total Posts', 'value' => $postCount, 'icon' => 'file-text', 'color' => 'blue'],
            ['label' => 'Comments', 'value' => $commentCount, 'icon' => 'message-circle', 'color' => 'green'],
            ['label' => 'Views', 'value' => number_format($totalViews), 'icon' => 'eye', 'color' => 'purple'],
            ['label' => 'Followers', 'value' => $followerCount, 'icon' => 'users', 'color' => 'orange'],
        ];
        ?>
        
        <?php foreach ($stats as $stat): ?>
            <?php $this->include('dashboard/components/stat-card', ['stat' => $stat]) ?>
        <?php endforeach ?>
    </div>
    
    <div class="dashboard-grid">
        <div class="dashboard-col-8">
            <?php $this->include('dashboard/sections/recent-posts', ['posts' => $recentPosts]) ?>
            <?php $this->include('dashboard/sections/activity-feed', ['activities' => $activities]) ?>
        </div>
        
        <div class="dashboard-col-4">
            <?php $this->include('dashboard/sections/quick-actions') ?>
            <?php $this->include('dashboard/sections/notifications', ['notifications' => $notifications]) ?>
        </div>
    </div>
</div>
```

### Form with Validation Errors

```php
<!-- resources/views/users/edit.php -->
<?php $this->layout('layouts/app') ?>

<div class="container">
    <h1>Edit Profile</h1>
    
    <?php if (session('success')): ?>
        <?php $this->include('components/alert', [
            'type' => 'success',
            'message' => session('success'),
            'dismissible' => true
        ]) ?>
    <?php endif ?>
    
    <?php if (session('error')): ?>
        <?php $this->include('components/alert', [
            'type' => 'danger',
            'message' => session('error'),
            'dismissible' => true
        ]) ?>
    <?php endif ?>
    
    <form action="/users/<?= $user->id ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
        
        <div class="form-group">
            <label for="name">Name</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="<?= e(old('name', $user->name)) ?>"
                class="<?= $errors->has('name') ? 'is-invalid' : '' ?>"
            >
            <?php if ($errors->has('name')): ?>
                <div class="invalid-feedback">
                    <?= e($errors->first('name')) ?>
                </div>
            <?php endif ?>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="<?= e(old('email', $user->email)) ?>"
                class="<?= $errors->has('email') ? 'is-invalid' : '' ?>"
            >
            <?php if ($errors->has('email')): ?>
                <div class="invalid-feedback">
                    <?= e($errors->first('email')) ?>
                </div>
            <?php endif ?>
        </div>
        
        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea 
                id="bio" 
                name="bio" 
                rows="5"
                class="<?= $errors->has('bio') ? 'is-invalid' : '' ?>"
            ><?= e(old('bio', $user->bio)) ?></textarea>
            <?php if ($errors->has('bio')): ?>
                <div class="invalid-feedback">
                    <?= e($errors->first('bio')) ?>
                </div>
            <?php endif ?>
        </div>
        
        <div class="form-group">
            <label for="avatar">Profile Picture</label>
            <?php if ($user->avatar): ?>
                <img src="<?= e($user->avatar) ?>" alt="Current avatar" class="avatar-preview">
            <?php endif ?>
            <input 
                type="file" 
                id="avatar" 
                name="avatar"
                accept="image/*"
                class="<?= $errors->has('avatar') ? 'is-invalid' : '' ?>"
            >
            <?php if ($errors->has('avatar')): ?>
                <div class="invalid-feedback">
                    <?= e($errors->first('avatar')) ?>
                </div>
            <?php endif ?>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Profile</button>
            <a href="/users/<?= $user->id ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
```

### Data Table

```php
<!-- resources/views/users/index.php -->
<?php $this->layout('layouts/app') ?>

<div class="container">
    <div class="page-header">
        <h1>Users</h1>
        <a href="/users/create" class="btn btn-primary">Add User</a>
    </div>
    
    <?php if (session('success')): ?>
        <?php $this->include('components/alert', [
            'type' => 'success',
            'message' => session('success'),
            'dismissible' => true
        ]) ?>
    <?php endif ?>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users->isEmpty()): ?>
                    <tr>
                        <td colspan="7" class="text-center">No users found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user->id ?></td>
                            <td>
                                <div class="user-info">
                                    <img src="<?= e($user->avatar) ?>" alt="<?= e($user->name) ?>" class="avatar-sm">
                                    <?= e($user->name) ?>
                                </div>
                            </td>
                            <td><?= e($user->email) ?></td>
                            <td>
                                <span class="badge badge-<?= $user->role->color ?>">
                                    <?= e($user->role->name) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= $user->status === 'active' ? 'success' : 'danger' ?>">
                                    <?= e($user->status) ?>
                                </span>
                            </td>
                            <td><?= $user->created_at->format('M d, Y') ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="/users/<?= $user->id ?>" class="btn btn-sm btn-info">View</a>
                                    <a href="/users/<?= $user->id ?>/edit" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="/users/<?= $user->id ?>" method="POST" style="display:inline">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($users->hasPages()): ?>
        <?php $this->include('components/pagination', ['paginator' => $users]) ?>
    <?php endif ?>
</div>
```

## Best Practices 📋

### 1. Always Escape Output

```php
<!-- Good: Escaped output -->
<h1><?= e($title) ?></h1>
<p><?= e($user->name) ?></p>

<!-- Bad: Unescaped output - XSS vulnerability! -->
<h1><?= $title ?></h1>
<p><?= $user->name ?></p>
```

### 2. Keep Views Simple

```php
<!-- Good: Simple view logic -->
<?php if (auth()->check()): ?>
    <p>Welcome, <?= e(auth()->user()->name) ?></p>
<?php endif ?>

<!-- Bad: Complex business logic in view -->
<?php
$users = DB::table('users')
    ->where('active', 1)
    ->where('created_at', '>', now()->subDays(30))
    ->orderBy('name')
    ->get();
// Too much logic!
?>
```

### 3. Use Layouts for Common Structure

```php
<!-- Good: DRY with layouts -->
<?php $this->layout('layouts/app') ?>
<h1>Page Content</h1>

<!-- Bad: Repeating HTML in every view -->
<!DOCTYPE html>
<html>
<head>...</head>
<body>
    <nav>...</nav>
    <h1>Page Content</h1>
    <footer>...</footer>
</body>
</html>
```

### 4. Component Reusability

```php
<!-- Create reusable components -->
<?php $this->include('components/button', [
    'text' => 'Save',
    'type' => 'submit',
    'class' => 'btn-primary'
]) ?>

<!-- Instead of repeating HTML -->
<button type="submit" class="btn btn-primary">Save</button>
```

### 5. Use View Composers for Common Data

```php
// Good: View composer
view()->composer('layouts/app', function ($view) {
    $view->with('notifications', auth()->user()->unreadNotifications);
});

// Bad: Passing in every controller
public function index() {
    return view('page', [
        'notifications' => auth()->user()->unreadNotifications,
        // Repeated in every action
    ]);
}
```

## Testing Views 🧪

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class ViewTest extends TestCase
{
    public function test_home_view_renders_correctly(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('Welcome');
        $response->assertSee('NeoFramework');
    }
    
    public function test_user_profile_displays_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        $response = $this->actingAs($user)->get("/users/{$user->id}");
        
        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
    }
    
    public function test_view_escapes_html(): void
    {
        $user = User::factory()->create([
            'name' => '<script>alert("XSS")</script>',
        ]);
        
        $response = $this->get("/users/{$user->id}");
        
        $response->assertDontSee('<script>', false);
        $response->assertSee('&lt;script&gt;');
    }
}
```

## Performance Tips ⚡

### 1. Cache Expensive View Composers

```php
view()->composer('sidebar', function ($view) {
    $view->with('data', Cache::remember('sidebar', 3600, function () {
        return expensive_operation();
    }));
});
```

### 2. Use Fragment Caching

```php
<?php if (!$cached = cache("fragment.posts.{$userId}")): ?>
    <?php ob_start() ?>
    <!-- Expensive content generation -->
    <?php foreach ($posts as $post): ?>
        <?php $this->include('partials/post-card', ['post' => $post]) ?>
    <?php endforeach ?>
    <?php 
    $cached = ob_get_clean();
    cache(["fragment.posts.{$userId}" => $cached], 3600);
    endif 
    ?>
<?= $cached ?>
```

### 3. Minimize Includes in Loops

```php
<!-- Good: Minimize includes -->
<div class="posts">
    <?php foreach ($posts as $post): ?>
        <article>
            <h2><?= e($post->title) ?></h2>
            <p><?= e($post->excerpt) ?></p>
        </article>
    <?php endforeach ?>
</div>

<!-- Avoid: Include in every loop iteration -->
<?php foreach ($posts as $post): ?>
    <?php $this->include('partials/post-card', ['post' => $post]) ?>
<?php endforeach ?>
```

## Related Documentation

- [Controllers](controllers.md) - Returning views from controllers
- [Responses](responses.md) - HTTP responses and views
- [Validation](validation.md) - Displaying validation errors
- [Blade Templating](../advanced/blade.md) - Advanced template engine

## Next Steps

Master view development with these resources:

1. **[Controllers](controllers.md)** - Learn controller basics
2. **[Responses](responses.md)** - HTTP response types
3. **[Middleware](middleware.md)** - Request filtering
4. **[Validation](validation.md)** - Form validation
