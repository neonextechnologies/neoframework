# Validation

## Introduction

NeoFramework provides several different approaches to validate your application's incoming data. It's most common to use the `validate` method available on all incoming HTTP requests. However, we will discuss other approaches to validation as well.

## Validation Quickstart

### Defining Routes

```php
Route::get('/post/create', [PostController::class, 'create']);
Route::post('/post', [PostController::class, 'store']);
```

### Creating Controller

```php
<?php

namespace App\Controllers;

use NeoPhp\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function create()
    {
        return view('post.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
            'author' => 'required|email',
        ]);
        
        // The data is valid...
        Post::create($validated);
        
        return redirect('/posts');
    }
}
```

If validation fails, a redirect response will be automatically generated. If the request was an AJAX request, a JSON response with validation errors will be returned.

### Displaying Validation Errors

```php
<!-- resources/views/post/create.php -->
<form method="POST" action="/post">
    <?= csrf_field() ?>
    
    <div>
        <label>Title</label>
        <input type="text" name="title" value="<?= old('title') ?>">
        
        <?php if ($errors->has('title')): ?>
            <div class="error">
                <?= $errors->first('title') ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div>
        <label>Body</label>
        <textarea name="body"><?= old('body') ?></textarea>
        
        <?php if ($errors->has('body')): ?>
            <div class="error">
                <?= $errors->first('body') ?>
            </div>
        <?php endif; ?>
    </div>
    
    <button type="submit">Submit</button>
</form>
```

## Available Validation Rules

### Required

```php
'field' => 'required'
```

### Email

```php
'email' => 'required|email'
```

### Numeric

```php
'age' => 'required|numeric'
'price' => 'required|numeric|min:0'
```

### String Length

```php
'name' => 'required|min:3|max:255'
'password' => 'required|min:8'
```

### Between

```php
'age' => 'required|between:18,65'
'price' => 'required|between:0.01,999.99'
```

### In / Not In

```php
'status' => 'required|in:draft,published,archived'
'role' => 'required|not_in:admin,superadmin'
```

### Unique

```php
'email' => 'required|email|unique:users'
'email' => 'required|email|unique:users,email'
'email' => 'required|email|unique:users,email,'.$userId
```

### Exists

```php
'user_id' => 'required|exists:users,id'
'category_id' => 'required|exists:categories,id'
```

### Confirmed

```php
'password' => 'required|min:8|confirmed'
// Expects 'password_confirmation' field
```

### Date

```php
'birthday' => 'required|date'
'start_date' => 'required|date|after:today'
'end_date' => 'required|date|after:start_date'
'event_date' => 'required|date|before:2025-12-31'
```

### Array

```php
'tags' => 'required|array'
'tags.*' => 'required|string|max:255'
'users.*.email' => 'required|email|unique:users'
```

### File Upload

```php
'avatar' => 'required|file|max:2048' // Max 2MB
'photo' => 'required|image' // Must be image
'photo' => 'required|image|mimes:jpg,png,gif'
'photo' => 'required|image|dimensions:min_width=100,min_height=100'
'document' => 'required|file|mimes:pdf,doc,docx'
```

### Boolean

```php
'is_active' => 'required|boolean'
```

### IP Address

```php
'ip' => 'required|ip'
'ipv4' => 'required|ipv4'
'ipv6' => 'required|ipv6'
```

### URL

```php
'website' => 'required|url'
```

### Regular Expression

```php
'code' => 'required|regex:/^[A-Z]{3}[0-9]{3}$/'
```

## Complex Validation

### Conditional Rules

```php
$validator = Validator::make($request->all(), [
    'email' => 'required|email',
    'games' => 'required|numeric',
]);

$validator->sometimes('reason', 'required|max:500', function ($input) {
    return $input->games >= 100;
});
```

### Custom Messages

```php
$validated = $request->validate([
    'title' => 'required|max:255',
    'body' => 'required',
], [
    'title.required' => 'Please enter a title for your post',
    'title.max' => 'The title is too long',
    'body.required' => 'Post body is required',
]);
```

### Custom Attribute Names

```php
$validated = $request->validate([
    'email' => 'required|email',
], [], [
    'email' => 'email address',
]);
// Error message: "The email address field is required."
```

## Form Request Validation

For complex validation logic, create a form request class:

```bash
php neo make:request StorePostRequest
```

This creates `app/Http/Requests/StorePostRequest.php`:

```php
<?php

namespace App\Http\Requests;

use NeoPhp\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    
    /**
     * Get the validation rules.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'slug' => 'required|unique:posts',
            'body' => 'required',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'published_at' => 'nullable|date',
        ];
    }
    
    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'A post title is required',
            'body.required' => 'Post content cannot be empty',
        ];
    }
    
    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'published_at' => 'publication date',
        ];
    }
}
```

Use in controller:

```php
public function store(StorePostRequest $request)
{
    // Request is already validated
    $post = Post::create($request->validated());
    
    return redirect()->route('posts.show', $post);
}
```

### Authorization in Form Requests

```php
public function authorize(): bool
{
    $post = Post::find($this->route('post'));
    
    return $post && $this->user()->can('update', $post);
}
```

### Preparing Input

```php
protected function prepareForValidation()
{
    $this->merge([
        'slug' => Str::slug($this->title),
    ]);
}
```

## Manual Validation

### Creating Validator

