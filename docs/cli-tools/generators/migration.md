# 🗄️ Migration Generator

Generate database migration files for creating and modifying database tables in your NeoFramework application. Migrations provide version control for your database schema.

## 📋 Table of Contents

- [Basic Usage](#basic-usage)
- [Command Options](#command-options)
- [Migration Types](#migration-types)
- [Generated Code](#generated-code)
- [Advanced Examples](#advanced-examples)
- [Best Practices](#best-practices)

## 🚀 Basic Usage

### Generate Migration

```bash
php neo make:migration create_users_table
```

**Generated:** `database/migrations/2024_01_01_000000_create_users_table.php`

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

## ⚙️ Command Options

### Available Options

| Option | Shortcut | Description |
|--------|----------|-------------|
| `--create=<table>` | | Create a new table |
| `--table=<table>` | | Modify an existing table |
| `--path=<path>` | | Custom migration path |
| `--force` | | Overwrite existing migration |

### Create Table Migration

```bash
php neo make:migration create_posts_table --create=posts
```

**Generated:**

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

### Modify Table Migration

```bash
php neo make:migration add_status_to_posts_table --table=posts
```

**Generated:**

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            //
        });
    }
};
```

## 📝 Migration Types

### Create Table

**Complete User Table:**

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('role', ['user', 'admin', 'moderator'])->default('user');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('email');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

**Complete Posts Table:**

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->integer('view_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('user_id');
            $table->index('category_id');
            $table->index('status');
            $table->index('published_at');
            $table->fullText(['title', 'content']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

### Add Columns

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->text('bio')->nullable()->after('avatar');
            $table->string('website')->nullable()->after('bio');
            $table->string('location')->nullable()->after('website');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'bio', 'website', 'location']);
        });
    }
};
```

### Modify Columns

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->text('bio')->nullable()->change();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 100)->change();
            $table->string('bio')->nullable()->change();
            $table->enum('status', ['active', 'inactive'])->default('active')->change();
        });
    }
};
```

### Rename Columns

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'full_name');
            $table->renameColumn('phone', 'phone_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('full_name', 'name');
            $table->renameColumn('phone_number', 'phone');
        });
    }
};
```

### Drop Columns

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['middle_name', 'suffix']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('middle_name')->nullable();
            $table->string('suffix', 10)->nullable();
        });
    }
};
```

### Foreign Keys

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('comments')
                ->nullOnDelete();
            $table->text('content');
            $table->timestamps();
            
            // Composite index
            $table->index(['post_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
```

### Pivot Tables

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['post_id', 'tag_id']);
            
            // Indexes
            $table->index('post_id');
            $table->index('tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_tag');
    }
};
```

### Indexes

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Add indexes
            $table->index('user_id');
            $table->index('status');
            $table->index(['status', 'published_at']);
            
            // Add unique index
            $table->unique('slug');
            
            // Add full-text index
            $table->fullText(['title', 'content']);
            
            // Custom index name
            $table->index('email', 'users_email_index');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['status', 'published_at']);
            $table->dropUnique(['slug']);
            $table->dropFullText(['title', 'content']);
            $table->dropIndex('users_email_index');
        });
    }
};
```

## 🔧 Column Types

### String and Text

```php
$table->string('name');                    // VARCHAR(255)
$table->string('name', 100);               // VARCHAR(100)
$table->text('description');               // TEXT
$table->mediumText('content');             // MEDIUMTEXT
$table->longText('content');               // LONGTEXT
$table->char('code', 4);                   // CHAR(4)
```

### Numeric

```php
$table->integer('votes');                  // INTEGER
$table->tinyInteger('status');             // TINYINT
$table->smallInteger('age');               // SMALLINT
$table->mediumInteger('count');            // MEDIUMINT
$table->bigInteger('value');               // BIGINT
$table->unsignedBigInteger('amount');      // UNSIGNED BIGINT
$table->decimal('amount', 8, 2);           // DECIMAL(8,2)
$table->double('amount', 8, 2);            // DOUBLE(8,2)
$table->float('amount', 8, 2);             // FLOAT(8,2)
```

### Date and Time

```php
$table->date('birth_date');                // DATE
$table->dateTime('created_at');            // DATETIME
$table->dateTime('created_at', 0);         // DATETIME(0)
$table->time('sunrise');                   // TIME
$table->timestamp('added_on');             // TIMESTAMP
$table->timestamps();                      // created_at, updated_at
$table->timestampsTz();                    // with timezone
$table->softDeletes();                     // deleted_at
$table->softDeletesTz();                   // with timezone
$table->year('birth_year');                // YEAR
```

### Boolean and Binary

```php
$table->boolean('confirmed');              // BOOLEAN
$table->binary('data');                    // BLOB
```

### Special Types

