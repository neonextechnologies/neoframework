# Metadata & Attributes 🏷️

## Introduction

NeoFramework leverages PHP 8's powerful Attribute system to enable metadata-driven development. Attributes allow you to attach structured metadata to classes, properties, methods, and parameters, enabling declarative programming patterns that reduce boilerplate and improve code clarity.

With attributes, you can define database schemas, validation rules, API documentation, relationships, and more directly in your code using native PHP syntax. This approach is cleaner, type-safe, and IDE-friendly compared to traditional annotations or configuration arrays.

## Understanding PHP 8 Attributes

Attributes (also known as annotations in other languages) are a form of structured metadata that can be attached to declarations in your code. They're native to PHP 8+ and provide a clean, standardized way to add metadata.

### Basic Attribute Syntax

```php
// Simple attribute
#[Table('users')]
class User {}

// Attribute with parameters
#[Field('email', type: 'string', unique: true)]
public string $email;

// Multiple attributes
#[Required]
#[Email]
#[MaxLength(255)]
public string $email;

// Attribute with named arguments
#[HasMany(
    model: Post::class,
    foreignKey: 'user_id',
    localKey: 'id'
)]
public function posts() {}
```

## Built-in Attributes 📦

### Table Attribute

Define the database table for a model:

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;
use NeoPhp\Metadata\Table;

#[Table(name: 'users', connection: 'mysql', engine: 'InnoDB')]
class User extends Model
{
    // Model properties
}

// Usage in framework
$reflection = new ReflectionClass(User::class);
$attributes = $reflection->getAttributes(Table::class);

if (!empty($attributes)) {
    $table = $attributes[0]->newInstance();
    echo $table->name; // 'users'
}
```

### Field Attribute

Define field metadata for model properties:

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;
use NeoPhp\Metadata\Field;
use NeoPhp\Metadata\Table;

#[Table('users')]
class User extends Model
{
    #[Field(
        name: 'email',
        type: 'string',
        length: 255,
        unique: true,
        nullable: false,
        label: 'Email Address',
        inputType: 'email',
        validation: ['required', 'email']
    )]
    public string $email;
    
    #[Field(
        name: 'age',
        type: 'integer',
        nullable: true,
        min: 0,
        max: 150,
        label: 'Age',
        inputType: 'number'
    )]
    public ?int $age = null;
    
    #[Field(
        name: 'bio',
        type: 'text',
        nullable: true,
        label: 'Biography',
        inputType: 'textarea',
        placeholder: 'Tell us about yourself...',
        maxLength: 1000
    )]
    public ?string $bio = null;
    
    #[Field(
        name: 'status',
        type: 'enum',
        enum: ['active', 'inactive', 'suspended'],
        default: 'active',
        label: 'Account Status'
    )]
    public string $status = 'active';
}
```

### Relationship Attributes

Define model relationships using attributes:

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;
use NeoPhp\Metadata\HasMany;
use NeoPhp\Metadata\HasOne;
use NeoPhp\Metadata\BelongsTo;
use NeoPhp\Metadata\BelongsToMany;

class User extends Model
{
    #[HasMany(
        model: Post::class,
        foreignKey: 'user_id',
        localKey: 'id'
    )]
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    
    #[HasOne(
        model: Profile::class,
        foreignKey: 'user_id',
        localKey: 'id'
    )]
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
    
    #[BelongsToMany(
        model: Role::class,
        pivotTable: 'user_roles',
        foreignKey: 'user_id',
        relatedKey: 'role_id'
    )]
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }
}

class Post extends Model
{
    #[BelongsTo(
        model: User::class,
        foreignKey: 'user_id',
        ownerKey: 'id'
    )]
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

## Creating Custom Attributes 🎨

### Basic Custom Attribute

```php
<?php

namespace App\Metadata;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ApiResource
{
    public function __construct(
        public string $endpoint,
        public string $version = 'v1',
        public array $methods = ['GET', 'POST', 'PUT', 'DELETE'],
        public bool $authenticated = true
    ) {}
}

// Usage
#[ApiResource(
    endpoint: '/api/users',
    version: 'v2',
    methods: ['GET', 'POST']
)]
class UserController
{
    // Controller methods
}
```

