# Responses

## Introduction

All routes and controllers should return a response to be sent back to the user's browser. NeoFramework provides several different ways to return responses, giving you full control over the response type and content.

## String & Array Responses

### String Responses

```php
Route::get('/', function () {
    return 'Hello World';
});
```

### Array & JSON Responses

```php
Route::get('/user', function () {
    return ['name' => 'John', 'email' => 'john@example.com'];
});
// Automatically converted to JSON
```

## Response Objects

### Creating Responses

```php
Route::get('/', function () {
    return response('Hello World', 200)
        ->header('Content-Type', 'text/plain');
});
```

### Attaching Headers

```php
return response($content)
    ->header('Content-Type', 'application/json')
    ->header('X-Custom-Header', 'Value');

// Multiple headers
return response($content)
    ->withHeaders([
        'Content-Type' => 'application/json',
        'X-Custom-Header' => 'Value',
    ]);
```

### Attaching Cookies

```php
return response($content)
    ->cookie('name', 'value', $minutes);

// With options
return response($content)
    ->cookie('name', 'value', $minutes, $path, $domain, $secure, $httpOnly);
```

## View Responses

```php
return response()
    ->view('hello', $data, 200)
    ->header('Content-Type', 'text/html');

// Or simply
return view('hello', $data);
```

## JSON Responses

### Basic JSON

```php
return response()->json([
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

### JSONP Responses

```php
return response()
    ->json(['name' => 'John'])
    ->withCallback($request->input('callback'));
```

### JSON with Status Code

```php
return response()->json([
    'message' => 'Resource created'
], 201);

return response()->json([
    'error' => 'Not found'
], 404);
```

## File Downloads

### Download Response

```php
return response()->download($pathToFile);

// With custom filename
return response()->download($pathToFile, $name, $headers);

// Delete after download
return response()->download($pathToFile)->deleteFileAfterSend();
```

### File Response

Display file in browser instead of downloading:

```php
return response()->file($pathToFile);

// With headers
return response()->file($pathToFile, $headers);
```

### Streamed Downloads

```php
return response()->streamDownload(function () {
    echo file_get_contents('https://example.com/large-file.zip');
}, 'file.zip');
```

## Redirects

### Basic Redirects

```php
return redirect('/home');

// To named route
return redirect()->route('home');

// To controller action
return redirect()->action([HomeController::class, 'index']);

// To external URL
return redirect()->away('https://www.google.com');
```

### Redirecting with Flash Data

```php
return redirect('/dashboard')->with('status', 'Profile updated!');

// In view
<?php if (session('status')): ?>
    <div class="alert"><?= session('status') ?></div>
<?php endif; ?>
```

### Redirecting with Input

```php
return redirect('/form')->withInput();

return redirect('/form')->withInput($request->except('password'));
```

### Redirecting with Errors

```php
return redirect('/form')->withErrors([
    'email' => 'Invalid email address'
]);
```

### Redirecting to Previous URL

```php
return back();

return back()->withInput();
```

## Response Macros

Register custom response macros in a service provider:

```php
use NeoPhp\Support\Facades\Response;

Response::macro('caps', function ($value) {
    return Response::make(strtoupper($value));
});
```

Use the macro:

```php
return response()->caps('foo');
// FOO
```

## Practical Examples

### Example 1: API Success Response

```php
class ApiController extends Controller
{
    protected function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
    
    protected function errorResponse($message, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }
    
