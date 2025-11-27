# 📝 Form Generator

Generate form classes with automatic rendering, validation, and model binding in your NeoFramework application. The form generator creates fully-featured form classes leveraging NeoFramework's metadata system.

## 📋 Table of Contents

- [Basic Usage](#basic-usage)
- [Generated Code](#generated-code)
- [Form Features](#form-features)
- [Advanced Examples](#advanced-examples)
- [Best Practices](#best-practices)

## 🚀 Basic Usage

### Generate Form Class

```bash
php neo make:form UserForm
```

**Generated:** `app/Forms/UserForm.php`

```php
<?php

namespace App\Forms;

use Neo\Forms\Form;
use Neo\Forms\Fields\TextField;
use Neo\Forms\Fields\EmailField;
use Neo\Forms\Fields\PasswordField;

class UserForm extends Form
{
    /**
     * Define form fields.
     */
    public function fields(): array
    {
        return [
            TextField::make('name')
                ->label('Full Name')
                ->required()
                ->maxLength(255),

            EmailField::make('email')
                ->label('Email Address')
                ->required()
                ->unique('users'),

            PasswordField::make('password')
                ->label('Password')
                ->required()
                ->minLength(8)
                ->confirmed(),
        ];
    }

    /**
     * Get validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
```

## 📝 Complete Form Examples

### User Registration Form

```php
<?php

namespace App\Forms;

use Neo\Forms\Form;
use Neo\Forms\Fields\TextField;
use Neo\Forms\Fields\EmailField;
use Neo\Forms\Fields\PasswordField;
use Neo\Forms\Fields\CheckboxField;
use Neo\Forms\Fields\DateField;

class UserRegistrationForm extends Form
{
    /**
     * Define form fields.
     */
    public function fields(): array
    {
        return [
            TextField::make('name')
                ->label('Full Name')
                ->placeholder('Enter your full name')
                ->required()
                ->maxLength(255)
                ->help('This will be displayed on your profile'),

            EmailField::make('email')
                ->label('Email Address')
                ->placeholder('you@example.com')
                ->required()
                ->unique('users'),

            PasswordField::make('password')
                ->label('Password')
                ->required()
                ->minLength(8)
                ->confirmed()
                ->help('Must be at least 8 characters'),

            PasswordField::make('password_confirmation')
                ->label('Confirm Password')
                ->required(),

            DateField::make('birth_date')
                ->label('Date of Birth')
                ->nullable()
                ->maxDate('today'),

            CheckboxField::make('terms')
                ->label('I agree to the Terms of Service')
                ->required()
                ->trueValue(true)
                ->falseValue(false),

            CheckboxField::make('newsletter')
                ->label('Subscribe to newsletter')
                ->default(true),
        ];
    }

    /**
     * Get validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'birth_date' => 'nullable|date|before:today',
            'terms' => 'required|accepted',
            'newsletter' => 'boolean',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'terms.required' => 'You must accept the terms of service',
            'email.unique' => 'This email is already registered',
        ];
    }
}
```

### Post Form

```php
<?php

namespace App\Forms;

use Neo\Forms\Form;
use Neo\Forms\Fields\TextField;
use Neo\Forms\Fields\TextareaField;
use Neo\Forms\Fields\SelectField;
use Neo\Forms\Fields\MultiSelectField;
use Neo\Forms\Fields\FileField;
use Neo\Forms\Fields\RichTextField;
use Neo\Forms\Fields\SwitchField;
use App\Models\Category;
use App\Models\Tag;

class PostForm extends Form
{
    /**
     * Define form fields.
     */
    public function fields(): array
    {
        return [
            TextField::make('title')
                ->label('Post Title')
                ->required()
                ->maxLength(255)
                ->placeholder('Enter an engaging title'),

            TextField::make('slug')
                ->label('URL Slug')
                ->maxLength(255)
                ->help('Leave blank to auto-generate from title'),

            TextareaField::make('excerpt')
                ->label('Excerpt')
                ->rows(3)
                ->maxLength(500)
                ->help('Brief summary of the post'),

            RichTextField::make('content')
                ->label('Content')
                ->required()
                ->toolbar(['bold', 'italic', 'link', 'image'])
                ->help('Full post content with formatting'),

            SelectField::make('category_id')
                ->label('Category')
                ->required()
                ->options(Category::pluck('name', 'id'))
                ->placeholder('Select a category'),

            MultiSelectField::make('tags')
                ->label('Tags')
                ->options(Tag::pluck('name', 'id'))
                ->help('Select all relevant tags'),

            SelectField::make('status')
                ->label('Status')
                ->required()
                ->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                    'archived' => 'Archived',
                ])
                ->default('draft'),

            FileField::make('featured_image')
                ->label('Featured Image')
                ->accept(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(2048) // KB
                ->help('Maximum file size: 2MB'),

            SwitchField::make('is_featured')
                ->label('Featured Post')
                ->default(false)
                ->help('Display on homepage'),

            SwitchField::make('allow_comments')
                ->label('Allow Comments')
                ->default(true),
        ];
    }

    /**
     * Get validation rules.
     */
    public function rules(): array
    {
        $postId = $this->model?->id;

        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,' . $postId,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'required|in:draft,published,archived',
            'featured_image' => 'nullable|image|max:2048',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
        ];
    }

    /**
     * Prepare data before validation.
     */
    protected function prepareForValidation(array $data): array
    {
        // Auto-generate slug if not provided
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = str_slug($data['title']);
        }

        return $data;
    }

    /**
     * Handle after save hook.
     */
    protected function afterSave($model, array $data): void
    {
        // Sync tags relationship
        if (isset($data['tags'])) {
            $model->tags()->sync($data['tags']);
        }

        // Handle featured image upload
        if (isset($data['featured_image'])) {
            $path = $data['featured_image']->store('posts', 'public');
            $model->update(['featured_image' => $path]);
        }
    }
}
```

### Product Form

```php
<?php

namespace App\Forms;

use Neo\Forms\Form;
use Neo\Forms\Fields\TextField;
use Neo\Forms\Fields\TextareaField;
use Neo\Forms\Fields\NumberField;
use Neo\Forms\Fields\SelectField;
use Neo\Forms\Fields\MultiFileField;
use Neo\Forms\Fields\RepeaterField;
use App\Models\Category;
use App\Models\Brand;

class ProductForm extends Form
{
    /**
     * Define form fields.
     */
    public function fields(): array
    {
        return [
            TextField::make('name')
                ->label('Product Name')
                ->required()
                ->maxLength(255),

            TextField::make('sku')
                ->label('SKU')
                ->required()
                ->unique('products')
                ->maxLength(50),

            TextareaField::make('description')
                ->label('Description')
                ->required()
                ->rows(5),

            NumberField::make('price')
                ->label('Price')
                ->required()
                ->min(0)
                ->step(0.01)
                ->prefix('$')
                ->help('Regular selling price'),

            NumberField::make('sale_price')
                ->label('Sale Price')
                ->nullable()
                ->min(0)
                ->step(0.01)
                ->prefix('$')
                ->help('Leave blank if not on sale'),

            NumberField::make('quantity')
                ->label('Stock Quantity')
                ->required()
                ->min(0)
                ->step(1),

            SelectField::make('category_id')
                ->label('Category')
                ->required()
                ->options(Category::pluck('name', 'id')),

            SelectField::make('brand_id')
                ->label('Brand')
                ->nullable()
                ->options(Brand::pluck('name', 'id')),

            MultiFileField::make('images')
                ->label('Product Images')
                ->accept(['image/*'])
                ->maxFiles(5)
                ->maxSize(1024)
                ->help('Upload up to 5 images'),

            RepeaterField::make('specifications')
                ->label('Specifications')
                ->schema([
                    TextField::make('name')->label('Specification'),
                    TextField::make('value')->label('Value'),
                ])
                ->minItems(1)
                ->maxItems(10),

            SelectField::make('status')
                ->label('Status')
                ->required()
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'out_of_stock' => 'Out of Stock',
                ])
                ->default('active'),
        ];
    }

    /**
     * Get validation rules.
     */
    public function rules(): array
    {
        $productId = $this->model?->id;

        return [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku,' . $productId,
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'images.*' => 'image|max:1024',
            'specifications' => 'nullable|array',
            'specifications.*.name' => 'required_with:specifications|string',
            'specifications.*.value' => 'required_with:specifications|string',
            'status' => 'required|in:active,inactive,out_of_stock',
        ];
    }
}
```

### Contact Form

```php
<?php

namespace App\Forms;

use Neo\Forms\Form;
use Neo\Forms\Fields\TextField;
use Neo\Forms\Fields\EmailField;
use Neo\Forms\Fields\TextareaField;
use Neo\Forms\Fields\SelectField;

class ContactForm extends Form
{
    /**
     * Define form fields.
     */
    public function fields(): array
    {
        return [
            TextField::make('name')
                ->label('Your Name')
                ->required()
                ->maxLength(100),

            EmailField::make('email')
                ->label('Email Address')
                ->required(),

            TextField::make('phone')
                ->label('Phone Number')
                ->nullable()
                ->tel()
                ->pattern('[0-9]{10}')
                ->help('10-digit phone number'),

            SelectField::make('subject')
                ->label('Subject')
                ->required()
                ->options([
                    'general' => 'General Inquiry',
                    'support' => 'Technical Support',
                    'sales' => 'Sales',
                    'feedback' => 'Feedback',
                ]),

            TextareaField::make('message')
                ->label('Message')
                ->required()
                ->minLength(10)
                ->maxLength(1000)
                ->rows(6),
        ];
    }

    /**
     * Get validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'nullable|regex:/^[0-9]{10}$/',
            'subject' => 'required|in:general,support,sales,feedback',
            'message' => 'required|string|min:10|max:1000',
        ];
    }

    /**
     * Handle successful form submission.
     */
    protected function afterValidation(array $data): void
    {
        // Send email to admin
        Mail::to(config('mail.admin_email'))
            ->send(new ContactFormSubmitted($data));

        // Log the contact
        ContactSubmission::create($data);
    }
}
```

## 🔧 Field Types

### Text Inputs

```php
TextField::make('username')
    ->label('Username')
    ->placeholder('Enter username')
    ->required()
    ->minLength(3)
    ->maxLength(20)
    ->pattern('[a-zA-Z0-9_]+')
    ->help('Only letters, numbers, and underscores');

EmailField::make('email')
    ->label('Email')
    ->required()
    ->unique('users');

PasswordField::make('password')
    ->label('Password')
    ->required()
    ->minLength(8)
    ->confirmed();

TelField::make('phone')
    ->label('Phone')
    ->pattern('[0-9]{10}');

UrlField::make('website')
    ->label('Website')
    ->nullable();
```

### Textarea and Rich Text

```php
TextareaField::make('bio')
    ->label('Biography')
    ->rows(5)
    ->maxLength(500);

RichTextField::make('content')
    ->label('Content')
    ->toolbar(['bold', 'italic', 'underline', 'link', 'image'])
    ->required();
```

### Number Inputs

```php
NumberField::make('age')
    ->label('Age')
    ->min(18)
    ->max(100)
    ->step(1);

NumberField::make('price')
    ->label('Price')
    ->min(0)
    ->step(0.01)
    ->prefix('$')
    ->suffix('USD');
```

### Date and Time

```php
DateField::make('birth_date')
    ->label('Birth Date')
    ->minDate('1900-01-01')
    ->maxDate('today');

DateTimeField::make('event_start')
    ->label('Event Start')
    ->minDate('today')
    ->required();

TimeField::make('appointment_time')
    ->label('Appointment Time')
    ->step(15); // 15-minute intervals
```

### Select and Multi-select

```php
SelectField::make('country')
    ->label('Country')
    ->options([
        'us' => 'United States',
        'uk' => 'United Kingdom',
        'ca' => 'Canada',
    ])
    ->placeholder('Choose a country')
    ->searchable();

MultiSelectField::make('interests')
    ->label('Interests')
    ->options([
        'tech' => 'Technology',
        'sports' => 'Sports',
        'music' => 'Music',
    ])
    ->maxSelections(5);
```

### Checkboxes and Radios

```php
CheckboxField::make('subscribe')
    ->label('Subscribe to newsletter')
    ->default(true);

RadioField::make('gender')
    ->label('Gender')
    ->options([
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
    ])
    ->required();

CheckboxGroupField::make('permissions')
    ->label('Permissions')
    ->options([
        'read' => 'Read',
        'write' => 'Write',
        'delete' => 'Delete',
    ]);
```

### File Uploads

```php
FileField::make('avatar')
    ->label('Profile Picture')
    ->accept(['image/*'])
    ->maxSize(1024); // KB

MultiFileField::make('documents')
    ->label('Documents')
    ->accept(['application/pdf', '.doc', '.docx'])
    ->maxFiles(5)
    ->maxSize(2048);
```

### Special Fields

```php
SwitchField::make('is_active')
    ->label('Active')
    ->default(true);

RangeField::make('volume')
    ->label('Volume')
    ->min(0)
    ->max(100)
    ->step(5);

ColorField::make('theme_color')
    ->label('Theme Color')
    ->default('#3490dc');

RepeaterField::make('addresses')
    ->label('Addresses')
    ->schema([
        TextField::make('street'),
        TextField::make('city'),
        TextField::make('state'),
        TextField::make('zip'),
    ])
    ->minItems(1)
    ->maxItems(3);
```

## 🎯 Using Forms

### In Controllers

```php
<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Forms\UserForm;
use App\Models\User;
use Neo\Http\Request;

class UserController extends Controller
{
    public function create()
    {
        $form = new UserForm();
        return view('users.create', compact('form'));
    }

    public function store(Request $request)
    {
        $form = new UserForm();
        
        $validated = $form->validate($request->all());
        
        $user = User::create($validated);
        
        return redirect()->route('users.show', $user)
            ->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        $form = new UserForm($user);
        return view('users.edit', compact('form', 'user'));
    }

    public function update(Request $request, User $user)
    {
        $form = new UserForm($user);
        
        $validated = $form->validate($request->all());
        
        $user->update($validated);
        
        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully!');
    }
}
```

### In Views

```blade
<!-- resources/views/users/create.blade.php -->
<form method="POST" action="{{ route('users.store') }}">
    @csrf
    
    {!! $form->render() !!}
    
    <button type="submit">Create User</button>
</form>

<!-- Or render individual fields -->
<form method="POST" action="{{ route('users.store') }}">
    @csrf
    
    {!! $form->field('name')->render() !!}
    {!! $form->field('email')->render() !!}
    {!! $form->field('password')->render() !!}
    
    <button type="submit">Create User</button>
</form>
```

## 🎯 Best Practices

### Keep Forms Focused

```php
// Good: One form per purpose
class UserCreateForm extends Form { }
class UserEditForm extends Form { }
class UserProfileForm extends Form { }

// Bad: One form for everything
class UserForm extends Form {
    public function __construct($type) { }
}
```

### Use Appropriate Field Types

```php
// Good
EmailField::make('email')
NumberField::make('age')
DateField::make('birth_date')

// Bad
TextField::make('email')
TextField::make('age')
TextField::make('birth_date')
```

### Validate at Form Level

```php
public function rules(): array
{
    return [
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ];
}
```

### Handle Related Data

```php
protected function afterSave($model, array $data): void
{
    if (isset($data['tags'])) {
        $model->tags()->sync($data['tags']);
    }
    
    if (isset($data['avatar'])) {
        $model->updateAvatar($data['avatar']);
    }
}
```

## 📚 Related Documentation

- [Validation](../basics/validation.md) - Form validation
- [Metadata](../metadata/form-generation.md) - Form generation from metadata
- [Views](../basics/views.md) - Rendering forms

## 🔗 Quick Reference

```bash
# Generate form
php neo make:form UserForm

# Force overwrite
php neo make:form UserForm --force
```

**Common patterns:**

```php
// Create form
$form = new UserForm();

// Create with model
$form = new UserForm($user);

// Validate
$validated = $form->validate($request->all());

// Render form
{!! $form->render() !!}

// Render field
{!! $form->field('name')->render() !!}
```