```php
use NeoPhp\Validation\Validator;

$validator = Validator::make($request->all(), [
    'title' => 'required|max:255',
    'body' => 'required',
]);

if ($validator->fails()) {
    return redirect('post/create')
        ->withErrors($validator)
        ->withInput();
}

$validated = $validator->validated();
```

### Checking Validation

```php
if ($validator->fails()) {
    // Validation failed
}

if ($validator->passes()) {
    // Validation passed
}
```

### Getting Errors

```php
$errors = $validator->errors();

// Get first error for field
$error = $errors->first('email');

// Get all errors for field
$errors = $errors->get('email');

// Get all errors
$all = $errors->all();

// Check if has error
if ($errors->has('email')) {
    // Email has error
}
```

## Custom Validation Rules

### Using Closures

```php
$validated = $request->validate([
    'title' => [
        'required',
        'max:255',
        function ($attribute, $value, $fail) {
            if (str_contains(strtolower($value), 'spam')) {
                $fail('The '.$attribute.' contains spam words.');
            }
        },
    ],
]);
```

### Rule Objects

Create custom rule:

```bash
php neo make:rule Uppercase
```

```php
<?php

namespace App\Rules;

use NeoPhp\Contracts\Validation\Rule;

class Uppercase implements Rule
{
    public function passes($attribute, $value): bool
    {
        return strtoupper($value) === $value;
    }
    
    public function message(): string
    {
        return 'The :attribute must be uppercase.';
    }
}
```

Use custom rule:

```php
use App\Rules\Uppercase;

$validated = $request->validate([
    'name' => ['required', new Uppercase],
]);
```

## Practical Examples

### Example 1: User Registration

```php
<?php

namespace App\Http\Requests;

use NeoPhp\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
            'avatar' => 'nullable|image|max:2048',
        ];
    }
    
    public function messages(): array
    {
        return [
            'terms.accepted' => 'You must accept the terms and conditions',
            'password.confirmed' => 'Password confirmation does not match',
        ];
    }
}
```

### Example 2: Product Creation

```php
<?php

namespace App\Http\Requests;

use NeoPhp\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-products');
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products|regex:/^[a-z0-9-]+$/',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|gt:price',
            'cost' => 'nullable|numeric|min:0',
            'sku' => 'required|string|unique:products',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpg,png|max:5120',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'specifications' => 'nullable|array',
            'specifications.*.name' => 'required|string',
            'specifications.*.value' => 'required|string',
        ];
    }
    
    public function messages(): array
    {
        return [
            'compare_price.gt' => 'Compare price must be greater than regular price',
            'images.required' => 'At least one product image is required',
            'images.*.max' => 'Each image must not exceed 5MB',
        ];
    }
    
    protected function prepareForValidation()
    {
        if (!$this->slug) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
    }
}
```

### Example 3: Multi-Step Form

```php
class OrderController extends Controller
{
    public function step1(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|regex:/^[0-9]{10}$/',
        ]);
        
        session(['order_step1' => $validated]);
        
        return redirect()->route('order.step2');
    }
    
    public function step2(Request $request)
    {
        $validated = $request->validate([
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip' => 'required|regex:/^[0-9]{5}$/',
        ]);
        
        session(['order_step2' => $validated]);
        
        return redirect()->route('order.step3');
    }
    
    public function step3(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:credit_card,paypal,bank_transfer',
            'card_number' => 'required_if:payment_method,credit_card',
            'card_expiry' => 'required_if:payment_method,credit_card',
            'card_cvv' => 'required_if:payment_method,credit_card',
        ]);
        
        // Combine all steps
        $orderData = array_merge(
            session('order_step1'),
            session('order_step2'),
            $validated
        );
        
        $order = Order::create($orderData);
        
        session()->forget(['order_step1', 'order_step2']);
        
        return redirect()->route('order.success', $order);
    }
}
```

## Best Practices

### 1. Use Form Requests for Complex Validation

```php
// Instead of this in controller
$request->validate([...]);

// Use Form Request
public function store(StorePostRequest $request)
{
    // Already validated
}
```

### 2. Validate Early

```php
public function store(Request $request)
{
    // Validate first
    $validated = $request->validate([...]);
    
    // Then process
    Post::create($validated);
}
```

### 3. Use Custom Error Messages

```php
$request->validate([...], [
    'email.required' => 'We need your email address',
    'email.email' => 'Please provide a valid email',
]);
```

### 4. Prepare Data Before Validation

```php
protected function prepareForValidation()
{
    $this->merge([
        'slug' => Str::slug($this->title),
        'published' => $this->boolean('published'),
    ]);
}
```

### 5. Use Rule Objects for Complex Rules

```php
$request->validate([
    'code' => ['required', new ValidPromotionCode],
]);
```

## Testing Validation

```php
class PostValidationTest extends TestCase
{
    public function test_title_is_required()
    {
        $response = $this->post('/posts', [
            'body' => 'Post body',
        ]);
        
        $response->assertSessionHasErrors('title');
    }
    
    public function test_email_must_be_unique()
    {
        $user = User::factory()->create();
        
        $response = $this->post('/register', [
            'email' => $user->email,
        ]);
        
        $response->assertSessionHasErrors('email');
    }
}
```

## Next Steps

- [Form Requests](form-requests.md) - Advanced form validation
- [Error Handling](../advanced/error-handling.md) - Handle validation errors
- [Testing](../testing/getting-started.md) - Test validation logic