### Validation Attribute

```php
<?php

namespace App\Metadata;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Validate
{
    public function __construct(
        public string $rule,
        public ?string $message = null,
        public array $params = []
    ) {}
}

// Usage
class CreateUserRequest
{
    #[Validate('required', message: 'Name is required')]
    #[Validate('string')]
    #[Validate('min:3', message: 'Name must be at least 3 characters')]
    #[Validate('max:255')]
    public string $name;
    
    #[Validate('required')]
    #[Validate('email', message: 'Please provide a valid email address')]
    #[Validate('unique:users,email')]
    public string $email;
    
    #[Validate('required')]
    #[Validate('min:8', message: 'Password must be at least 8 characters')]
    #[Validate('confirmed')]
    public string $password;
}
```

### Route Attribute

```php
<?php

namespace App\Metadata;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    public function __construct(
        public string $method,
        public string $path,
        public ?string $name = null,
        public array $middleware = []
    ) {}
}

#[Attribute(Attribute::TARGET_METHOD)]
class Get extends Route
{
    public function __construct(
        string $path,
        ?string $name = null,
        array $middleware = []
    ) {
        parent::__construct('GET', $path, $name, $middleware);
    }
}

#[Attribute(Attribute::TARGET_METHOD)]
class Post extends Route
{
    public function __construct(
        string $path,
        ?string $name = null,
        array $middleware = []
    ) {
        parent::__construct('POST', $path, $name, $middleware);
    }
}

// Usage
class UserController
{
    #[Get('/users', name: 'users.index', middleware: ['auth'])]
    public function index()
    {
        return User::all();
    }
    
    #[Post('/users', name: 'users.store', middleware: ['auth', 'admin'])]
    public function store(Request $request)
    {
        return User::create($request->validated());
    }
    
    #[Get('/users/{id}', name: 'users.show')]
    public function show(int $id)
    {
        return User::findOrFail($id);
    }
}
```

## Reading Metadata 🔍

### The MetadataRepository

NeoFramework provides a `MetadataRepository` for reading and caching attribute metadata:

```php
<?php

namespace NeoPhp\Metadata;

use ReflectionClass;
use ReflectionProperty;
use ReflectionMethod;

class MetadataRepository
{
    protected array $cache = [];
    
    /**
     * Get class metadata
     */
    public function getClassMetadata(string $class): array
    {
        if (isset($this->cache[$class])) {
            return $this->cache[$class];
        }
        
        $reflection = new ReflectionClass($class);
        $metadata = [
            'class' => $this->getClassAttributes($reflection),
            'properties' => $this->getPropertyAttributes($reflection),
            'methods' => $this->getMethodAttributes($reflection),
        ];
        
        return $this->cache[$class] = $metadata;
    }
    
    /**
     * Get class-level attributes
     */
    protected function getClassAttributes(ReflectionClass $reflection): array
    {
        $attributes = [];
        
        foreach ($reflection->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();
            $attributes[get_class($instance)] = $instance;
        }
        
        return $attributes;
    }
    
    /**
     * Get property attributes
     */
    protected function getPropertyAttributes(ReflectionClass $reflection): array
    {
        $properties = [];
        
        foreach ($reflection->getProperties() as $property) {
            $attributes = [];
            
            foreach ($property->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();
                $name = get_class($instance);
                
                // Handle repeatable attributes
                if (isset($attributes[$name])) {
                    if (!is_array($attributes[$name])) {
                        $attributes[$name] = [$attributes[$name]];
                    }
                    $attributes[$name][] = $instance;
                } else {
                    $attributes[$name] = $instance;
                }
            }
            
            if (!empty($attributes)) {
                $properties[$property->getName()] = $attributes;
            }
        }
        
        return $properties;
    }
    
    /**
     * Get specific attribute from class
     */
    public function getClassAttribute(string $class, string $attributeClass): ?object
    {
        $metadata = $this->getClassMetadata($class);
        return $metadata['class'][$attributeClass] ?? null;
    }
    
    /**
     * Get specific attribute from property
     */
    public function getPropertyAttribute(
        string $class,
        string $property,
        string $attributeClass
    ): mixed {
        $metadata = $this->getClassMetadata($class);
        return $metadata['properties'][$property][$attributeClass] ?? null;
    }
}
```

