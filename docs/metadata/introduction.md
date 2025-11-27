# Metadata System

## Introduction

NeoFramework's Metadata System leverages PHP 8 attributes to provide a modern, declarative way to define model structure, validation rules, relationships, and UI generation hints. This powerful system eliminates repetitive configuration and enables automatic form generation, validation, and database schema management.

## Overview

The metadata system uses PHP attributes (annotations) to attach metadata directly to your model classes and properties. This approach offers several advantages:

- **Type Safety**: Attributes are validated at compile time
- **IDE Support**: Full autocomplete and inspection capabilities
- **Readability**: Configuration lives with the code it describes
- **Consistency**: Single source of truth for model behavior
- **Automation**: Enable code generation and scaffolding

## Core Concepts

### PHP 8 Attributes

Attributes are a form of structured metadata that can be added to declarations in your code:

```php
<?php

namespace App\Models;

use Neo\Metadata\Attributes\Table;
use Neo\Metadata\Attributes\Column;
use Neo\Metadata\Attributes\Validation;

#[Table(name: 'users', timestamps: true)]
class User extends Model
{
    #[Column(type: 'string', length: 255)]
    #[Validation(rules: 'required|email|unique:users,email')]
    public string $email;
    
    #[Column(type: 'string', length: 255)]
    #[Validation(rules: 'required|min:8')]
    #[Hidden]
    public string $password;
}
```

### Metadata Reader

The `MetadataReader` class extracts and processes attributes from your models:

```php
<?php

use Neo\Metadata\MetadataReader;

$reader = new MetadataReader();
$metadata = $reader->getClassMetadata(User::class);

// Access table information
$tableName = $metadata->getTableName(); // 'users'
$hasTimestamps = $metadata->hasTimestamps(); // true

// Access column information
$columns = $metadata->getColumns();
foreach ($columns as $column) {
    echo $column->getName() . ': ' . $column->getType();
}
```

### Metadata Cache

For performance, metadata is cached after the first read:

```php
<?php

use Neo\Metadata\MetadataCache;

$cache = new MetadataCache();

// Cache is automatically used by MetadataReader
$reader = new MetadataReader($cache);
$metadata = $reader->getClassMetadata(User::class);

// Clear cache when needed
$cache->clear();
$cache->clearClass(User::class);
```

## Attribute Categories

### Table-Level Attributes

Define table structure and behavior:

```php
<?php

#[Table(name: 'products', engine: 'InnoDB')]
#[SoftDeletes]
#[Timestamps]
#[Index(columns: ['category_id', 'status'])]
class Product extends Model
{
    // ...
}
```

### Field-Level Attributes

Define column properties and behavior:

```php
<?php

class Product extends Model
{
    #[Column(type: 'integer', autoIncrement: true)]
    #[PrimaryKey]
    public int $id;
    
    #[Column(type: 'string', length: 255)]
    #[Validation(rules: 'required|max:255')]
    #[Searchable]
    public string $name;
    
    #[Column(type: 'decimal', precision: 10, scale: 2)]
    #[Validation(rules: 'required|numeric|min:0')]
    public float $price;
    
    #[Column(type: 'text', nullable: true)]
    #[Validation(rules: 'nullable|max:1000')]
    public ?string $description;
}
```

### Relationship Attributes

Define model relationships:

```php
<?php

class User extends Model
{
    #[HasMany(target: Post::class, foreignKey: 'user_id')]
    public Collection $posts;
    
    #[HasOne(target: Profile::class, foreignKey: 'user_id')]
    public ?Profile $profile;
    
    #[BelongsToMany(
        target: Role::class,
        pivotTable: 'role_user',
        foreignKey: 'user_id',
        relatedKey: 'role_id'
    )]
    public Collection $roles;
}
```

### Validation Attributes

Define validation rules:

```php
<?php

class User extends Model
{
    #[Validation(rules: 'required|email|unique:users,email')]
    #[ValidationMessage(
        'required' => 'Email address is required',
        'email' => 'Please enter a valid email address',
        'unique' => 'This email is already registered'
    )]
    public string $email;
    
    #[Validation(rules: 'required|min:8|confirmed')]
    #[ValidationMessage('confirmed' => 'Passwords do not match')]
    public string $password;
}
```

### UI Attributes

Define UI generation hints:

```php
<?php

class Product extends Model
{
    #[FormField(type: 'text', label: 'Product Name')]
    #[Placeholder('Enter product name')]
    public string $name;
    
    #[FormField(type: 'textarea', label: 'Description')]
    #[Rows(5)]
    public ?string $description;
    
    #[FormField(type: 'select', label: 'Category')]
    #[Options(source: 'categories', valueField: 'id', labelField: 'name')]
    public int $category_id;
    
    #[Hidden]
    public string $internal_code;
}
```

## Using Metadata in Your Application

### Automatic Validation

Validate model instances using metadata:

```php
<?php

use Neo\Metadata\MetadataValidator;

$user = new User();
$user->email = 'invalid-email';
$user->password = '123';

$validator = new MetadataValidator();
$result = $validator->validate($user);

if ($result->fails()) {
    foreach ($result->errors() as $field => $messages) {
        echo "$field: " . implode(', ', $messages) . "\n";
    }
}
```

### Form Generation

Generate forms automatically from model metadata:

```php
<?php

use Neo\Forms\FormGenerator;

$generator = new FormGenerator();
$form = $generator->generateFromModel(User::class);

// Render in view
echo $form->render();
```

### Migration Generation

Generate migrations from model metadata:

```php
<?php

use Neo\Generator\MigrationGenerator;

$generator = new MigrationGenerator();
$migration = $generator->generateFromModel(User::class);

file_put_contents(
    database_path('migrations/' . date('Y_m_d_His') . '_create_users_table.php'),
    $migration
);
```

### API Documentation

Generate API documentation from metadata:

```php
<?php

use Neo\Metadata\ApiDocGenerator;

$generator = new ApiDocGenerator();
$docs = $generator->generateForModel(User::class);

// Returns OpenAPI/Swagger compatible documentation
echo json_encode($docs, JSON_PRETTY_PRINT);
```

## Advanced Usage

### Custom Attributes

Create your own attribute types:

```php
<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Encrypted
{
    public function __construct(
        public string $algorithm = 'AES-256-CBC'
    ) {}
}
```

Use in models:

```php
<?php

class User extends Model
{
    #[Column(type: 'text')]
    #[Encrypted]
    public string $sensitive_data;
}
```

Handle in your code:

```php
<?php

use App\Attributes\Encrypted;
use Neo\Metadata\MetadataReader;

$reader = new MetadataReader();
$metadata = $reader->getClassMetadata(User::class);

$property = $metadata->getProperty('sensitive_data');
$encrypted = $property->getAttribute(Encrypted::class);

if ($encrypted) {
    // Handle encryption/decryption
    $algorithm = $encrypted->algorithm;
}
```

### Conditional Attributes

Apply attributes conditionally:

```php
<?php

class Product extends Model
{
    #[Column(type: 'decimal', precision: 10, scale: 2)]
    #[Validation(rules: 'required|numeric|min:0')]
    #[FormField(
        type: 'number',
        label: 'Price',
        attributes: ['step' => '0.01', 'min' => '0']
    )]
    public float $price;
    
    #[Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Validation(rules: 'nullable|numeric|min:0|lt:price')]
    #[FormField(
        type: 'number',
        label: 'Sale Price',
        attributes: ['step' => '0.01', 'min' => '0']
    )]
    #[ConditionalDisplay(when: 'on_sale', equals: true)]
    public ?float $sale_price;
}
```

### Attribute Inheritance

Attributes can be inherited from parent classes:

```php
<?php

#[Table(timestamps: true)]
#[SoftDeletes]
abstract class BaseModel extends Model
{
    #[Column(type: 'integer', autoIncrement: true)]
    #[PrimaryKey]
    public int $id;
}

// User inherits timestamps, soft deletes, and id column
#[Table(name: 'users')]
class User extends BaseModel
{
    #[Column(type: 'string', length: 255)]
    public string $email;
}
```