    public function store(Request $request)
    {
        $user = User::create($request->validated());
        
        return $this->successResponse(
            new UserResource($user),
            'User created successfully',
            201
        );
    }
}
```

### Example 2: File Export

```php
public function exportUsers()
{
    $users = User::all();
    
    $csv = "Name,Email,Created At\n";
    foreach ($users as $user) {
        $csv .= "{$user->name},{$user->email},{$user->created_at}\n";
    }
    
    return response($csv)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', 'attachment; filename="users.csv"');
}
```

### Example 3: Paginated JSON Response

```php
public function index()
{
    $users = User::paginate(15);
    
    return response()->json([
        'data' => UserResource::collection($users),
        'links' => [
            'first' => $users->url(1),
            'last' => $users->url($users->lastPage()),
            'prev' => $users->previousPageUrl(),
            'next' => $users->nextPageUrl(),
        ],
        'meta' => [
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
        ],
    ]);
}
```

### Example 4: Conditional Response

```php
public function show(Request $request, Post $post)
{
    if ($request->wantsJson()) {
        return response()->json(new PostResource($post));
    }
    
    return view('posts.show', compact('post'));
}
```

### Example 5: Image Response

```php
public function avatar(User $user)
{
    $path = storage_path('app/avatars/' . $user->avatar);
    
    if (!file_exists($path)) {
        return response()->file(public_path('images/default-avatar.png'));
    }
    
    return response()->file($path, [
        'Content-Type' => 'image/jpeg',
        'Cache-Control' => 'max-age=31536000',
    ]);
}
```

### Example 6: Streaming Response

```php
public function stream()
{
    return response()->stream(function () {
        while (true) {
            echo 'data: ' . json_encode([
                'time' => now()->toDateTimeString(),
                'random' => rand(1, 100)
            ]) . "\n\n";
            
            ob_flush();
            flush();
            
            sleep(1);
        }
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'X-Accel-Buffering' => 'no',
    ]);
}
```

### Example 7: Form Submission Response

```php
public function store(StorePostRequest $request)
{
    $post = Post::create($request->validated());
    
    if ($request->expectsJson()) {
        return response()->json([
            'message' => 'Post created successfully',
            'post' => new PostResource($post),
        ], 201);
    }
    
    return redirect()
        ->route('posts.show', $post)
        ->with('success', 'Post created successfully!');
}
```

## HTTP Status Codes

### Success Codes

```php
return response()->json($data, 200); // OK
return response()->json($data, 201); // Created
return response()->json(null, 204);  // No Content
```

### Client Error Codes

```php
return response()->json(['error' => 'Bad Request'], 400);
return response()->json(['error' => 'Unauthorized'], 401);
return response()->json(['error' => 'Forbidden'], 403);
return response()->json(['error' => 'Not Found'], 404);
return response()->json(['error' => 'Validation Failed'], 422);
```

### Server Error Codes

```php
return response()->json(['error' => 'Server Error'], 500);
return response()->json(['error' => 'Service Unavailable'], 503);
```

## Response Helpers

### abort()

Throw HTTP exception:

```php
abort(404);
abort(403, 'Unauthorized action.');
abort_if($condition, 403);
abort_unless($condition, 404);
```

### Custom Error Pages

Create views in `resources/views/errors/`:

- `404.php` - Not Found
- `403.php` - Forbidden
- `500.php` - Server Error

```php
<!-- resources/views/errors/404.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Page Not Found</title>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you're looking for doesn't exist.</p>
    <a href="<?= route('home') ?>">Go Home</a>
</body>
</html>
```

## Best Practices

### 1. Use Appropriate Status Codes

```php
// Created resource
return response()->json($data, 201);

// No content
return response()->json(null, 204);

// Validation error
return response()->json($errors, 422);
```

### 2. Be Consistent with API Responses

```php
// Always use same structure
return response()->json([
    'success' => true,
    'data' => $data,
    'message' => $message,
]);
```

### 3. Use Resource Classes

```php
return response()->json(new UserResource($user));
return response()->json(UserResource::collection($users));
```

### 4. Handle Both JSON and HTML

```php
if ($request->expectsJson()) {
    return response()->json($data);
}

return view('page', compact('data'));
```

### 5. Flash Messages for Redirects

```php
return redirect()
    ->route('posts.index')
    ->with('success', 'Post deleted successfully!');
```

## Testing Responses

```php
class ResponseTest extends TestCase
{
    public function test_json_response()
    {
        $response = $this->getJson('/api/users');
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'links', 'meta']);
    }
    
    public function test_redirect_response()
    {
        $response = $this->post('/posts', $data);
        
        $response->assertRedirect(route('posts.index'))
                 ->assertSessionHas('success');
    }
    
    public function test_file_download()
    {
        $response = $this->get('/export/users');
        
        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'text/csv');
    }
}
```

## Next Steps

- [Views](views.md) - Rendering views
- [Validation](validation.md) - Validating input
- [API Resources](../api/resources.md) - Transforming responses
- [Error Handling](../advanced/error-handling.md) - Handling errors
