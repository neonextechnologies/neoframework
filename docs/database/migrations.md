# Migrations

## Introduction

Migrations are like version control for your database, allowing your team to define and share the application's database schema definition. Migrations are typically paired with NeoFramework's schema builder to build your application's database schema.

## Generating Migrations

### Create Migration

```bash
php neo make:migration create_users_table
php neo make:migration add_votes_to_users_table
php neo make:migration create_posts_table --create=posts
php neo make:migration add_status_to_posts --table=posts
```

Migration files are stored in `database/migrations` directory.

### Migration Structure

```php
<?php

use NeoPhp\Database\Schema\Blueprint;
use NeoPhp\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
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

## Running Migrations

### Run All Migrations

```bash
php neo migrate
```

### Rollback Last Migration

```bash
php neo migrate:rollback
```

### Rollback Multiple Steps

```bash
php neo migrate:rollback --step=5
```

### Reset All Migrations

```bash
php neo migrate:reset
```

### Refresh Database

```bash
# Rollback and re-run all migrations
php neo migrate:refresh

# Refresh and seed
php neo migrate:refresh --seed
```

### Fresh Migration

```bash
# Drop all tables and re-run migrations
php neo migrate:fresh

# Fresh and seed
php neo migrate:fresh --seed
```

## Tables

### Creating Tables

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});
```

### Checking Table Existence

```php
if (Schema::hasTable('users')) {
    // Table exists
}

if (Schema::hasColumn('users', 'email')) {
    // Column exists
}
```

### Renaming Tables

```php
Schema::rename($from, $to);
```

### Dropping Tables

```php
Schema::drop('users');
Schema::dropIfExists('users');
```

## Columns

### Available Column Types

```php
$table->id();                          // Auto-incrementing ID
$table->bigIncrements('id');          // BIGINT auto-increment
$table->binary('data');               // BLOB
$table->boolean('confirmed');         // BOOLEAN
$table->char('name', 100);           // CHAR
$table->date('created_at');          // DATE
$table->dateTime('created_at');      // DATETIME
$table->decimal('amount', 8, 2);     // DECIMAL
$table->double('amount', 8, 2);      // DOUBLE
$table->enum('level', ['easy', 'hard']); // ENUM
$table->float('amount', 8, 2);       // FLOAT
$table->integer('votes');            // INTEGER
$table->json('options');             // JSON
$table->longText('description');     // LONGTEXT
$table->mediumText('description');   // MEDIUMTEXT
$table->string('name', 100);         // VARCHAR
$table->text('description');         // TEXT
$table->time('sunrise');             // TIME
$table->timestamp('added_on');       // TIMESTAMP
$table->uuid('id');                  // UUID

// Foreign keys
$table->foreignId('user_id');        // BIGINT for foreign key
$table->foreignUuid('user_id');      // UUID for foreign key
```

### Column Modifiers

```php
$table->string('email')->nullable();           // Allow NULL
$table->string('name')->default('Guest');      // Default value
$table->integer('votes')->unsigned();          // UNSIGNED
$table->decimal('amount')->unsigned();         // UNSIGNED
$table->timestamp('created_at')->useCurrent(); // Use current timestamp
$table->string('email')->unique();             // Unique constraint
$table->integer('user_id')->index();          // Add index
$table->text('comment')->comment('User comment'); // Column comment
$table->integer('votes')->after('name');      // After column (MySQL)
$table->integer('votes')->first();            // First column (MySQL)
```

### Modifying Columns

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('name', 100)->change();  // Change column
    $table->renameColumn('from', 'to');      // Rename column
});
```

### Dropping Columns

```php
Schema::table('users', function (Blueprint $table) {
    $table->dropColumn('votes');
    $table->dropColumn(['votes', 'avatar']);
});
```

## Indexes

### Creating Indexes

```php
$table->string('email')->unique();      // Unique index
$table->index('email');                 // Regular index
$table->index(['user_id', 'created_at']); // Compound index

