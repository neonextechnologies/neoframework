# Form Generation from Metadata

## Introduction

NeoFramework's form generation system automatically creates HTML forms from model metadata. By leveraging PHP 8 attributes on your models, you can generate complete, validated, and styled forms with minimal code.

## Basic Form Generation

### Simple Form Generation

Generate a basic form from a model:

```php
<?php

use Neo\Forms\FormGenerator;
use App\Models\User;

$generator = new FormGenerator();
$form = $generator->generateFromModel(User::class);

// In your view
echo $form->render();
```

### Specifying Fields

Generate forms with specific fields:

```php
<?php

$form = $generator->generateFromModel(User::class, [
    'fields' => ['name', 'email', 'password', 'bio']
]);

echo $form->render();
```

### Excluding Fields

Exclude certain fields from generation:

```php
<?php

$form = $generator->generateFromModel(User::class, [
    'exclude' => ['id', 'created_at', 'updated_at', 'deleted_at']
]);

echo $form->render();
```

## Form Field Attributes

### FormField Attribute

Define form field properties:

```php
<?php

namespace App\Models;

use Neo\Database\Model;
use Neo\Metadata\Attributes\FormField;

class User extends Model
{
    #[FormField(
        type: 'text',
        label: 'Full Name',
        placeholder: 'Enter your full name',
        required: true
    )]
    public string $name;
    
    #[FormField(
        type: 'email',
        label: 'Email Address',
        placeholder: 'you@example.com',
        required: true
    )]
    public string $email;
    
    #[FormField(
        type: 'password',
        label: 'Password',
        required: true,
        autocomplete: 'new-password'
    )]
    public string $password;
    
    #[FormField(
        type: 'textarea',
        label: 'Bio',
        rows: 5,
        placeholder: 'Tell us about yourself'
    )]
    public ?string $bio;
}
```

### Field Types

Supported field types:

```php
<?php

class Product extends Model
{
    // Text input
    #[FormField(type: 'text', label: 'Product Name')]
    public string $name;
    
    // Email input
    #[FormField(type: 'email', label: 'Contact Email')]
    public string $email;
    
    // Password input
    #[FormField(type: 'password', label: 'Password')]
    public string $password;
    
    // Number input
    #[FormField(
        type: 'number',
        label: 'Price',
        min: 0,
        max: 99999,
        step: 0.01
    )]
    public float $price;
    
    // Textarea
    #[FormField(
        type: 'textarea',
        label: 'Description',
        rows: 5,
        cols: 50
    )]
    public string $description;
    
    // Select dropdown
    #[FormField(
        type: 'select',
        label: 'Category',
        options: ['electronics', 'clothing', 'books', 'toys']
    )]
    public string $category;
    
    // Checkbox
    #[FormField(
        type: 'checkbox',
        label: 'Featured Product'
    )]
    public bool $is_featured;
    
    // Radio buttons
    #[FormField(
        type: 'radio',
        label: 'Status',
        options: ['draft', 'published', 'archived']
    )]
    public string $status;
    
    // Date input
    #[FormField(
        type: 'date',
        label: 'Release Date'
    )]
    public DateTime $release_date;
    
    // Time input
    #[FormField(
        type: 'time',
        label: 'Available From'
    )]
    public DateTime $available_time;
    
    // DateTime input
    #[FormField(
        type: 'datetime-local',
        label: 'Event Date'
    )]
    public DateTime $event_datetime;
    
    // File upload
    #[FormField(
        type: 'file',
        label: 'Product Image',
        accept: 'image/*'
    )]
    public string $image;
    
    // Hidden input
    #[FormField(type: 'hidden')]
    public string $token;
    
    // Color picker
    #[FormField(
        type: 'color',
        label: 'Theme Color'
    )]
    public string $color;
    
    // Range slider
    #[FormField(
        type: 'range',
        label: 'Priority',
        min: 1,
        max: 10,
        step: 1
    )]
    public int $priority;
    
    // URL input
    #[FormField(
        type: 'url',
        label: 'Website'
    )]
    public string $website;
    
    // Tel input
    #[FormField(
        type: 'tel',
        label: 'Phone Number'
    )]
    public string $phone;
}
```

## Dynamic Options

### Database Options

Load options from database:

```php
<?php

class Product extends Model
{
    #[FormField(
        type: 'select',
        label: 'Category',
        optionsFrom: Category::class,
        optionValue: 'id',
        optionLabel: 'name'
    )]
    public int $category_id;
    
    #[FormField(
        type: 'select',
        label: 'Brand',
        optionsQuery: [Brand::class, 'active'],
        optionValue: 'id',
        optionLabel: 'name'
    )]
    public int $brand_id;
}

// In Brand model
public static function active()
{
    return static::where('is_active', true)->orderBy('name');
}
```

