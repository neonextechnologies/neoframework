# Table-Level Attributes

## Introduction

Table-level attributes in NeoFramework define database table structure, behavior, and constraints at the model class level. These attributes control table names, indexes, timestamps, soft deletes, and other table-wide configurations.

## Core Table Attributes

### Table Attribute

The `#[Table]` attribute defines the database table configuration:

```php
<?php

namespace App\Models;

use Neo\Database\Model;
use Neo\Metadata\Attributes\Table;

#[Table(name: 'users')]
class User extends Model
{
    // Model properties...
}

// Advanced table configuration
#[Table(
    name: 'products',
    engine: 'InnoDB',
    charset: 'utf8mb4',
    collation: 'utf8mb4_unicode_ci',
    comment: 'Product catalog table'
)]
class Product extends Model
{
    // Model properties...
}

// Temporary table
#[Table(name: 'session_data', temporary: true)]
class SessionData extends Model
{
    // Model properties...
}
```

#### Table Options

```php
<?php

#[Table(
    name: 'orders',
    engine: 'InnoDB',           // Database engine
    charset: 'utf8mb4',          // Character set
    collation: 'utf8mb4_unicode_ci', // Collation
    comment: 'Order records',    // Table comment
    rowFormat: 'DYNAMIC',        // Row format
    temporary: false,            // Temporary table
    ifNotExists: true            // Create only if not exists
)]
class Order extends Model
{
    // ...
}
```

### Timestamps Attribute

Enable automatic timestamp management:

```php
<?php

use Neo\Metadata\Attributes\Timestamps;

#[Table(name: 'posts')]
#[Timestamps]  // Adds created_at and updated_at
class Post extends Model
{
    // Automatically managed:
    // public DateTime $created_at;
    // public DateTime $updated_at;
}

// Custom timestamp columns
#[Table(name: 'articles')]
#[Timestamps(
    createdAt: 'created_on',
    updatedAt: 'modified_on'
)]
class Article extends Model
{
    public DateTime $created_on;
    public DateTime $modified_on;
}

// Disable one timestamp
#[Table(name: 'logs')]
#[Timestamps(updatedAt: null)]  // Only created_at
class Log extends Model
{
    public DateTime $created_at;
}
```

### SoftDeletes Attribute

Enable soft delete functionality:

```php
<?php

use Neo\Metadata\Attributes\SoftDeletes;

#[Table(name: 'users')]
#[SoftDeletes]  // Adds deleted_at column
class User extends Model
{
    // Automatically managed:
    // public ?DateTime $deleted_at;
}

// Custom soft delete column
#[Table(name: 'posts')]
#[SoftDeletes(column: 'removed_at')]
class Post extends Model
{
    public ?DateTime $removed_at;
}

// Usage
$user = User::find(1);
$user->delete();  // Soft delete (sets deleted_at)

// Query excluding soft deleted
$users = User::all();  // Excludes soft deleted

// Include soft deleted
$users = User::withTrashed()->get();

// Only soft deleted
$users = User::onlyTrashed()->get();

// Permanently delete
$user->forceDelete();

// Restore soft deleted
$user->restore();
```

## Index Attributes

### Index Attribute

Define table-level indexes:

```php
<?php

use Neo\Metadata\Attributes\Index;

#[Table(name: 'posts')]
#[Index(columns: ['user_id'])]
#[Index(columns: ['status', 'published_at'], name: 'idx_status_published')]
class Post extends Model
{
    public int $user_id;
    public string $status;
    public DateTime $published_at;
}

// Multiple indexes
#[Table(name: 'products')]
#[Index(columns: ['category_id'])]
#[Index(columns: ['sku'], unique: true)]
#[Index(columns: ['price', 'status'])]
#[Index(columns: ['name'], type: 'fulltext')]
class Product extends Model
{
    // ...
}
```

### UniqueIndex Attribute

Define unique constraints:

```php
<?php

use Neo\Metadata\Attributes\UniqueIndex;

#[Table(name: 'users')]
#[UniqueIndex(columns: ['email'])]
#[UniqueIndex(columns: ['username'])]
class User extends Model
{
    public string $email;
    public string $username;
}

// Composite unique constraint
#[Table(name: 'user_roles')]
#[UniqueIndex(columns: ['user_id', 'role_id'], name: 'unique_user_role')]
class UserRole extends Model
{
    public int $user_id;
    public int $role_id;
}
```