// Custom index name
$table->unique('email', 'unique_email');
```

### Dropping Indexes

```php
$table->dropUnique('users_email_unique');
$table->dropIndex('users_email_index');
```

## Foreign Keys

### Creating Foreign Keys

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('title');
    $table->timestamps();
});

// Specify table name
$table->foreignId('user_id')->constrained('users');

// Custom column name
$table->foreignId('author_id')->constrained('users', 'id');

// On delete/update actions
$table->foreignId('user_id')
    ->constrained()
    ->onDelete('cascade')
    ->onUpdate('cascade');
```

### Dropping Foreign Keys

```php
$table->dropForeign('posts_user_id_foreign');
$table->dropForeign(['user_id']);
```

### Enable/Disable Foreign Keys

```php
Schema::disableForeignKeyConstraints();
Schema::enableForeignKeyConstraints();
```

## Practical Examples

### Example 1: Users Table

```php
<?php

use NeoPhp\Database\Schema\Blueprint;
use NeoPhp\Database\Migrations\Migration;

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
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

### Example 2: Posts with Relationships

```php
<?php

use NeoPhp\Database\Schema\Blueprint;
use NeoPhp\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->integer('views')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('published_at');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

### Example 3: Pivot Table

```php
<?php

use NeoPhp\Database\Schema\Blueprint;
use NeoPhp\Database\Migrations\Migration;

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
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_tag');
    }
};
```

### Example 4: Polymorphic Table

```php
<?php

use NeoPhp\Database\Schema\Blueprint;
use NeoPhp\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('commentable'); // commentable_id, commentable_type
            $table->text('body');
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index(['commentable_id', 'commentable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
```

### Example 5: E-commerce Orders

```php
<?php

use NeoPhp\Database\Schema\Blueprint;
use NeoPhp\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', [
                'pending',
                'processing',
                'shipped',
                'delivered',
                'cancelled'
            ])->default('pending');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('shipping', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('USD');
            
            // Shipping address
            $table->string('shipping_name');
            $table->string('shipping_address');
            $table->string('shipping_city');
            $table->string('shipping_state');
            $table->string('shipping_zip', 20);
            $table->string('shipping_country');
            $table->string('shipping_phone', 20);
            
            // Billing address
            $table->string('billing_name');
            $table->string('billing_address');
            $table->string('billing_city');
            $table->string('billing_state');
            $table->string('billing_zip', 20);
            $table->string('billing_country');
            
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('order_number');
            $table->index('status');
            $table->index('created_at');
        });
        
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->string('product_name');
            $table->string('product_sku', 50);
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2);
            $table->decimal('total', 10, 2);
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
```

### Example 6: Adding Column to Existing Table

```php
<?php

use NeoPhp\Database\Schema\Blueprint;
use NeoPhp\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('two_factor_secret')->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at'
            ]);
        });
    }
};
```

## Best Practices

### 1. Always Provide Down Method

```php
public function down(): void
{
    Schema::dropIfExists('users');
}
```

### 2. Use Descriptive Migration Names

```bash
# Good
php neo make:migration create_users_table
php neo make:migration add_status_to_posts_table
php neo make:migration add_foreign_keys_to_comments_table

# Bad
php neo make:migration update_database
php neo make:migration changes
```

### 3. Order Migrations Correctly

Foreign key migrations should come after the tables they reference.

### 4. Use Soft Deletes When Appropriate

```php
$table->softDeletes();
```

### 5. Add Indexes for Frequently Queried Columns

```php
$table->index('email');
$table->index(['user_id', 'status']);
```

### 6. Use Unsigned for IDs

```php
$table->unsignedBigInteger('user_id');
// or
$table->foreignId('user_id');
```

## Testing Migrations

```php
class MigrationTest extends TestCase
{
    public function test_users_table_exists()
    {
        $this->assertTrue(Schema::hasTable('users'));
    }
    
    public function test_users_table_has_columns()
    {
        $this->assertTrue(Schema::hasColumn('users', 'email'));
        $this->assertTrue(Schema::hasColumn('users', 'password'));
    }
}
```

## Next Steps

- [Seeding](seeding.md) - Populate database with test data
- [Eloquent ORM](eloquent.md) - Working with models
- [Query Builder](query-builder.md) - Building queries
