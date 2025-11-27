# Validation via Attributes

## Introduction

NeoFramework provides a powerful attribute-based validation system that allows you to define validation rules directly on your model properties. This declarative approach eliminates the need for separate validation files and keeps your validation logic close to your data definitions.

## Basic Validation

### Validation Attribute

Define validation rules using the `#[Validation]` attribute:

```php
<?php

namespace App\Models;

use Neo\Database\Model;
use Neo\Metadata\Attributes\Validation;

class User extends Model
{
    #[Validation(rules: 'required|max:255')]
    public string $name;
    
    #[Validation(rules: 'required|email|unique:users,email')]
    public string $email;
    
    #[Validation(rules: 'required|min:8')]
    public string $password;
}
```

### Validating Models

Validate model instances:

```php
<?php

use Neo\Metadata\MetadataValidator;

$user = new User();
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->password = '12345';  // Too short

$validator = new MetadataValidator();
$result = $validator->validate($user);

if ($result->fails()) {
    foreach ($result->errors() as $field => $messages) {
        echo "$field: " . implode(', ', $messages) . "\n";
    }
    // Output: password: The password must be at least 8 characters.
}
```

### Validating Request Data

Validate incoming request data:

```php
<?php

use Neo\Http\Request;

public function store(Request $request)
{
    $validator = MetadataValidator::fromModel(User::class);
    $validated = $validator->validate($request->all());
    
    if ($validated->fails()) {
        return back()
            ->withErrors($validated)
            ->withInput();
    }
    
    $user = User::create($validated->validated());
    return redirect()->route('users.show', $user->id);
}
```

## Validation Rules

### Required Rules

```php
<?php

class User extends Model
{
    // Required field
    #[Validation(rules: 'required')]
    public string $name;
    
    // Required if another field has specific value
    #[Validation(rules: 'required_if:is_company,true')]
    public ?string $company_name;
    
    // Required unless another field has specific value
    #[Validation(rules: 'required_unless:type,individual')]
    public ?string $tax_id;
    
    // Required with specific fields
    #[Validation(rules: 'required_with:address,city')]
    public ?string $postal_code;
    
    // Required without specific fields
    #[Validation(rules: 'required_without:email')]
    public ?string $phone;
}
```

### String Rules

```php
<?php

class Post extends Model
{
    // String with max length
    #[Validation(rules: 'required|string|max:255')]
    public string $title;
    
    // String with min length
    #[Validation(rules: 'required|string|min:10')]
    public string $slug;
    
    // String with exact length
    #[Validation(rules: 'required|string|size:10')]
    public string $code;
    
    // String between lengths
    #[Validation(rules: 'required|string|between:5,100')]
    public string $summary;
    
    // Alpha characters only
    #[Validation(rules: 'required|alpha')]
    public string $status;
    
    // Alphanumeric
    #[Validation(rules: 'required|alpha_num')]
    public string $username;
    
    // Alpha with dashes and underscores
    #[Validation(rules: 'required|alpha_dash')]
    public string $slug;
    
    // Regular expression
    #[Validation(rules: 'required|regex:/^[A-Z0-9]+$/')]
    public string $tracking_code;
}
```

### Numeric Rules

```php
<?php

class Product extends Model
{
    // Integer
    #[Validation(rules: 'required|integer')]
    public int $stock;
    
    // Numeric (int or float)
    #[Validation(rules: 'required|numeric')]
    public float $price;
    
    // Min value
    #[Validation(rules: 'required|numeric|min:0')]
    public float $discount;
    
    // Max value
    #[Validation(rules: 'required|numeric|max:100')]
    public int $percentage;
    
    // Between values
    #[Validation(rules: 'required|numeric|between:1,1000')]
    public int $quantity;
    
    // Greater than
    #[Validation(rules: 'required|numeric|gt:0')]
    public float $weight;
    
    // Greater than or equal
    #[Validation(rules: 'required|numeric|gte:1')]
    public int $minimum_order;
    
    // Less than
    #[Validation(rules: 'required|numeric|lt:price')]
    public ?float $sale_price;
    
    // Less than or equal
    #[Validation(rules: 'required|numeric|lte:100')]
    public int $discount_percentage;
}
```