### Using the MetadataRepository

```php
use NeoPhp\Metadata\MetadataRepository;
use App\Models\User;

$repository = new MetadataRepository();

// Get table information
$table = $repository->getClassAttribute(User::class, Table::class);
echo $table->name; // 'users'

// Get field metadata
$emailField = $repository->getPropertyAttribute(
    User::class,
    'email',
    Field::class
);

echo $emailField->type; // 'string'
echo $emailField->unique; // true

// Get all metadata for a class
$metadata = $repository->getClassMetadata(User::class);
```

## Practical Applications 🚀

### Auto-Generate Database Migrations

```php
<?php

namespace App\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Metadata\MetadataRepository;
use NeoPhp\Metadata\Table;
use NeoPhp\Metadata\Field;

class GenerateMigrationCommand extends Command
{
    public function handle(MetadataRepository $metadata): void
    {
        $modelClass = $this->argument('model');
        
        $tableAttr = $metadata->getClassAttribute($modelClass, Table::class);
        if (!$tableAttr) {
            $this->error('Model does not have Table attribute');
            return;
        }
        
        $this->info("Generating migration for {$tableAttr->name}...");
        
        $fields = $this->extractFields($modelClass, $metadata);
        $migration = $this->generateMigrationCode($tableAttr->name, $fields);
        
        $filename = date('Y_m_d_His') . "_create_{$tableAttr->name}_table.php";
        file_put_contents(
            database_path("migrations/{$filename}"),
            $migration
        );
        
        $this->info("Migration created: {$filename}");
    }
    
    protected function extractFields(string $modelClass, MetadataRepository $metadata): array
    {
        $classMetadata = $metadata->getClassMetadata($modelClass);
        $fields = [];
        
        foreach ($classMetadata['properties'] as $property => $attributes) {
            if (isset($attributes[Field::class])) {
                $field = $attributes[Field::class];
                $fields[$property] = $field;
            }
        }
        
        return $fields;
    }
    
    protected function generateMigrationCode(string $table, array $fields): string
    {
        $columns = [];
        
        foreach ($fields as $name => $field) {
            $column = $this->generateColumnDefinition($field);
            $columns[] = "            \$table->{$column};";
        }
        
        $columnsStr = implode("\n", $columns);
        
        return <<<PHP
<?php

use NeoPhp\Database\Migrations\Migration;
use NeoPhp\Database\Schema\Blueprint;
use NeoPhp\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table) {
            \$table->id();
{$columnsStr}
            \$table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP;
    }
    
    protected function generateColumnDefinition(Field $field): string
    {
        $column = match($field->type) {
            'string' => "string('{$field->name}', {$field->length})",
            'text' => "text('{$field->name}')",
            'integer' => "integer('{$field->name}')",
            'bigint' => "bigInteger('{$field->name}')",
            'float' => "float('{$field->name}')",
            'decimal' => "decimal('{$field->name}', {$field->precision}, {$field->scale})",
            'boolean' => "boolean('{$field->name}')",
            'date' => "date('{$field->name}')",
            'datetime' => "datetime('{$field->name}')",
            'timestamp' => "timestamp('{$field->name}')",
            'json' => "json('{$field->name}')",
            'enum' => "enum('{$field->name}', ['" . implode("', '", $field->enum) . "'])",
            default => "string('{$field->name}')"
        };
        
        if ($field->nullable) {
            $column .= "->nullable()";
        }
        
        if ($field->unique) {
            $column .= "->unique()";
        }
        
        if ($field->default !== null) {
            $default = is_string($field->default) ? "'{$field->default}'" : $field->default;
            $column .= "->default({$default})";
        }
        
        if ($field->comment) {
            $column .= "->comment('{$field->comment}')";
        }
        
        return $column;
    }
}
```