### Metadata Events

Listen to metadata-related events:

```php
<?php

use Neo\Events\Event;
use Neo\Metadata\Events\MetadataLoaded;
use Neo\Metadata\Events\MetadataCached;

Event::listen(MetadataLoaded::class, function (MetadataLoaded $event) {
    $class = $event->class;
    $metadata = $event->metadata;
    
    // Process metadata after loading
    log_info("Metadata loaded for {$class}");
});

Event::listen(MetadataCached::class, function (MetadataCached $event) {
    // Metadata was cached
    log_info("Metadata cached for {$event->class}");
});
```

## Configuration

Configure the metadata system in `config/metadata.php`:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Metadata Cache
    |--------------------------------------------------------------------------
    |
    | Enable or disable metadata caching. In production, caching should be
    | enabled for better performance.
    |
    */
    'cache' => [
        'enabled' => env('METADATA_CACHE', true),
        'driver' => env('METADATA_CACHE_DRIVER', 'file'),
        'ttl' => env('METADATA_CACHE_TTL', 3600),
        'prefix' => 'metadata:',
    ],

    /*
    |--------------------------------------------------------------------------
    | Attribute Namespaces
    |--------------------------------------------------------------------------
    |
    | Define namespaces to scan for custom attributes.
    |
    */
    'namespaces' => [
        'Neo\\Metadata\\Attributes',
        'App\\Attributes',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-discovery
    |--------------------------------------------------------------------------
    |
    | Automatically discover and cache metadata for all models on boot.
    |
    */
    'auto_discovery' => [
        'enabled' => env('METADATA_AUTO_DISCOVERY', false),
        'paths' => [
            app_path('Models'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    |
    | Configure metadata validation behavior.
    |
    */
    'validation' => [
        'auto_validate' => true,
        'throw_on_error' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Form Generation
    |--------------------------------------------------------------------------
    |
    | Configure form generation defaults.
    |
    */
    'forms' => [
        'default_theme' => 'bootstrap5',
        'generate_labels' => true,
        'generate_placeholders' => true,
        'generate_help_text' => true,
    ],
];
```

## Performance Considerations

### Caching Strategy

Always enable caching in production:

```php
<?php

// In production, metadata is cached after first read
$reader = new MetadataReader(app('metadata.cache'));

// Clear cache after model changes
php neo metadata:clear
```

### Lazy Loading

Metadata is loaded lazily by default:

```php
<?php

// Metadata is not loaded until accessed
$metadata = $reader->getClassMetadata(User::class);

// First access triggers loading and caching
$columns = $metadata->getColumns();

// Subsequent accesses use cached data
$relationships = $metadata->getRelationships();
```

### Preloading

Preload metadata for better performance:

```php
<?php

// In AppServiceProvider
public function boot()
{
    if (config('metadata.auto_discovery.enabled')) {
        $reader = app(MetadataReader::class);
        $models = $this->discoverModels();
        
        foreach ($models as $model) {
            $reader->getClassMetadata($model);
        }
    }
}
```

## Best Practices

1. **Use Attributes Consistently**: Apply attributes to all model properties for complete metadata coverage

2. **Group Related Attributes**: Place related attributes together for better readability

3. **Leverage Inheritance**: Define common attributes in base classes

4. **Cache in Production**: Always enable metadata caching in production environments

5. **Document Custom Attributes**: Provide clear documentation for custom attribute types

6. **Validate Early**: Use metadata validation during development to catch issues

7. **Keep It Simple**: Don't over-complicate attribute usage; start simple and add as needed

8. **Test Thoroughly**: Write tests for models with complex metadata configurations

## Next Steps

- Learn about [Field Attributes](field-attributes.md)
- Explore [Table Attributes](table-attributes.md)
- Understand [Relationship Attributes](relationships.md)
- Discover [Form Generation](form-generation.md)
- Master [Validation via Attributes](validation.md)