### FullTextIndex Attribute

Define full-text search indexes:

```php
<?php

use Neo\Metadata\Attributes\FullTextIndex;

#[Table(name: 'articles')]
#[FullTextIndex(columns: ['title', 'content'])]
class Article extends Model
{
    public string $title;
    public string $content;
}

// Multiple full-text indexes
#[Table(name: 'products')]
#[FullTextIndex(columns: ['name'], name: 'ft_name')]
#[FullTextIndex(columns: ['description'], name: 'ft_description')]
#[FullTextIndex(columns: ['name', 'description'], name: 'ft_name_description')]
class Product extends Model
{
    public string $name;
    public string $description;
}

// Usage
$articles = Article::whereFullText(['title', 'content'], 'search term')->get();
```

### SpatialIndex Attribute

Define spatial indexes for geographic data:

```php
<?php

use Neo\Metadata\Attributes\SpatialIndex;

#[Table(name: 'locations')]
#[SpatialIndex(columns: ['coordinates'])]
class Location extends Model
{
    #[Column(type: 'point')]
    public $coordinates;
}

// Usage with spatial queries
$nearby = Location::withinDistance('coordinates', $latitude, $longitude, $radiusKm)->get();
```

## Composite Key Attributes

### CompositePrimaryKey Attribute

Define composite primary keys:

```php
<?php

use Neo\Metadata\Attributes\CompositePrimaryKey;

#[Table(name: 'order_items')]
#[CompositePrimaryKey(columns: ['order_id', 'product_id'])]
class OrderItem extends Model
{
    public int $order_id;
    public int $product_id;
    public int $quantity;
    public float $price;
}

// No auto-incrementing ID needed
$item = new OrderItem();
$item->order_id = 1;
$item->product_id = 5;
$item->quantity = 2;
$item->save();
```

## Table Partitioning

### Partition Attribute

Define table partitioning:

```php
<?php

use Neo\Metadata\Attributes\Partition;

#[Table(name: 'logs')]
#[Partition(
    type: 'RANGE',
    expression: 'YEAR(created_at)',
    partitions: [
        ['name' => 'p2022', 'value' => 2022],
        ['name' => 'p2023', 'value' => 2023],
        ['name' => 'p2024', 'value' => 2024],
        ['name' => 'pmax', 'value' => 'MAXVALUE']
    ]
)]
class Log extends Model
{
    public DateTime $created_at;
    public string $message;
}

// Hash partitioning
#[Table(name: 'sessions')]
#[Partition(
    type: 'HASH',
    expression: 'id',
    count: 10
)]
class Session extends Model
{
    // ...
}

// List partitioning
#[Table(name: 'regional_data')]
#[Partition(
    type: 'LIST',
    expression: 'region',
    partitions: [
        ['name' => 'p_north', 'values' => ['NY', 'MA', 'CT']],
        ['name' => 'p_south', 'values' => ['FL', 'GA', 'TX']],
        ['name' => 'p_west', 'values' => ['CA', 'OR', 'WA']]
    ]
)]
class RegionalData extends Model
{
    // ...
}
```

## Table Relationships

### HasMany Attribute (Table Level)

Define one-to-many relationships at table level:

```php
<?php

use Neo\Metadata\Attributes\HasManyRelation;

#[Table(name: 'users')]
#[HasManyRelation(model: Post::class, foreignKey: 'user_id')]
#[HasManyRelation(model: Comment::class, foreignKey: 'user_id')]
class User extends Model
{
    // Relationships automatically available
}
```

### BelongsToMany Attribute (Table Level)

Define many-to-many relationships:

```php
<?php

use Neo\Metadata\Attributes\BelongsToManyRelation;

#[Table(name: 'users')]
#[BelongsToManyRelation(
    model: Role::class,
    pivotTable: 'role_user',
    foreignKey: 'user_id',
    relatedKey: 'role_id'
)]
class User extends Model
{
    // ...
}
```

## Caching Attributes

### Cacheable Attribute

Enable automatic query caching:

```php
<?php

use Neo\Metadata\Attributes\Cacheable;

#[Table(name: 'categories')]
#[Cacheable(ttl: 3600)]  // Cache for 1 hour
class Category extends Model
{
    // Queries automatically cached
}

// With tags
#[Table(name: 'products')]
#[Cacheable(
    ttl: 1800,
    tags: ['products', 'catalog']
)]
class Product extends Model
{
    // ...
}

// Per-query control
$products = Product::noCache()->where('status', 'active')->get();
```

## Observer Attributes

### Observable Attribute

Register model observers:

```php
<?php

use Neo\Metadata\Attributes\Observable;

#[Table(name: 'orders')]
#[Observable(observer: OrderObserver::class)]
class Order extends Model
{
    // OrderObserver automatically registered
}

// Multiple observers
#[Table(name: 'users')]
#[Observable(observer: UserObserver::class)]
#[Observable(observer: ActivityLogger::class)]
class User extends Model
{
    // ...
}
```

## Scope Attributes

### GlobalScope Attribute

Define global query scopes:

```php
<?php

use Neo\Metadata\Attributes\GlobalScope;

#[Table(name: 'posts')]
#[GlobalScope(scope: PublishedScope::class)]
class Post extends Model
{
    // Only published posts returned by default
}

// Multiple global scopes
#[Table(name: 'products')]
#[GlobalScope(scope: ActiveScope::class)]
#[GlobalScope(scope: AvailableScope::class)]
class Product extends Model
{
    // ...
}

// Disable global scope for specific query
$allPosts = Post::withoutGlobalScope(PublishedScope::class)->get();
```

## Versioning Attributes

### Versionable Attribute

Enable model versioning:

```php
<?php

use Neo\Metadata\Attributes\Versionable;

#[Table(name: 'documents')]
#[Versionable(
    versionTable: 'document_versions',
    versionColumn: 'version',
    keepVersions: 10
)]
class Document extends Model
{
    public string $title;
    public string $content;
    
    // Automatically creates versions on update
}

// Usage
$document = Document::find(1);
$document->title = 'Updated Title';
$document->save();  // Creates new version

// Access versions
$versions = $document->versions;
$previousVersion = $document->previousVersion();

// Revert to version
$document->revertToVersion(5);
```

## Audit Attributes

### Auditable Attribute

Enable audit logging:

```php
<?php

use Neo\Metadata\Attributes\Auditable;

#[Table(name: 'users')]
#[Auditable(
    events: ['created', 'updated', 'deleted'],
    auditTable: 'audits'
)]
class User extends Model
{
    // All changes automatically logged
}

// Exclude fields from audit
#[Table(name: 'profiles')]
#[Auditable(exclude: ['password', 'remember_token'])]
class Profile extends Model
{
    // ...
}

// Access audit log
$audits = $user->audits;
```

## Replication Attributes

### Replication Attribute

Configure database replication:

```php
<?php

use Neo\Metadata\Attributes\Replication;

#[Table(name: 'analytics')]
#[Replication(
    read: 'replica',
    write: 'primary'
)]
class Analytics extends Model
{
    // Reads from replica, writes to primary
}
```

## Advanced Table Configurations

### Composite Example

Complete model with multiple table attributes:

```php
<?php

namespace App\Models;

use Neo\Database\Model;
use Neo\Metadata\Attributes\*;

#[Table(
    name: 'products',
    engine: 'InnoDB',
    charset: 'utf8mb4',
    collation: 'utf8mb4_unicode_ci',
    comment: 'Product catalog'
)]
#[Timestamps]
#[SoftDeletes]
#[Index(columns: ['category_id', 'status'])]
#[Index(columns: ['sku'], unique: true)]
#[FullTextIndex(columns: ['name', 'description'])]
#[Cacheable(ttl: 1800, tags: ['products'])]
#[Observable(observer: ProductObserver::class)]
#[GlobalScope(scope: ActiveScope::class)]
#[Auditable(events: ['created', 'updated', 'deleted'])]
class Product extends Model
{
    #[Column(type: 'integer', autoIncrement: true)]
    #[PrimaryKey]
    public int $id;
    
    #[Column(type: 'string', length: 100)]
    #[Unique]
    public string $sku;
    
    #[Column(type: 'string', length: 255)]
    #[Searchable]
    public string $name;
    
    #[Column(type: 'text')]
    #[Searchable]
    public string $description;
    
    #[Column(type: 'decimal', precision: 10, scale: 2)]
    public float $price;
    
    #[Column(type: 'integer')]
    #[ForeignKey(references: 'id', on: Category::class)]
    public int $category_id;
    
    #[Column(type: 'string', length: 20)]
    #[Default('active')]
    public string $status;
    
    #[Column(type: 'datetime')]
    public DateTime $created_at;
    
    #[Column(type: 'datetime')]
    public DateTime $updated_at;
    
    #[Column(type: 'datetime', nullable: true)]
    public ?DateTime $deleted_at;
}
```