### Auto-Generate Forms from Metadata

```php
<?php

namespace App\Services;

use NeoPhp\Metadata\MetadataRepository;
use NeoPhp\Metadata\Field;

class FormGenerator
{
    public function __construct(
        protected MetadataRepository $metadata
    ) {}
    
    public function generateForm(string $modelClass): string
    {
        $classMetadata = $this->metadata->getClassMetadata($modelClass);
        $html = '<form method="POST">';
        
        foreach ($classMetadata['properties'] as $property => $attributes) {
            if (!isset($attributes[Field::class])) {
                continue;
            }
            
            $field = $attributes[Field::class];
            
            if ($field->hidden) {
                continue;
            }
            
            $html .= $this->generateFieldHtml($field);
        }
        
        $html .= '<button type="submit">Submit</button>';
        $html .= '</form>';
        
        return $html;
    }
    
    protected function generateFieldHtml(Field $field): string
    {
        $label = $field->label ?? ucfirst($field->name);
        $inputType = $field->inputType ?? $this->guessInputType($field);
        
        $html = "<div class='form-group'>";
        $html .= "<label for='{$field->name}'>{$label}</label>";
        
        if ($inputType === 'textarea') {
            $html .= $this->generateTextarea($field);
        } elseif ($inputType === 'select') {
            $html .= $this->generateSelect($field);
        } else {
            $html .= $this->generateInput($field, $inputType);
        }
        
        $html .= "</div>";
        
        return $html;
    }
    
    protected function generateInput(Field $field, string $type): string
    {
        $required = !$field->nullable ? 'required' : '';
        $placeholder = $field->placeholder ?? '';
        
        $attrs = [
            "type='{$type}'",
            "id='{$field->name}'",
            "name='{$field->name}'",
            "placeholder='{$placeholder}'",
            $required
        ];
        
        if ($field->min !== null) {
            $attrs[] = "min='{$field->min}'";
        }
        
        if ($field->max !== null) {
            $attrs[] = "max='{$field->max}'";
        }
        
        if ($field->pattern) {
            $attrs[] = "pattern='{$field->pattern}'";
        }
        
        return "<input " . implode(' ', $attrs) . ">";
    }
    
    protected function generateTextarea(Field $field): string
    {
        $required = !$field->nullable ? 'required' : '';
        $placeholder = $field->placeholder ?? '';
        
        return "<textarea id='{$field->name}' name='{$field->name}' " .
               "placeholder='{$placeholder}' {$required}></textarea>";
    }
    
    protected function generateSelect(Field $field): string
    {
        $required = !$field->nullable ? 'required' : '';
        $html = "<select id='{$field->name}' name='{$field->name}' {$required}>";
        
        if ($field->nullable) {
            $html .= "<option value=''>-- Select --</option>";
        }
        
        foreach ($field->enum ?? [] as $option) {
            $selected = $option === $field->default ? 'selected' : '';
            $html .= "<option value='{$option}' {$selected}>{$option}</option>";
        }
        
        $html .= "</select>";
        
        return $html;
    }
    
    protected function guessInputType(Field $field): string
    {
        return match($field->type) {
            'email' => 'email',
            'integer' => 'number',
            'float', 'decimal' => 'number',
            'boolean' => 'checkbox',
            'date' => 'date',
            'datetime' => 'datetime-local',
            'text' => 'textarea',
            'enum' => 'select',
            default => 'text'
        };
    }
}

// Usage
$generator = new FormGenerator($metadataRepository);
$formHtml = $generator->generateForm(User::class);
echo $formHtml;
```

### Auto-Generate API Documentation