### Email and URL Rules

```php
<?php

class Contact extends Model
{
    // Email validation
    #[Validation(rules: 'required|email')]
    public string $email;
    
    // Email with DNS check
    #[Validation(rules: 'required|email:rfc,dns')]
    public string $verified_email;
    
    // URL validation
    #[Validation(rules: 'required|url')]
    public string $website;
    
    // Active URL (DNS check)
    #[Validation(rules: 'required|active_url')]
    public string $homepage;
    
    // IP address
    #[Validation(rules: 'required|ip')]
    public string $ip_address;
    
    // IPv4
    #[Validation(rules: 'required|ipv4')]
    public string $ipv4_address;
    
    // IPv6
    #[Validation(rules: 'required|ipv6')]
    public string $ipv6_address;
}
```

### Date and Time Rules

```php
<?php

class Event extends Model
{
    // Date format
    #[Validation(rules: 'required|date')]
    public DateTime $event_date;
    
    // Date with specific format
    #[Validation(rules: 'required|date_format:Y-m-d')]
    public string $date_string;
    
    // After date
    #[Validation(rules: 'required|date|after:today')]
    public DateTime $start_date;
    
    // After or equal
    #[Validation(rules: 'required|date|after_or_equal:start_date')]
    public DateTime $end_date;
    
    // Before date
    #[Validation(rules: 'required|date|before:end_date')]
    public DateTime $registration_deadline;
    
    // Before or equal
    #[Validation(rules: 'required|date|before_or_equal:today')]
    public DateTime $birth_date;
    
    // Between dates
    #[Validation(rules: 'required|date|after:2023-01-01|before:2024-12-31')]
    public DateTime $valid_date;
}
```

### File Upload Rules

```php
<?php

class Document extends Model
{
    // File required
    #[Validation(rules: 'required|file')]
    public string $attachment;
    
    // Image file
    #[Validation(rules: 'required|image')]
    public string $photo;
    
    // Specific image types
    #[Validation(rules: 'required|mimes:jpeg,jpg,png,gif')]
    public string $avatar;
    
    // Image dimensions
    #[Validation(rules: 'required|image|dimensions:min_width=100,min_height=100')]
    public string $thumbnail;
    
    // File size (kilobytes)
    #[Validation(rules: 'required|file|max:2048')]  // 2MB
    public string $document;
    
    // MIME types
    #[Validation(rules: 'required|mimetypes:application/pdf,application/msword')]
    public string $contract;
}
```

### Array and Collection Rules

```php
<?php

class Order extends Model
{
    // Array validation
    #[Validation(rules: 'required|array')]
    public array $items;
    
    // Array with min items
    #[Validation(rules: 'required|array|min:1')]
    public array $products;
    
    // Array with max items
    #[Validation(rules: 'required|array|max:10')]
    public array $tags;
    
    // Array between sizes
    #[Validation(rules: 'required|array|between:1,5')]
    public array $categories;
    
    // In list
    #[Validation(rules: 'required|in:pending,processing,completed')]
    public string $status;
    
    // Not in list
    #[Validation(rules: 'required|not_in:draft,archived')]
    public string $publish_status;
}
```

### Database Rules

```php
<?php

class User extends Model
{
    // Exists in database
    #[Validation(rules: 'required|exists:categories,id')]
    public int $category_id;
    
    // Unique in database
    #[Validation(rules: 'required|unique:users,email')]
    public string $email;
    
    // Unique except current record
    #[Validation(rules: 'required|unique:users,email,{id}')]
    public string $email_update;
    
    // Exists with conditions
    #[Validation(rules: 'required|exists:posts,id,status,published')]
    public int $post_id;
}
```

