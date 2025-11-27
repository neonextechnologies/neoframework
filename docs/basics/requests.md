# Requests

## Introduction

NeoFramework's `Request` class provides an object-oriented way to interact with the current HTTP request being handled by your application, as well as retrieve the input, cookies, and files submitted with the request.

## Accessing the Request

### Dependency Injection

To obtain an instance of the current HTTP request via dependency injection, type-hint the `Request` class on your controller method:

```php
<?php

namespace App\Controllers;

use NeoPhp\Http\Request;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $name = $request->input('name');
        
        // ...
    }
}
```

### Request Path & Method

```php
// Get request path
$uri = $request->path();
// /posts/123

// Get full URL
$url = $request->url();
// http://example.com/posts/123

$url = $request->fullUrl();
// http://example.com/posts/123?page=1

// Get request method
$method = $request->method();
// GET, POST, PUT, DELETE

// Check request method
if ($request->isMethod('post')) {
    // Handle POST request
}
```

## Retrieving Input

### All Input Data

```php
$input = $request->all();
```

### Specific Input Values

```php
$name = $request->input('name');

// With default value
$name = $request->input('name', 'Guest');

// Nested values
$email = $request->input('user.email');

// Array input
$names = $request->input('products.*.name');
```

### Query String Input

```php
$name = $request->query('name');

// With default
$name = $request->query('name', 'Guest');

// All query parameters
$query = $request->query();
```

### Dynamic Properties

```php
$name = $request->name;
$email = $request->email;
```

### JSON Input

```php
$name = $request->input('user.name');
// From JSON: {"user": {"name": "John"}}
```

### Retrieving Portion of Input

```php
$input = $request->only(['username', 'password']);

$input = $request->except(['credit_card']);
```

### Checking Input Presence

```php
if ($request->has('name')) {
    // Input exists
}

if ($request->has(['name', 'email'])) {
    // Both exist
}

if ($request->hasAny(['name', 'email'])) {
    // At least one exists
}

if ($request->filled('name')) {
    // Input exists and not empty
}

if ($request->missing('name')) {
    // Input doesn't exist
}
```

## Old Input

### Flashing Input to Session

```php
$request->flash();

$request->flashOnly(['username', 'email']);

$request->flashExcept('password');
```

### Flash Input then Redirect

```php
return redirect('form')->withInput();

return redirect('form')->withInput(
    $request->except('password')
);
```

### Retrieving Old Input

```php
$username = $request->old('username');
```

In views:

```php
<input type="text" name="username" value="<?= old('username') ?>">
```

## Cookies

### Retrieving Cookies

```php
$value = $request->cookie('name');

// With default
$value = $request->cookie('name', 'default');
```

## Files

### Retrieving Uploaded Files

```php
$file = $request->file('photo');

// Check if file exists
if ($request->hasFile('photo')) {
    // File was uploaded
}
```

### Validating Files

```php
if ($request->file('photo')->isValid()) {
    // File is valid
}
```

### File Information

```php
$file = $request->file('photo');

$path = $file->path();
$extension = $file->extension();
$size = $file->getSize();
$mimeType = $file->getMimeType();
$originalName = $file->getClientOriginalName();
```

### Storing Files

```php
$path = $request->file('photo')->store('photos');
// photos/abc123.jpg

// Specify disk
$path = $request->file('photo')->store('photos', 's3');

// Specify filename
$path = $request->file('photo')->storeAs('photos', 'profile.jpg');
```

## Headers

### Retrieving Headers

```php
$value = $request->header('X-Header-Name');

// With default
$value = $request->header('X-Header-Name', 'default');

// Check if header exists
if ($request->hasHeader('X-Header-Name')) {
    // Header exists
}
```

### Bearer Token

```php
$token = $request->bearerToken();
```

## Request Information

### IP Address

```php
$ip = $request->ip();
```

### User Agent

```php
$userAgent = $request->userAgent();
```

### Content Negotiation

```php
// Check if expects JSON
if ($request->expectsJson()) {
    return response()->json($data);
}

// Check if wants JSON
if ($request->wantsJson()) {
    return response()->json($data);
}

// Accepted content types
$accepts = $request->getAcceptableContentTypes();
```