```php
<?php

namespace App\Services;

use NeoPhp\Metadata\MetadataRepository;
use App\Metadata\ApiResource;
use App\Metadata\Route;

class ApiDocGenerator
{
    public function __construct(
        protected MetadataRepository $metadata
    ) {}
    
    public function generateDocs(array $controllers): array
    {
        $docs = [];
        
        foreach ($controllers as $controller) {
            $controllerDocs = $this->generateControllerDocs($controller);
            if ($controllerDocs) {
                $docs[] = $controllerDocs;
            }
        }
        
        return $docs;
    }
    
    protected function generateControllerDocs(string $controller): ?array
    {
        $classMetadata = $this->metadata->getClassMetadata($controller);
        
        if (!isset($classMetadata['class'][ApiResource::class])) {
            return null;
        }
        
        $resource = $classMetadata['class'][ApiResource::class];
        
        $endpoints = [];
        foreach ($classMetadata['methods'] as $method => $attributes) {
            if (isset($attributes[Route::class])) {
                $routes = is_array($attributes[Route::class])
                    ? $attributes[Route::class]
                    : [$attributes[Route::class]];
                
                foreach ($routes as $route) {
                    $endpoints[] = [
                        'method' => $route->method,
                        'path' => $resource->endpoint . $route->path,
                        'handler' => $method,
                        'authenticated' => $resource->authenticated,
                        'middleware' => $route->middleware,
                    ];
                }
            }
        }
        
        return [
            'resource' => $resource->endpoint,
            'version' => $resource->version,
            'endpoints' => $endpoints,
        ];
    }
}
```

### Validation from Metadata

```php
<?php

namespace App\Services;

use NeoPhp\Metadata\MetadataRepository;
use NeoPhp\Metadata\Field;
use NeoPhp\Validation\Validator;

class MetadataValidator
{
    public function __construct(
        protected MetadataRepository $metadata,
        protected Validator $validator
    ) {}
    
    public function validate(object $model, array $data): array
    {
        $rules = $this->extractValidationRules($model);
        
        return $this->validator->validate($data, $rules);
    }
    
    protected function extractValidationRules(object $model): array
    {
        $classMetadata = $this->metadata->getClassMetadata(get_class($model));
        $rules = [];
        
        foreach ($classMetadata['properties'] as $property => $attributes) {
            if (!isset($attributes[Field::class])) {
                continue;
            }
            
            $field = $attributes[Field::class];
            $rules[$property] = $field->getValidationRules();
        }
        
        return $rules;
    }
}

// Usage
$validator = new MetadataValidator($metadata, $validator);
$user = new User();

$errors = $validator->validate($user, $request->all());

if (!empty($errors)) {
    return response()->json(['errors' => $errors], 422);
}
```

## Advanced Patterns 🎯

### Attribute Inheritance

```php
#[Attribute(Attribute::TARGET_CLASS)]
class CachedResource
{
    public function __construct(
        public int $ttl = 3600,
        public ?string $tag = null
    ) {}
}

// Base attribute
#[CachedResource(ttl: 7200, tag: 'api')]
class BaseApiController {}

// Inherits caching behavior
class UserController extends BaseApiController
{
    // Automatically cached with parent's settings
}
```

### Composite Attributes

```php
#[Attribute(Attribute::TARGET_CLASS)]
class RestResource
{
    public function __construct(
        public string $endpoint,
        public bool $cached = false,
        public bool $versioned = true,
        public bool $paginated = true,
        public array $middleware = ['auth']
    ) {}
}

#[RestResource(
    endpoint: '/api/users',
    cached: true,
    versioned: true,
    paginated: true
)]
class UserController
{
    // All REST conventions applied automatically
}
```

### Conditional Attributes

```php
#[Attribute(Attribute::TARGET_METHOD)]
class FeatureFlag
{
    public function __construct(
        public string $flag,
        public bool $required = true
    ) {}
}

class FeatureController
{
    #[FeatureFlag('beta_features', required: true)]
    public function betaFeature()
    {
        // Only accessible if beta_features flag is enabled
    }
}

// Middleware to check feature flags
class FeatureFlagMiddleware
{
    public function handle($request, $next)
    {
        $route = $request->route();
        $controller = $route->getController();
        $method = $route->getActionMethod();
        
        $metadata = app(MetadataRepository::class);
        $classMetadata = $metadata->getClassMetadata(get_class($controller));
        
        if (isset($classMetadata['methods'][$method][FeatureFlag::class])) {
            $flag = $classMetadata['methods'][$method][FeatureFlag::class];
            
            if ($flag->required && !Features::enabled($flag->flag)) {
                abort(404);
            }
        }
        
        return $next($request);
    }
}
```