### Closure Options

Generate options dynamically:

```php
<?php

class Order extends Model
{
    #[FormField(
        type: 'select',
        label: 'Country',
        optionsClosure: function() {
            return [
                'US' => 'United States',
                'CA' => 'Canada',
                'GB' => 'United Kingdom',
                'AU' => 'Australia'
            ];
        }
    )]
    public string $country;
    
    #[FormField(
        type: 'select',
        label: 'Year',
        optionsClosure: function() {
            $currentYear = date('Y');
            return range($currentYear - 10, $currentYear + 5);
        }
    )]
    public int $year;
}
```

### Grouped Options

Create optgroup elements:

```php
<?php

class Product extends Model
{
    #[FormField(
        type: 'select',
        label: 'Category',
        groupedOptions: [
            'Electronics' => [
                1 => 'Computers',
                2 => 'Phones',
                3 => 'Tablets'
            ],
            'Clothing' => [
                4 => 'Shirts',
                5 => 'Pants',
                6 => 'Shoes'
            ]
        ]
    )]
    public int $category_id;
}
```

## Form Layouts

### Vertical Layout

Default vertical form layout:

```php
<?php

$form = $generator->generateFromModel(User::class, [
    'layout' => 'vertical'
]);

echo $form->render();
```

Generated HTML:
```html
<form method="POST">
    <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" required>
    </div>
    <!-- More fields... -->
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

### Horizontal Layout

Bootstrap horizontal form layout:

```php
<?php

$form = $generator->generateFromModel(User::class, [
    'layout' => 'horizontal',
    'labelColumns' => 3,
    'inputColumns' => 9
]);

echo $form->render();
```

Generated HTML:
```html
<form method="POST" class="form-horizontal">
    <div class="form-group row">
        <label for="name" class="col-sm-3 col-form-label">Full Name</label>
        <div class="col-sm-9">
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
    </div>
    <!-- More fields... -->
</form>
```

### Inline Layout

Compact inline form layout:

```php
<?php

$form = $generator->generateFromModel(User::class, [
    'layout' => 'inline',
    'fields' => ['email', 'password']
]);

echo $form->render();
```

## Form Themes

### Bootstrap 5 Theme

```php
<?php

$form = $generator->generateFromModel(User::class, [
    'theme' => 'bootstrap5'
]);

echo $form->render();
```

### Tailwind CSS Theme

```php
<?php

$form = $generator->generateFromModel(User::class, [
    'theme' => 'tailwind'
]);

echo $form->render();
```

### Material Design Theme

```php
<?php

$form = $generator->generateFromModel(User::class, [
    'theme' => 'material'
]);

echo $form->render();
```

### Custom Theme

Define custom theme:

```php
<?php

// config/forms.php
return [
    'themes' => [
        'custom' => [
            'form_class' => 'my-form',
            'form_group_class' => 'field-wrapper',
            'label_class' => 'field-label',
            'input_class' => 'field-input',
            'error_class' => 'field-error',
            'button_class' => 'btn btn-submit',
        ]
    ]
];

// Use custom theme
$form = $generator->generateFromModel(User::class, [
    'theme' => 'custom'
]);
```

## Form Validation

### Automatic Validation

Validation rules from metadata:

```php
<?php

use Neo\Metadata\Attributes\Validation;

class User extends Model
{
    #[FormField(type: 'text', label: 'Name')]
    #[Validation(rules: 'required|max:255')]
    public string $name;
    
    #[FormField(type: 'email', label: 'Email')]
    #[Validation(rules: 'required|email|unique:users,email')]
    public string $email;
    
    #[FormField(type: 'password', label: 'Password')]
    #[Validation(rules: 'required|min:8|confirmed')]
    public string $password;
}

// Form includes validation
$form = $generator->generateFromModel(User::class);

// Server-side validation
$validator = $form->validate($request->all());

if ($validator->fails()) {
    return back()->withErrors($validator)->withInput();
}
```

### Client-Side Validation

Generate HTML5 validation attributes:

```php
<?php

$form = $generator->generateFromModel(User::class, [
    'clientValidation' => true
]);

echo $form->render();
```

Generated HTML:
```html
<input type="text" name="name" required maxlength="255">
<input type="email" name="email" required>
<input type="password" name="password" required minlength="8">
```

### Custom Validation Messages

```php
<?php

use Neo\Metadata\Attributes\ValidationMessage;

class User extends Model
{
    #[FormField(type: 'email', label: 'Email')]
    #[Validation(rules: 'required|email|unique:users,email')]
    #[ValidationMessage(
        'required' => 'Please enter your email address',
        'email' => 'Please enter a valid email address',
        'unique' => 'This email is already registered'
    )]
    public string $email;
}
```

## Advanced Form Features

### Conditional Fields

Show/hide fields based on conditions:

```php
<?php