### Request Segments

```php
// /users/123/posts
$segment = $request->segment(1); // users
$segment = $request->segment(2); // 123
```

## Practical Examples

### Example 1: Search & Filter

```php
public function index(Request $request)
{
    $query = Product::query();
    
    // Search
    if ($request->filled('search')) {
        $query->where('name', 'like', "%{$request->search}%");
    }
    
    // Category filter
    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }
    
    // Price range
    if ($request->filled('min_price')) {
        $query->where('price', '>=', $request->min_price);
    }
    
    if ($request->filled('max_price')) {
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
    
    return view('products.index', compact('products'));
}
```

### Example 2: File Upload

```php
public function upload(Request $request)
{
    $request->validate([
        'photo' => 'required|image|max:2048',
        'document' => 'required|file|mimes:pdf,doc,docx|max:5120',
    ]);
    
    // Store photo
    if ($request->hasFile('photo')) {
        $photo = $request->file('photo');
        
        $filename = time() . '.' . $photo->extension();
        $path = $photo->storeAs('photos', $filename, 'public');
        
        // Save to database
        $user->update(['photo' => $path]);
    }
    
    // Store document
    if ($request->hasFile('document')) {
        $document = $request->file('document');
        $path = $document->store('documents', 's3');
        
        Document::create([
            'user_id' => $user->id,
            'filename' => $document->getClientOriginalName(),
            'path' => $path,
            'size' => $document->getSize(),
        ]);
    }
    
    return back()->with('success', 'Files uploaded successfully!');
}
```

### Example 3: API Request Handling

```php
public function store(Request $request)
{
    // Validate JSON input
    $validated = $request->validate([
        'title' => 'required|max:255',
        'content' => 'required',
        'tags' => 'array',
        'tags.*' => 'string|max:50',
    ]);
    
    // Get authenticated user
    $user = $request->user();
    
    // Create post
    $post = $user->posts()->create([
        'title' => $validated['title'],
        'content' => $validated['content'],
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);
    
    // Attach tags
    if ($request->has('tags')) {
        foreach ($validated['tags'] as $tagName) {
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $post->tags()->attach($tag);
        }
    }
    
    return response()->json([
        'message' => 'Post created successfully',
        'post' => new PostResource($post),
    ], 201);
}
```

### Example 4: Form with Old Input

```php
public function create(Request $request)
{
    // If form was submitted with errors, data is available via old()
    $categories = Category::all();
    
    return view('posts.create', compact('categories'));
}

public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'content' => 'required',
        'category_id' => 'required|exists:categories,id',
    ]);
    
    // If validation fails, input is automatically flashed
    
    $post = Post::create($validated);
    
    return redirect()->route('posts.show', $post)
        ->with('success', 'Post created!');
}
```

View template:

```php
<form method="POST" action="<?= route('posts.store') ?>">
    <?= csrf_field() ?>
    
    <input type="text" 
           name="title" 
           value="<?= old('title') ?>"
           required>
    
    <textarea name="content" required><?= old('content') ?></textarea>
    
    <select name="category_id">
        <?php foreach ($categories as $category): ?>
            <option value="<?= $category->id ?>"
                    <?= old('category_id') == $category->id ? 'selected' : '' ?>>
                <?= $category->name ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <button type="submit">Create Post</button>
</form>
```

## Best Practices

### 1. Always Validate Input

```php
$validated = $request->validate([...]);
```

### 2. Use Type Hints

```php
public function store(Request $request)
{
    // Type-hinted $request
}
```

### 3. Check Before Accessing

```php
if ($request->has('email')) {
    $email = $request->email;
}
```

### 4. Use Filled for Required Fields

```php
if ($request->filled('search')) {
    // Search is not empty
}
```

### 5. Flash Input on Errors

```php
return back()->withInput()->withErrors($errors);
```

## Next Steps

- [Responses](responses.md) - Returning responses
- [Validation](validation.md) - Validating input
- [File Storage](../advanced/storage.md) - Storing files
- [Middleware](middleware.md) - Filtering requests