### Boolean Rules

```php
<?php

class Settings extends Model
{
    // Boolean
    #[Validation(rules: 'required|boolean')]
    public bool $is_enabled;
    
    // Accepted (checkbox)
    #[Validation(rules: 'accepted')]
    public bool $terms_accepted;
    
    // Declined
    #[Validation(rules: 'declined')]
    public bool $opt_out;
}
```

### Confirmation Rules

```php
<?php

class User extends Model
{
    // Confirmed field
    #[Validation(rules: 'required|min:8|confirmed')]
    public string $password;
    // Expects password_confirmation field
    
    #[Validation(rules: 'required|email|confirmed')]
    public string $email;
    // Expects email_confirmation field
}
```

### Same and Different Rules

```php
<?php

class Form extends Model
{
    // Same as another field
    #[Validation(rules: 'required|same:password')]
    public string $password_confirmation;
    
    // Different from another field
    #[Validation(rules: 'required|different:old_password')]
    public string $new_password;
}
```

## Custom Validation Messages

### ValidationMessage Attribute

Define custom error messages:

```php
<?php

use Neo\Metadata\Attributes\ValidationMessage;

class User extends Model
{
    #[Validation(rules: 'required|email|unique:users,email')]
    #[ValidationMessage(
        'required' => 'Please enter your email address',
        'email' => 'Please enter a valid email address',
        'unique' => 'This email is already registered'
    )]
    public string $email;
    
    #[Validation(rules: 'required|min:8|confirmed')]
    #[ValidationMessage(
        'required' => 'Password is required',
        'min' => 'Password must be at least 8 characters',
        'confirmed' => 'Password confirmation does not match'
    )]
    public string $password;
}
```

### Placeholder Values

Use placeholders in messages:

```php
<?php

class Product extends Model
{
    #[Validation(rules: 'required|numeric|min:0|max:99999')]
    #[ValidationMessage(
        'required' => 'The :attribute field is required',
        'numeric' => 'The :attribute must be a number',
        'min' => 'The :attribute must be at least :min',
        'max' => 'The :attribute may not be greater than :max'
    )]
    public float $price;
}
```

## Conditional Validation

### Conditional Rules

Apply rules based on conditions:

```php
<?php

use Neo\Metadata\Attributes\ConditionalValidation;

class Order extends Model
{
    #[Validation(rules: 'required|in:delivery,pickup')]
    public string $fulfillment_type;
    
    #[ConditionalValidation(
        when: 'fulfillment_type',
        is: 'delivery',
        rules: 'required|string|max:255'
    )]
    public ?string $delivery_address;
    
    #[ConditionalValidation(
        when: 'fulfillment_type',
        is: 'pickup',
        rules: 'required|exists:stores,id'
    )]
    public ?int $store_id;
}
```

### Multiple Conditions

```php
<?php

class User extends Model
{
    #[Validation(rules: 'required|boolean')]
    public bool $is_company;
    
    #[ConditionalValidation(
        when: 'is_company',
        is: true,
        rules: 'required|string|max:255'
    )]
    public ?string $company_name;
    
    #[ConditionalValidation(
        when: 'is_company',
        is: true,
        rules: 'required|string|size:11'
    )]
    public ?string $tax_id;
}
```

## Custom Validation Rules

### Creating Custom Rules

Define custom validation rules:

```php
<?php

namespace App\Validation\Rules;

use Neo\Validation\Rule;

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

// Usage
use App\Validation\Rules\Uppercase;

class Product extends Model
{
    #[Validation(rules: [new Uppercase(), 'required', 'max:10'])]
    public string $code;
}
```

### Rule with Parameters