## Migration Generation

Table attributes automatically generate migrations:

```php
<?php

use Neo\Generator\MigrationGenerator;

$generator = new MigrationGenerator();
$migration = $generator->generateFromModel(Product::class);

// Generated migration includes all table attributes:
// - Table creation with engine, charset, etc.
// - All indexes and unique constraints
// - Full-text indexes
// - Foreign keys
// - Timestamps and soft deletes
```

## Configuration

Configure table defaults in `config/database.php`:

```php
<?php

return [
    'default' => [
        'engine' => 'InnoDB',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
    ],
    
    'timestamps' => [
        'enabled' => true,
        'format' => 'Y-m-d H:i:s',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
    ],
    
    'soft_deletes' => [
        'enabled' => true,
        'column' => 'deleted_at',
    ],
    
    'caching' => [
        'enabled' => true,
        'default_ttl' => 3600,
    ],
];
```

## Best Practices

1. **Use Appropriate Engines**: Choose InnoDB for transactional tables, MyISAM for read-heavy tables

2. **Index Strategically**: Add indexes for frequently queried columns, avoid over-indexing

3. **Enable Timestamps**: Use timestamps for auditing and tracking changes

4. **Consider Soft Deletes**: Use soft deletes for data that may need recovery

5. **Document Tables**: Add meaningful comments to table definitions

6. **Optimize Partitioning**: Use partitioning for very large tables

7. **Cache Wisely**: Enable caching for frequently accessed, rarely changed data

8. **Use Full-Text Indexes**: For text search functionality on large text fields

9. **Leverage Observers**: Use observers for cross-cutting concerns like logging

10. **Version Important Data**: Enable versioning for critical documents and records

## Performance Considerations

### Index Performance

```php
<?php

// Good: Single-column index for simple queries
#[Index(columns: ['user_id'])]

// Good: Composite index for multi-column queries
#[Index(columns: ['user_id', 'status', 'created_at'])]

// Bad: Too many indexes slow down writes
#[Index(columns: ['col1'])]
#[Index(columns: ['col2'])]
#[Index(columns: ['col3'])]
// ... (too many)
```

### Caching Strategy

```php
<?php

// Good: Cache stable data
#[Cacheable(ttl: 86400)]  // 24 hours for categories
class Category extends Model {}

// Good: Short TTL for frequently changing data
#[Cacheable(ttl: 300)]  // 5 minutes for products
class Product extends Model {}

// Bad: Don't cache rapidly changing data
#[Cacheable(ttl: 3600)]  // Not good for real-time data
class ActiveSession extends Model {}
```

## Troubleshooting

### Common Issues

**Issue**: Indexes not created

```php
// Solution: Ensure migrations are run
php neo migrate

// Or regenerate migrations
php neo make:migration create_products_table --model=Product
```

**Issue**: Soft deletes not working

```php
// Ensure SoftDeletes trait is used
#[Table(name: 'users')]
#[SoftDeletes]
class User extends Model
{
    use \Neo\Database\SoftDeletes;  // Required
}
```

**Issue**: Timestamps not updating

```php
// Ensure model has timestamps property
#[Table(name: 'posts')]
#[Timestamps]
class Post extends Model
{
    public $timestamps = true;  // Required
}
```

## Next Steps

- Learn about [Field Attributes](field-attributes.md)
- Explore [Relationship Attributes](relationships.md)
- Understand [Form Generation](form-generation.md)
- Master [Validation](validation.md)