```php
$table->enum('status', ['pending', 'active']); // ENUM
$table->set('roles', ['admin', 'user']);   // SET
$table->json('options');                   // JSON
$table->jsonb('options');                  // JSONB (PostgreSQL)
$table->uuid('id');                        // UUID
$table->ipAddress('visitor');              // IP address
$table->macAddress('device');              // MAC address
```

### Foreign Keys

```php
$table->foreignId('user_id');              // UNSIGNED BIGINT
$table->foreignId('user_id')->constrained(); // + foreign key
$table->foreignId('user_id')
    ->constrained('users')                 // Specify table
    ->onDelete('cascade')                  // On delete action
    ->onUpdate('cascade');                 // On update action
```

## 🎨 Column Modifiers

```php
$table->string('email')->nullable();       // Allow NULL
$table->string('name')->default('Guest');  // Default value
$table->integer('votes')->unsigned();      // Unsigned
$table->string('email')->unique();         // Unique constraint
$table->string('bio')->after('email');     // Column order
$table->string('name')->first();           // Place first
$table->text('comment')->comment('User comment'); // Column comment
$table->integer('age')->autoIncrement();   // Auto increment
```

## 🎯 Advanced Examples

### E-commerce Product Table

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            
            // Basic info
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('specifications')->nullable();
            
            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2);
            
            // Inventory
            $table->string('sku', 50)->unique();
            $table->integer('quantity')->default(0);
            $table->integer('min_quantity')->default(0);
            $table->boolean('track_inventory')->default(true);
            
            // Dimensions
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            
            // Status
            $table->enum('status', ['active', 'inactive', 'out_of_stock'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->integer('view_count')->default(0);
            $table->integer('sales_count')->default(0);
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            // Images
            $table->string('featured_image')->nullable();
            $table->json('images')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('category_id');
            $table->index('brand_id');
            $table->index('status');
            $table->index('price');
            $table->index('created_at');
            $table->fullText(['name', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

### Multi-tenant Database

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('database')->unique();
            $table->json('config')->nullable();
            $table->enum('status', ['active', 'suspended', 'cancelled'])->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });
        
        Schema::create('tenant_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['owner', 'admin', 'member'])->default('member');
            $table->timestamps();
            
            $table->unique(['tenant_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_users');
        Schema::dropIfExists('tenants');
    }
};
```

### Polymorphic Relations

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('commentable'); // Creates commentable_id and commentable_type
            $table->text('content');
            $table->integer('likes_count')->default(0);
            $table->timestamps();
            
            $table->index(['commentable_id', 'commentable_type']);
        });
        
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('likeable');
            $table->timestamps();
            
            $table->unique(['user_id', 'likeable_id', 'likeable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
        Schema::dropIfExists('comments');
    }
};
```

## 🎯 Best Practices

### Naming Conventions

```php
// Table names: plural, snake_case
create_users_table
create_posts_table
create_order_items_table

// Modification: descriptive action
add_status_to_posts_table
remove_phone_from_users_table
modify_users_table_columns
create_posts_tags_pivot_table
```

### Always Include Down Method

```php
// Good
public function up(): void
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('posts');
}

// Bad
public function down(): void
{
    // Empty or missing
}
```

### Use Foreign Key Constraints

```php
// Good
$table->foreignId('user_id')
    ->constrained()
    ->onDelete('cascade');

// Bad
$table->unsignedBigInteger('user_id');
```

### Add Indexes for Performance

```php
// Index frequently queried columns
$table->index('email');
$table->index('status');
$table->index(['user_id', 'created_at']);

// Full-text for search
$table->fullText(['title', 'content']);
```

### Use Appropriate Data Types

```php
// Good
$table->enum('status', ['active', 'inactive']);
$table->decimal('price', 10, 2);
$table->boolean('is_active');

// Bad
$table->string('status'); // Use enum
$table->float('price'); // Use decimal for money
$table->integer('is_active'); // Use boolean
```

## 📚 Related Documentation

- [Migrations](../database/migrations.md) - Complete migration documentation
- [Schema Builder](../database/getting-started.md#schema-builder) - Schema builder reference
- [Database Commands](../cli-tools/database-commands.md) - Running migrations

## 🔗 Quick Reference

```bash
# Create table migration
php neo make:migration create_posts_table --create=posts

# Modify table migration
php neo make:migration add_status_to_posts --table=posts

# Custom path
php neo make:migration create_users_table --path=database/migrations/custom

# Run migrations
php neo migrate

# Rollback
php neo migrate:rollback
```

**Common Column Patterns:**

```php
// Timestamps
$table->timestamps();
$table->softDeletes();

// Foreign key
$table->foreignId('user_id')->constrained()->onDelete('cascade');

// Status enum
$table->enum('status', ['active', 'inactive'])->default('active');

// Money
$table->decimal('price', 10, 2);

// Boolean
$table->boolean('is_active')->default(true);
```