```php
<?php

namespace App\Validation\Rules;

use Neo\Validation\Rule;

class WordCount implements Rule
{
    protected int $min;
    protected int $max;
    
    public function __construct(int $min, int $max)
    {
        $this->min = $min;
        $this->max = $max;
    }
    
    public function passes($attribute, $value): bool
    {
        $wordCount = str_word_count($value);
        return $wordCount >= $this->min && $wordCount <= $this->max;
    }
    
    public function message(): string
    {
        return "The :attribute must contain between {$this->min} and {$this->max} words.";
    }
}

// Usage
class Post extends Model
{
    #[Validation(rules: [new WordCount(10, 100), 'required'])]
    public string $summary;
}
```

### Closure Rules

Use closures for validation:

```php
<?php

class Product extends Model
{
    #[Validation(rules: [
        'required',
        'numeric',
        function($attribute, $value, $fail) {
            if ($value > 0 && $value < 1) {
                $fail('The '.$attribute.' must be 0 or at least 1.');
            }
        }
    ])]
    public float $quantity;
}
```

## Advanced Validation

### Nested Validation

Validate nested arrays:

```php
<?php

class Order extends Model
{
    #[Validation(rules: 'required|array|min:1')]
    #[NestedValidation([
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0'
    ])]
    public array $items;
}
```

### Sometimes Validation

Only validate when field is present:

```php
<?php

class User extends Model
{
    #[Validation(rules: 'sometimes|email|unique:users,email')]
    public ?string $email;
    
    #[Validation(rules: 'sometimes|min:8')]
    public ?string $password;
}
```

### Bail Rule

Stop validation on first failure:

```php
<?php

class User extends Model
{
    #[Validation(rules: 'bail|required|email|unique:users,email')]
    public string $email;
    // Stops at first failing rule
}
```

### Nullable Rule

Allow null values:

```php
<?php

class Product extends Model
{
    #[Validation(rules: 'nullable|numeric|min:0')]
    public ?float $sale_price;
    
    #[Validation(rules: 'nullable|date|after:today')]
    public ?DateTime $promotion_ends;
}
```

## Validation Groups

### Group Attribute

Define validation groups:

```php
<?php

use Neo\Metadata\Attributes\ValidationGroup;

class User extends Model
{
    #[Validation(rules: 'required|max:255')]
    #[ValidationGroup('registration')]
    public string $name;
    
    #[Validation(rules: 'required|email|unique:users,email')]
    #[ValidationGroup('registration')]
    public string $email;
    
    #[Validation(rules: 'required|min:8')]
    #[ValidationGroup('registration')]
    public string $password;
    
    #[Validation(rules: 'sometimes|max:1000')]
    #[ValidationGroup('profile')]
    public ?string $bio;
    
    #[Validation(rules: 'sometimes|image|max:2048')]
    #[ValidationGroup('profile')]
    public ?string $avatar;
}

// Validate specific group
$validator = MetadataValidator::fromModel(User::class, 'registration');
$result = $validator->validate($request->all());
```

## Server-Side Validation

### Automatic Validation

Validate before saving:

```php
<?php

use Neo\Metadata\Attributes\AutoValidate;

#[AutoValidate]
class User extends Model
{
    #[Validation(rules: 'required|email|unique:users,email')]
    public string $email;
    
    #[Validation(rules: 'required|min:8')]
    public string $password;
}

// Automatically validates on save
$user = new User();
$user->email = 'invalid-email';  // Invalid
try {
    $user->save();  // Throws ValidationException
} catch (ValidationException $e) {
    echo $e->getMessage();
    print_r($e->errors());
}
```

### Manual Validation

Validate manually:

```php
<?php

$user = new User();
$user->email = 'test@example.com';
$user->password = '12345678';

$validator = new MetadataValidator();
$result = $validator->validate($user);

if ($result->passes()) {
    $user->save();
} else {
    foreach ($result->errors() as $field => $errors) {
        echo "$field: " . implode(', ', $errors) . "\n";
    }
}
```

## Client-Side Validation