use Neo\Metadata\Attributes\ConditionalField;

class Product extends Model
{
    #[FormField(type: 'checkbox', label: 'On Sale')]
    public bool $on_sale;
    
    #[FormField(
        type: 'number',
        label: 'Sale Price',
        step: 0.01
    )]
    #[ConditionalField(
        dependsOn: 'on_sale',
        showWhen: true
    )]
    public ?float $sale_price;
    
    #[FormField(type: 'select', label: 'Type', options: ['physical', 'digital'])]
    public string $type;
    
    #[FormField(
        type: 'number',
        label: 'Weight (kg)',
        step: 0.01
    )]
    #[ConditionalField(
        dependsOn: 'type',
        showWhen: 'physical'
    )]
    public ?float $weight;
}
```

### Field Groups

Group related fields:

```php
<?php

use Neo\Metadata\Attributes\FieldGroup;

class User extends Model
{
    #[FormField(type: 'text', label: 'First Name')]
    #[FieldGroup('personal')]
    public string $first_name;
    
    #[FormField(type: 'text', label: 'Last Name')]
    #[FieldGroup('personal')]
    public string $last_name;
    
    #[FormField(type: 'date', label: 'Date of Birth')]
    #[FieldGroup('personal')]
    public DateTime $birth_date;
    
    #[FormField(type: 'text', label: 'Street Address')]
    #[FieldGroup('address')]
    public string $street;
    
    #[FormField(type: 'text', label: 'City')]
    #[FieldGroup('address')]
    public string $city;
    
    #[FormField(type: 'text', label: 'Postal Code')]
    #[FieldGroup('address')]
    public string $postal_code;
}

// Generate form with groups
$form = $generator->generateFromModel(User::class, [
    'groups' => [
        'personal' => ['title' => 'Personal Information'],
        'address' => ['title' => 'Address Information']
    ]
]);
```

### Repeatable Fields

Create repeatable field sets:

```php
<?php

use Neo\Metadata\Attributes\Repeatable;

class Order extends Model
{
    #[FormField(type: 'hidden')]
    public int $id;
    
    #[Repeatable(
        fields: ['product_id', 'quantity', 'price'],
        minItems: 1,
        maxItems: 10,
        addButtonText: 'Add Item',
        removeButtonText: 'Remove'
    )]
    public array $items;
}
```

### File Upload Fields

Handle file uploads:

```php
<?php

use Neo\Metadata\Attributes\FileUpload;

class Product extends Model
{
    #[FormField(
        type: 'file',
        label: 'Product Image',
        accept: 'image/*'
    )]
    #[FileUpload(
        maxSize: '2MB',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        uploadPath: 'products',
        generateThumbnail: true
    )]
    public string $image;
    
    #[FormField(
        type: 'file',
        label: 'Gallery Images',
        multiple: true
    )]
    #[FileUpload(
        maxSize: '5MB',
        maxFiles: 5,
        mimeTypes: ['image/*']
    )]
    public array $gallery;
    
    #[FormField(
        type: 'file',
        label: 'Product Manual (PDF)'
    )]
    #[FileUpload(
        maxSize: '10MB',
        mimeTypes: ['application/pdf']
    )]
    public ?string $manual;
}
```

### Rich Text Editors

Integrate WYSIWYG editors:

```php
<?php

use Neo\Metadata\Attributes\RichText;

class Post extends Model
{
    #[FormField(type: 'textarea', label: 'Content')]
    #[RichText(
        editor: 'tinymce',
        toolbar: ['bold', 'italic', 'link', 'image'],
        height: 400
    )]
    public string $content;
    
    #[FormField(type: 'textarea', label: 'Description')]
    #[RichText(
        editor: 'quill',
        modules: ['toolbar', 'image', 'link']
    )]
    public string $description;
}
```

### Date Pickers

Use date picker components:

```php
<?php

use Neo\Metadata\Attributes\DatePicker;

class Event extends Model
{
    #[FormField(type: 'text', label: 'Event Date')]
    #[DatePicker(
        format: 'Y-m-d',
        minDate: 'today',
        maxDate: '+1 year'
    )]
    public DateTime $event_date;
    
    #[FormField(type: 'text', label: 'Start Time')]
    #[DatePicker(
        type: 'time',
        format: 'H:i',
        minuteStep: 15
    )]
    public DateTime $start_time;
    
    #[FormField(type: 'text', label: 'Date Range')]
    #[DatePicker(
        type: 'range',
        format: 'Y-m-d'
    )]
    public array $date_range;
}
```

## Form Submission

### Handling Form Data

Process submitted form data:

```php
<?php

use Neo\Http\Request;
use App\Models\User;