## Best Practices 📋

### 1. Keep Attributes Focused

```php
// Good: Single responsibility
#[Table('users')]
#[SoftDeletes]
#[Cached(ttl: 3600)]
class User {}

// Bad: Too many concerns in one attribute
#[Model(
    table: 'users',
    cached: true,
    softDeletes: true,
    versioned: true,
    searchable: true,
    // Too much!
)]
class User {}
```

### 2. Use Named Arguments

```php
// Good: Clear and self-documenting
#[Field(
    name: 'email',
    type: 'string',
    unique: true,
    nullable: false
)]

// Bad: Positional arguments hard to read
#[Field('email', 'string', null, false, null, true)]
```

### 3. Provide Sensible Defaults

```php
#[Attribute(Attribute::TARGET_CLASS)]
class ApiResource
{
    public function __construct(
        public string $endpoint,
        public string $version = 'v1',  // Default
        public array $methods = ['GET', 'POST', 'PUT', 'DELETE'],  // Default
        public bool $authenticated = true  // Default
    ) {}
}
```

### 4. Document Your Attributes

```php
/**
 * Marks a class as an API resource
 * 
 * @property string $endpoint API endpoint path (required)
 * @property string $version API version (default: v1)
 * @property array $methods Allowed HTTP methods
 * @property bool $authenticated Requires authentication
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ApiResource
{
    // ...
}
```

### 5. Cache Metadata

```php
// Cache metadata to avoid repeated reflection
$metadata = Cache::remember("metadata:{$class}", 3600, function () use ($class) {
    return $repository->getClassMetadata($class);
});
```

## Testing Attributes 🧪

```php
<?php

namespace Tests\Unit\Metadata;

use Tests\TestCase;
use App\Models\User;
use NeoPhp\Metadata\Table;
use NeoPhp\Metadata\Field;

class AttributesTest extends TestCase
{
    public function test_user_has_table_attribute(): void
    {
        $reflection = new \ReflectionClass(User::class);
        $attributes = $reflection->getAttributes(Table::class);
        
        $this->assertNotEmpty($attributes);
        
        $table = $attributes[0]->newInstance();
        $this->assertEquals('users', $table->name);
    }
    
    public function test_email_field_has_correct_metadata(): void
    {
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('email');
        $attributes = $property->getAttributes(Field::class);
        
        $this->assertNotEmpty($attributes);
        
        $field = $attributes[0]->newInstance();
        $this->assertEquals('string', $field->type);
        $this->assertTrue($field->unique);
        $this->assertFalse($field->nullable);
    }
    
    public function test_field_generates_validation_rules(): void
    {
        $field = new Field(
            name: 'email',
            type: 'email',
            unique: true,
            nullable: false,
            max: 255
        );
        
        $rules = $field->getValidationRules();
        
        $this->assertContains('required', $rules);
        $this->assertContains('email', $rules);
        $this->assertContains('unique', $rules);
        $this->assertContains('max:255', $rules);
    }
}
```

## Related Documentation

- [Database Models](../database/eloquent.md) - Using attributes with models
- [Validation](../basics/validation.md) - Validation from metadata
- [API Resources](../api/resources.md) - API documentation with attributes
- [Migrations](../database/migrations.md) - Generate migrations from attributes

## Next Steps

Explore these topics to master metadata-driven development:

1. **[Database Eloquent](../database/eloquent.md)** - Using attributes with models
2. **[Validation](../basics/validation.md)** - Metadata-based validation
3. **[Service Providers](service-providers.md)** - Register metadata services
4. **[Plugins](plugins.md)** - Extend with attribute-based plugins