### Generate HTML5 Attributes

```php
<?php

class User extends Model
{
    #[Validation(rules: 'required|max:255')]
    public string $name;
    // Generates: required maxlength="255"
    
    #[Validation(rules: 'required|email')]
    public string $email;
    // Generates: required type="email"
    
    #[Validation(rules: 'required|numeric|min:0|max:100')]
    public int $age;
    // Generates: required type="number" min="0" max="100"
}
```

### JavaScript Validation

Generate JavaScript validation:

```php
<?php

$validator = MetadataValidator::fromModel(User::class);
$jsRules = $validator->toJavaScript();

// In view
<script>
<?= $jsRules ?>

// Use with validation library
$('#user-form').validate({
    rules: validationRules,
    messages: validationMessages
});
</script>
```

## Error Handling

### Error Messages

Access validation errors:

```php
<?php

$result = $validator->validate($data);

// Check if validation failed
if ($result->fails()) {
    // Get all errors
    $errors = $result->errors();
    
    // Get errors for specific field
    $emailErrors = $result->errors('email');
    
    // Get first error for field
    $firstError = $result->first('email');
    
    // Check if field has error
    if ($result->has('email')) {
        echo 'Email has errors';
    }
}

// Get validated data
$validated = $result->validated();
```

### Custom Error Formatting

```php
<?php

$result = $validator->validate($data);

// Format errors as array
$errors = $result->toArray();

// Format errors as JSON
$json = $result->toJson();

// Format for specific framework
$laravelBag = $result->toLaravelMessageBag();
```

## Configuration

Configure validation in `config/validation.php`:

```php
<?php

return [
    'throw_on_failure' => false,
    
    'auto_validate' => [
        'enabled' => true,
        'on_create' => true,
        'on_update' => true,
    ],
    
    'custom_rules' => [
        'uppercase' => App\Validation\Rules\Uppercase::class,
        'word_count' => App\Validation\Rules\WordCount::class,
    ],
    
    'messages' => [
        'required' => 'The :attribute field is required.',
        'email' => 'The :attribute must be a valid email address.',
        'min' => [
            'string' => 'The :attribute must be at least :min characters.',
            'numeric' => 'The :attribute must be at least :min.',
        ],
    ],
    
    'attributes' => [
        'email' => 'email address',
        'password' => 'password',
        'password_confirmation' => 'password confirmation',
    ],
];
```

## Best Practices

1. **Validate User Input**: Always validate data from external sources
2. **Use Appropriate Rules**: Choose validation rules that match your data type
3. **Provide Clear Messages**: Write user-friendly error messages
4. **Validate Server-Side**: Never rely solely on client-side validation
5. **Group Related Rules**: Organize rules logically
6. **Use Database Rules**: Validate foreign keys with `exists` rule
7. **Handle File Uploads**: Properly validate file types and sizes
8. **Test Validation**: Write tests for validation rules
9. **Document Custom Rules**: Provide clear documentation for custom rules
10. **Use Conditional Validation**: Apply rules only when needed

## Testing

### Testing Validation

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Neo\Metadata\MetadataValidator;

class UserValidationTest extends TestCase
{
    public function test_validates_email_format()
    {
        $validator = MetadataValidator::fromModel(User::class);
        
        $result = $validator->validate([
            'email' => 'invalid-email'
        ]);
        
        $this->assertTrue($result->fails());
        $this->assertTrue($result->has('email'));
    }
    
    public function test_validates_password_length()
    {
        $validator = MetadataValidator::fromModel(User::class);
        
        $result = $validator->validate([
            'password' => '123'
        ]);
        
        $this->assertTrue($result->fails());
        $this->assertStringContainsString('at least 8', $result->first('password'));
    }
}
```

## Next Steps

- Return to [Metadata Introduction](introduction.md)
- Explore [Form Generation](form-generation.md)
- Learn about [Field Attributes](field-attributes.md)
