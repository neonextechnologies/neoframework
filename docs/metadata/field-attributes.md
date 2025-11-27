# Field-Level Attributes

## Introduction

Field-level attributes in NeoFramework define properties and behavior for individual model fields. These attributes control database column configuration, validation rules, UI generation, serialization, and more.

## Core Field Attributes

### Column Attribute

The `#[Column]` attribute defines database column properties:

```php
<?php

namespace App\Models;

use Neo\Metadata\Attributes\Column;

class User extends Model
{
    #[Column(
        type: 'integer',
        autoIncrement: true,
        unsigned: true
    )]
    public int $id;
    
    #[Column(
        type: 'string',
        length: 255,
        nullable: false,
        default: null
    )]
    public string $email;
    
    #[Column(
        type: 'decimal',
        precision: 10,
        scale: 2,
        unsigned: true
    )]
    public float $balance;
    
    #[Column(
        type: 'text',
        nullable: true
    )]
    public ?string $bio;
    
    #[Column(
        type: 'boolean',
        default: false
    )]
    public bool $is_active;
    
    #[Column(
        type: 'datetime',
        nullable: true
    )]
    public ?DateTime $last_login;
}
```

#### Column Types

Supported column types:

```php
<?php

class Product extends Model
{
    // Integer types
    #[Column(type: 'integer')]
    public int $quantity;
    
    #[Column(type: 'bigInteger')]
    public int $views;
    
    #[Column(type: 'smallInteger')]
    public int $priority;
    
    #[Column(type: 'tinyInteger')]
    public int $status;
    
    // String types
    #[Column(type: 'string', length: 255)]
    public string $name;
    
    #[Column(type: 'char', length: 10)]
    public string $code;
    
    #[Column(type: 'text')]
    public string $description;
    
    #[Column(type: 'mediumText')]
    public string $content;
    
    #[Column(type: 'longText')]
    public string $article;
    
    // Decimal types
    #[Column(type: 'decimal', precision: 8, scale: 2)]
    public float $price;
    
    #[Column(type: 'float')]
    public float $rating;
    
    #[Column(type: 'double')]
    public float $latitude;
    
    // Date/Time types
    #[Column(type: 'date')]
    public DateTime $birth_date;
    
    #[Column(type: 'datetime')]
    public DateTime $created_at;
    
    #[Column(type: 'timestamp')]
    public DateTime $updated_at;
    
    #[Column(type: 'time')]
    public DateTime $opening_time;
    
    // Binary types
    #[Column(type: 'binary')]
    public string $data;
    
    #[Column(type: 'blob')]
    public string $file_content;
    
    // JSON type
    #[Column(type: 'json')]
    public array $metadata;
    
    // UUID type
    #[Column(type: 'uuid')]
    public string $uuid;
    
    // Enum type
    #[Column(type: 'enum', values: ['pending', 'active', 'suspended'])]
    public string $status;
}
```

### PrimaryKey Attribute

Define primary key columns:

```php
<?php

use Neo\Metadata\Attributes\PrimaryKey;
use Neo\Metadata\Attributes\Column;

class User extends Model
{
    #[Column(type: 'integer', autoIncrement: true)]
    #[PrimaryKey]
    public int $id;
}

// Composite primary key
class OrderItem extends Model
{
    #[Column(type: 'integer')]
    #[PrimaryKey]
    public int $order_id;
    
    #[Column(type: 'integer')]
    #[PrimaryKey]
    public int $product_id;
    
    #[Column(type: 'integer')]
    public int $quantity;
}

// UUID primary key
class Session extends Model
{
    #[Column(type: 'uuid')]
    #[PrimaryKey]
    public string $id;
    
    #[Column(type: 'json')]
    public array $data;
}
```

### Unique Attribute

Define unique constraints:

```php
<?php

use Neo\Metadata\Attributes\Unique;

class User extends Model
{
    #[Column(type: 'string', length: 255)]
    #[Unique]
    public string $email;
    
    #[Column(type: 'string', length: 50)]
    #[Unique]
    public string $username;
}

// Composite unique constraint
class Product extends Model
{
    #[Column(type: 'string', length: 100)]
    #[Unique(group: 'sku_vendor')]
    public string $sku;
    
    #[Column(type: 'integer')]
    #[Unique(group: 'sku_vendor')]
    public int $vendor_id;
}
```

### Index Attribute

Define database indexes:

```php
<?php

use Neo\Metadata\Attributes\Index;

class Post extends Model
{
    #[Column(type: 'string', length: 255)]
    #[Index]
    public string $title;
    
    #[Column(type: 'string', length: 100)]
    #[Index(name: 'idx_status')]
    public string $status;
    
    #[Column(type: 'integer')]
    #[Index(name: 'idx_user_status')]
    public int $user_id;
    
    #[Column(type: 'datetime')]
    #[Index(name: 'idx_published')]
    public DateTime $published_at;
}

// Composite index
class Comment extends Model
{
    #[Column(type: 'integer')]
    #[Index(group: 'post_user')]
    public int $post_id;
    
    #[Column(type: 'integer')]
    #[Index(group: 'post_user')]
    public int $user_id;
}

// Full-text index
class Article extends Model
{
    #[Column(type: 'string', length: 255)]
    #[Index(type: 'fulltext')]
    public string $title;
    
    #[Column(type: 'text')]
    #[Index(type: 'fulltext')]
    public string $content;
}
```

### ForeignKey Attribute

Define foreign key constraints:

```php
<?php

use Neo\Metadata\Attributes\ForeignKey;

class Post extends Model
{
    #[Column(type: 'integer')]
    #[ForeignKey(
        references: 'id',
        on: User::class,
        onDelete: 'cascade',
        onUpdate: 'cascade'
    )]
    public int $user_id;
    
    #[Column(type: 'integer', nullable: true)]
    #[ForeignKey(
        references: 'id',
        on: Category::class,
        onDelete: 'set null'
    )]
    public ?int $category_id;
}
```

### Default Attribute

Set default values:

```php
<?php

use Neo\Metadata\Attributes\Default;

class User extends Model
{
    #[Column(type: 'boolean')]
    #[Default(false)]
    public bool $is_admin;
    
    #[Column(type: 'string', length: 50)]
    #[Default('active')]
    public string $status;
    
    #[Column(type: 'integer')]
    #[Default(0)]
    public int $login_count;
    
    #[Column(type: 'datetime')]
    #[Default('CURRENT_TIMESTAMP')]
    public DateTime $created_at;
}
```

### Nullable Attribute

Mark fields as nullable:

```php
<?php

use Neo\Metadata\Attributes\Nullable;

class Product extends Model
{
    #[Column(type: 'string', length: 255)]
    public string $name; // Required
    
    #[Column(type: 'text')]
    #[Nullable]
    public ?string $description; // Optional
    
    #[Column(type: 'decimal', precision: 10, scale: 2)]
    #[Nullable]
    public ?float $sale_price; // Optional
}
```

## Serialization Attributes

### Hidden Attribute

Hide fields from serialization:

```php
<?php

use Neo\Metadata\Attributes\Hidden;

class User extends Model
{
    #[Column(type: 'string', length: 255)]
    public string $email;
    
    #[Column(type: 'string', length: 255)]
    #[Hidden]
    public string $password; // Never serialized
    
    #[Column(type: 'string', length: 100, nullable: true)]
    #[Hidden]
    public ?string $remember_token; // Never serialized
    
    #[Column(type: 'string', length: 255, nullable: true)]
    #[Hidden]
    public ?string $api_secret;
}
```

### Visible Attribute

Only include specific fields in serialization:

```php
<?php

use Neo\Metadata\Attributes\Visible;

class User extends Model
{
    #[Column(type: 'integer')]
    #[Visible]
    public int $id;
    
    #[Column(type: 'string', length: 255)]
    #[Visible]
    public string $name;
    
    #[Column(type: 'string', length: 255)]
    #[Visible]
    public string $email;
    
    #[Column(type: 'string', length: 255)]
    public string $password; // Not visible
    
    #[Column(type: 'string', length: 100, nullable: true)]
    public ?string $remember_token; // Not visible
}
```

### Appends Attribute

Append computed attributes to serialization:

```php
<?php

use Neo\Metadata\Attributes\Appends;

class User extends Model
{
    #[Column(type: 'string', length: 100)]
    public string $first_name;
    
    #[Column(type: 'string', length: 100)]
    public string $last_name;
    
    #[Appends]
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    
    #[Column(type: 'string', length: 255)]
    public string $avatar_path;
    
    #[Appends]
    public function getAvatarUrlAttribute(): string
    {
        return asset('storage/' . $this->avatar_path);
    }
}

// When serialized, includes full_name and avatar_url
$user->toArray();
// ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe', 
//  'full_name' => 'John Doe', 'avatar_url' => 'https://...']
```

### Casts Attribute

Define type casting:

```php
<?php

use Neo\Metadata\Attributes\Casts;

class User extends Model
{
    #[Column(type: 'json')]
    #[Casts('array')]
    public array $preferences;
    
    #[Column(type: 'json')]
    #[Casts('object')]
    public object $settings;
    
    #[Column(type: 'string', length: 10)]
    #[Casts('date')]
    public Carbon $birth_date;
    
    #[Column(type: 'datetime')]
    #[Casts('datetime')]
    public Carbon $created_at;
    
    #[Column(type: 'boolean')]
    #[Casts('boolean')]
    public bool $is_active;
    
    #[Column(type: 'integer')]
    #[Casts('integer')]
    public int $age;
    
    #[Column(type: 'decimal', precision: 10, scale: 2)]
    #[Casts('float')]
    public float $balance;
    
    #[Column(type: 'text')]
    #[Casts('encrypted')]
    public string $ssn;
}

// Custom cast classes
class Address extends Model
{
    #[Column(type: 'json')]
    #[Casts(AddressCast::class)]
    public AddressValue $address;
}
```

## Display Attributes

### Label Attribute

Define human-readable labels:

```php
<?php

use Neo\Metadata\Attributes\Label;

class Product extends Model
{
    #[Column(type: 'string', length: 255)]
    #[Label('Product Name')]
    public string $name;
    
    #[Column(type: 'decimal', precision: 10, scale: 2)]
    #[Label('Unit Price')]
    public float $price;
    
    #[Column(type: 'integer')]
    #[Label('Available Stock')]
    public int $stock_quantity;
}
```

### Description Attribute

Add field descriptions:

```php
<?php

use Neo\Metadata\Attributes\Description;

class User extends Model
{
    #[Column(type: 'string', length: 255)]
    #[Description('Primary email address for account notifications')]
    public string $email;
    
    #[Column(type: 'string', length: 20, nullable: true)]
    #[Description('Mobile phone number for two-factor authentication')]
    public ?string $phone;
}
```

### Placeholder Attribute

Define input placeholders:

```php
<?php

use Neo\Metadata\Attributes\Placeholder;

class Contact extends Model
{
    #[Column(type: 'string', length: 255)]
    #[Placeholder('Enter your full name')]
    public string $name;
    
    #[Column(type: 'string', length: 255)]
    #[Placeholder('you@example.com')]
    public string $email;
    
    #[Column(type: 'text')]
    #[Placeholder('What would you like to tell us?')]
    public string $message;
}
```

### HelpText Attribute

Add contextual help text:

```php
<?php

use Neo\Metadata\Attributes\HelpText;

class User extends Model
{
    #[Column(type: 'string', length: 255)]
    #[HelpText('Must be at least 8 characters with uppercase, lowercase, and numbers')]
    public string $password;
    
    #[Column(type: 'string', length: 50)]
    #[HelpText('Username must be unique and 3-50 characters')]
    public string $username;
}
```

## Search and Filter Attributes

### Searchable Attribute

Mark fields as searchable:

```php
<?php

use Neo\Metadata\Attributes\Searchable;

class Post extends Model
{
    #[Column(type: 'string', length: 255)]
    #[Searchable(weight: 10)]
    public string $title;
    
    #[Column(type: 'text')]
    #[Searchable(weight: 5)]
    public string $content;
    
    #[Column(type: 'string', length: 255)]
    #[Searchable]
    public string $excerpt;
}

// Usage
$results = Post::search('laravel framework');
```

### Filterable Attribute

Enable filtering on fields:

```php
<?php

use Neo\Metadata\Attributes\Filterable;

class Product extends Model
{
    #[Column(type: 'string', length: 100)]
    #[Filterable(type: 'select')]
    public string $category;
    
    #[Column(type: 'decimal', precision: 10, scale: 2)]
    #[Filterable(type: 'range', min: 0, max: 10000)]
    public float $price;
    
    #[Column(type: 'boolean')]
    #[Filterable(type: 'checkbox')]
    public bool $in_stock;
    
    #[Column(type: 'string', length: 100)]
    #[Filterable(type: 'text')]
    public string $brand;
}

// Usage
$products = Product::filter([
    'category' => 'Electronics',
    'price' => ['min' => 100, 'max' => 500],
    'in_stock' => true,
]);
```

### Sortable Attribute

Enable sorting on fields:

```php
<?php

use Neo\Metadata\Attributes\Sortable;

class Product extends Model
{
    #[Column(type: 'string', length: 255)]
    #[Sortable]
    public string $name;
    
    #[Column(type: 'decimal', precision: 10, scale: 2)]
    #[Sortable(default: 'asc')]
    public float $price;
    
    #[Column(type: 'datetime')]
    #[Sortable(default: 'desc')]
    public DateTime $created_at;
}

// Usage
$products = Product::orderByField('price', 'asc');
```

## Computed Attributes

### Computed Attribute

Define computed fields:

```php
<?php

use Neo\Metadata\Attributes\Computed;

class Order extends Model
{
    #[Column(type: 'decimal', precision: 10, scale: 2)]
    public float $subtotal;
    
    #[Column(type: 'decimal', precision: 10, scale: 2)]
    public float $tax;
    
    #[Column(type: 'decimal', precision: 10, scale: 2)]
    public float $shipping;
    
    #[Computed]
    public function getTotalAttribute(): float
    {
        return $this->subtotal + $this->tax + $this->shipping;
    }
    
    #[Computed(cache: true, ttl: 3600)]
    public function getItemCountAttribute(): int
    {
        return $this->items()->count();
    }
}

// Access computed attributes
echo $order->total;
echo $order->item_count;
```

## Advanced Attributes

### Encrypted Attribute

Automatically encrypt/decrypt fields:

```php
<?php

use Neo\Metadata\Attributes\Encrypted;

class User extends Model
{
    #[Column(type: 'text')]
    #[Encrypted]
    public string $ssn;
    
    #[Column(type: 'text')]
    #[Encrypted(algorithm: 'AES-256-CBC')]
    public string $credit_card;
}
```

### Hashed Attribute

Automatically hash fields:

```php
<?php

use Neo\Metadata\Attributes\Hashed;

class User extends Model
{
    #[Column(type: 'string', length: 255)]
    #[Hashed(algorithm: 'bcrypt')]
    public string $password;
    
    #[Column(type: 'string', length: 255)]
    #[Hashed(algorithm: 'sha256')]
    public string $api_token;
}
```

### Sluggable Attribute

Automatically generate slugs:

```php
<?php

use Neo\Metadata\Attributes\Sluggable;

class Post extends Model
{
    #[Column(type: 'string', length: 255)]
    public string $title;
    
    #[Column(type: 'string', length: 255)]
    #[Sluggable(source: 'title', unique: true)]
    public string $slug;
}

// Automatically generates slug from title
$post = new Post();
$post->title = 'My Awesome Blog Post';
$post->save();
echo $post->slug; // 'my-awesome-blog-post'
```

### AutoGenerate Attribute

Auto-generate field values:

```php
<?php

use Neo\Metadata\Attributes\AutoGenerate;

class Order extends Model
{
    #[Column(type: 'string', length: 50)]
    #[AutoGenerate(pattern: 'ORD-{date:Ymd}-{random:6}')]
    public string $order_number;
    
    #[Column(type: 'uuid')]
    #[AutoGenerate(type: 'uuid')]
    public string $uuid;
    
    #[Column(type: 'string', length: 100)]
    #[AutoGenerate(callable: [OrderGenerator::class, 'generateCode'])]
    public string $tracking_code;
}
```

## Best Practices

1. **Be Explicit**: Define all column properties explicitly for clarity

2. **Use Type Declarations**: Always use PHP type declarations with attributes

3. **Combine Attributes**: Use multiple attributes together for complete field definition

4. **Consider Performance**: Use indexes wisely on frequently queried fields

5. **Document Complex Fields**: Add descriptions for fields with special behavior

6. **Validate Consistently**: Apply validation attributes to all user-input fields

7. **Hide Sensitive Data**: Always use `#[Hidden]` for passwords and secrets

8. **Use Appropriate Types**: Choose the right column type for your data

## Examples

### Complete User Model

```php
<?php

namespace App\Models;

use Neo\Database\Model;
use Neo\Metadata\Attributes\*;

#[Table(name: 'users')]
#[SoftDeletes]
#[Timestamps]
class User extends Model
{
    #[Column(type: 'integer', autoIncrement: true)]
    #[PrimaryKey]
    public int $id;
    
    #[Column(type: 'string', length: 255)]
    #[Unique]
    #[Validation(rules: 'required|email|unique:users,email')]
    #[Label('Email Address')]
    #[Placeholder('you@example.com')]
    #[Searchable]
    #[Filterable(type: 'text')]
    public string $email;
    
    #[Column(type: 'string', length: 255)]
    #[Hidden]
    #[Hashed(algorithm: 'bcrypt')]
    #[Validation(rules: 'required|min:8')]
    #[Label('Password')]
    #[HelpText('Must be at least 8 characters')]
    public string $password;
    
    #[Column(type: 'string', length: 100)]
    #[Validation(rules: 'required|max:100')]
    #[Label('Full Name')]
    #[Searchable]
    #[Sortable]
    public string $name;
    
    #[Column(type: 'boolean')]
    #[Default(false)]
    #[Label('Administrator')]
    #[Filterable(type: 'checkbox')]
    public bool $is_admin;
    
    #[Column(type: 'datetime', nullable: true)]
    #[Label('Last Login')]
    #[Sortable]
    public ?DateTime $last_login_at;
}
```

## Next Steps

- Explore [Table Attributes](table-attributes.md)
- Learn about [Relationship Attributes](relationships.md)
- Discover [Form Generation](form-generation.md)
- Master [Validation](validation.md)