public function store(Request $request)
{
    $form = $generator->generateFromModel(User::class);
    
    // Validate
    $validated = $form->validate($request->all());
    
    // Create model instance
    $user = $form->fill(new User(), $validated);
    $user->save();
    
    return redirect()->route('users.index')
        ->with('success', 'User created successfully');
}
```

### Update Forms

Pre-fill forms for editing:

```php
<?php

public function edit(int $id)
{
    $user = User::findOrFail($id);
    
    $form = $generator->generateFromModel(User::class, [
        'method' => 'PUT',
        'action' => route('users.update', $user->id),
        'model' => $user  // Pre-fill with existing data
    ]);
    
    return view('users.edit', compact('form'));
}

public function update(Request $request, int $id)
{
    $user = User::findOrFail($id);
    $form = $generator->generateFromModel(User::class);
    
    $validated = $form->validate($request->all());
    $form->fill($user, $validated);
    $user->save();
    
    return redirect()->route('users.show', $user->id)
        ->with('success', 'User updated successfully');
}
```

## Form Components

### Submit Button

Customize submit button:

```php
<?php

$form = $generator->generateFromModel(User::class, [
    'submitButton' => [
        'text' => 'Create Account',
        'class' => 'btn btn-primary btn-lg',
        'icon' => 'fa-check'
    ]
]);
```

### Cancel Button

Add cancel button:

```php
<?php

$form = $generator->generateFromModel(User::class, [
    'cancelButton' => [
        'text' => 'Cancel',
        'url' => route('users.index'),
        'class' => 'btn btn-secondary'
    ]
]);
```

### Custom Buttons

Add additional buttons:

```php
<?php

$form = $generator->generateFromModel(User::class, [
    'buttons' => [
        [
            'text' => 'Save Draft',
            'name' => 'save_draft',
            'value' => '1',
            'class' => 'btn btn-outline-secondary'
        ],
        [
            'text' => 'Publish',
            'name' => 'publish',
            'value' => '1',
            'class' => 'btn btn-success'
        ]
    ]
]);
```

## Form Rendering

### Render Entire Form

```php
<?php

// In controller
$form = $generator->generateFromModel(User::class);
return view('users.create', compact('form'));

// In view
<div class="container">
    <h1>Create User</h1>
    <?= $form->render() ?>
</div>
```

### Render Individual Fields

```php
<?php

// In view
<form method="POST" action="<?= route('users.store') ?>">
    <?= csrf_field() ?>
    
    <div class="row">
        <div class="col-md-6">
            <?= $form->renderField('first_name') ?>
        </div>
        <div class="col-md-6">
            <?= $form->renderField('last_name') ?>
        </div>
    </div>
    
    <?= $form->renderField('email') ?>
    <?= $form->renderField('password') ?>
    
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

### Custom Field Rendering

```php
<?php

// Custom field template
$form->setFieldTemplate('email', function($field) {
    return <<<HTML
        <div class="custom-field">
            <label class="custom-label">{$field->label}</label>
            <div class="input-wrapper">
                <i class="icon-email"></i>
                <input type="{$field->type}" 
                       name="{$field->name}" 
                       class="custom-input"
                       placeholder="{$field->placeholder}">
            </div>
            {$field->renderErrors()}
        </div>
    HTML;
});
```

## Configuration

Configure form generation in `config/forms.php`:

```php
<?php

return [
    'default_theme' => 'bootstrap5',
    
    'default_layout' => 'vertical',
    
    'default_options' => [
        'clientValidation' => true,
        'showLabels' => true,
        'showPlaceholders' => true,
        'showHelpText' => true,
        'asteriskRequired' => true,
    ],
    
    'button_defaults' => [
        'submit' => [
            'text' => 'Submit',
            'class' => 'btn btn-primary'
        ],
        'cancel' => [
            'text' => 'Cancel',
            'class' => 'btn btn-secondary'
        ]
    ],
    
    'file_upload' => [
        'max_size' => '10MB',
        'upload_path' => 'uploads',
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
    ],
];
```

## Best Practices

1. **Use Attributes Consistently**: Define all form field attributes on models
2. **Validate Server-Side**: Always validate on server, client validation is optional
3. **Group Related Fields**: Use field groups for better organization
4. **Choose Appropriate Field Types**: Select the right input type for data
5. **Provide Clear Labels**: Use descriptive, user-friendly labels
6. **Add Help Text**: Include helpful hints for complex fields
7. **Handle File Uploads Properly**: Validate file types and sizes
8. **Theme Consistently**: Use same theme throughout application
9. **Test Forms Thoroughly**: Test all field types and validation rules
10. **Optimize for Mobile**: Ensure forms are responsive

## Next Steps

- Learn about [Validation](validation.md)
- Explore [Field Attributes](field-attributes.md)
- Return to [Metadata Introduction](introduction.md)
