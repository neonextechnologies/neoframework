# 🗄️ Database Commands

NeoFramework provides a powerful set of database commands to manage your database schema and data. These commands make it easy to run migrations, seed data, and maintain your database structure.

## 📋 Table of Contents

- [Migration Commands](#migration-commands)
- [Seeding Commands](#seeding-commands)
- [Database Maintenance](#database-maintenance)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

## 🔄 Migration Commands

### migrate

Run all pending database migrations.

```bash
php neo migrate
```

#### Options

- `--force` - Force migrations to run in production
- `--path=<path>` - Specify custom migration path
- `--database=<name>` - Specify database connection
- `--pretend` - Show SQL queries without executing
- `--step` - Run migrations in batches
- `--seed` - Seed database after migrations

#### Basic Usage

```bash
# Run all pending migrations
php neo migrate

# Run migrations with seeding
php neo migrate --seed

# Preview migration queries
php neo migrate --pretend
```

#### Advanced Examples

**Force Production Migration**

```bash
# Use with caution in production
php neo migrate --force
```

**Custom Migration Path**

```bash
# Run migrations from custom directory
php neo migrate --path=database/migrations/custom
```

**Specific Database Connection**

```bash
# Run migrations on specific connection
php neo migrate --database=tenant_db
```

#### Output Example

```
Migrating: 2024_01_01_000000_create_users_table
Migrated:  2024_01_01_000000_create_users_table (45.23ms)

Migrating: 2024_01_02_000000_create_posts_table
Migrated:  2024_01_02_000000_create_posts_table (32.15ms)

Migrating: 2024_01_03_000000_add_status_to_posts_table
Migrated:  2024_01_03_000000_add_status_to_posts_table (18.67ms)

✓ 3 migrations completed successfully
```

### migrate:rollback

Rollback the last batch of migrations.

```bash
php neo migrate:rollback
```

#### Options

- `--force` - Force rollback in production
- `--path=<path>` - Specify custom migration path
- `--database=<name>` - Specify database connection
- `--pretend` - Show SQL queries without executing
- `--step=<count>` - Number of migrations to rollback

#### Basic Usage

```bash
# Rollback last batch
php neo migrate:rollback

# Rollback specific number of migrations
php neo migrate:rollback --step=3

# Preview rollback queries
php neo migrate:rollback --pretend
```

#### Advanced Examples

**Rollback Multiple Steps**

```bash
# Rollback last 5 migrations
php neo migrate:rollback --step=5
```

**Rollback Specific Path**

```bash
# Rollback migrations from custom directory
php neo migrate:rollback --path=database/migrations/custom
```

#### Output Example

```
Rolling back: 2024_01_03_000000_add_status_to_posts_table
Rolled back:  2024_01_03_000000_add_status_to_posts_table (12.34ms)

Rolling back: 2024_01_02_000000_create_posts_table
Rolled back:  2024_01_02_000000_create_posts_table (23.45ms)

✓ 2 migrations rolled back successfully
```

### migrate:reset

Rollback all database migrations.

```bash
php neo migrate:reset
```

#### Options

- `--force` - Force reset in production
- `--path=<path>` - Specify custom migration path
- `--database=<name>` - Specify database connection
- `--pretend` - Show SQL queries without executing

#### Basic Usage

```bash
# Reset all migrations
php neo migrate:reset

# Preview reset queries
php neo migrate:reset --pretend
```

#### Advanced Examples

**Reset with Force**

```bash
# Force reset in production (dangerous!)
php neo migrate:reset --force
```

**Reset Custom Path**

```bash
# Reset migrations from custom directory
php neo migrate:reset --path=database/migrations/modules
```

#### Output Example

```
Rolling back: 2024_01_03_000000_add_status_to_posts_table
Rolled back:  2024_01_03_000000_add_status_to_posts_table (15.67ms)

Rolling back: 2024_01_02_000000_create_posts_table
Rolled back:  2024_01_02_000000_create_posts_table (28.91ms)

Rolling back: 2024_01_01_000000_create_users_table
Rolled back:  2024_01_01_000000_create_users_table (34.12ms)

✓ All migrations rolled back successfully
```

### migrate:fresh

Drop all tables and re-run all migrations.

```bash
php neo migrate:fresh
```

#### Options

- `--force` - Force fresh migration in production
- `--database=<name>` - Specify database connection
- `--seed` - Seed database after migrations
- `--drop-views` - Drop all views as well
- `--drop-types` - Drop all custom types

#### Basic Usage

```bash
# Fresh migration
php neo migrate:fresh

# Fresh migration with seeding
php neo migrate:fresh --seed
```

#### Advanced Examples

**Complete Database Reset**

```bash
# Drop tables, views, and types, then migrate and seed
php neo migrate:fresh --drop-views --drop-types --seed
```

**Production Fresh Migration**

```bash
# Use with extreme caution!
php neo migrate:fresh --force --seed
```

#### Output Example

```
Dropping all tables............................... DONE

Migrating: 2024_01_01_000000_create_users_table
Migrated:  2024_01_01_000000_create_users_table (45.23ms)

Migrating: 2024_01_02_000000_create_posts_table
Migrated:  2024_01_02_000000_create_posts_table (32.15ms)

Migrating: 2024_01_03_000000_create_comments_table
Migrated:  2024_01_03_000000_create_comments_table (28.67ms)

✓ Database refreshed successfully
✓ 3 migrations completed
```

## 🌱 Seeding Commands

### db:seed

Seed the database with test data.

```bash
php neo db:seed
```

#### Options

- `--class=<name>` - Specify seeder class to run
- `--database=<name>` - Specify database connection
- `--force` - Force seeding in production

#### Basic Usage

```bash
# Run default seeder (DatabaseSeeder)
php neo db:seed

# Run specific seeder class
php neo db:seed --class=UserSeeder

# Run multiple seeders
php neo db:seed --class=UserSeeder
php neo db:seed --class=PostSeeder
```

#### Advanced Examples

**Seeding in Order**

```bash
# Seed users first, then their related data
php neo db:seed --class=UserSeeder
php neo db:seed --class=RoleSeeder
php neo db:seed --class=PostSeeder
php neo db:seed --class=CommentSeeder
```

**Production Seeding**

```bash
# Seed production data with force flag
php neo db:seed --class=ProductionSeeder --force
```

**Custom Database Connection**

```bash
# Seed specific database
php neo db:seed --database=tenant_db --class=TenantSeeder
```

#### Example DatabaseSeeder

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call other seeders
        $this->call([
            UserSeeder::class,
            RoleSeeder::class,
            PostSeeder::class,
            CommentSeeder::class,
        ]);
        
        // Or use factory
        \App\Models\User::factory(50)->create();
    }
}
```

#### Output Example

```
Seeding: Database\Seeders\UserSeeder
Seeded:  Database\Seeders\UserSeeder (234.56ms)

Seeding: Database\Seeders\PostSeeder
Seeded:  Database\Seeders\PostSeeder (145.23ms)

Seeding: Database\Seeders\CommentSeeder
Seeded:  Database\Seeders\CommentSeeder (98.12ms)

✓ Database seeded successfully
```

## 🧹 Database Maintenance

### db:wipe

Drop all tables, views, and types.

```bash
php neo db:wipe
```

#### Options

- `--force` - Force wipe in production
- `--database=<name>` - Specify database connection
- `--drop-views` - Drop all views
- `--drop-types` - Drop all custom types

#### Basic Usage

```bash
# Wipe all tables
php neo db:wipe

# Wipe everything including views and types
php neo db:wipe --drop-views --drop-types
```

#### Advanced Examples

**Complete Database Wipe**

```bash
# Remove all database objects
php neo db:wipe --force --drop-views --drop-types
```

**Specific Database Wipe**

```bash
# Wipe specific database connection
php neo db:wipe --database=testing
```

#### Output Example

```
Dropping all tables...
  - users
  - posts
  - comments
  - categories
  - tags
  - post_tag

Dropping all views...
  - active_users_view
  - published_posts_view

✓ Database wiped successfully
⚠ All data has been permanently deleted
```

## 📊 Migration Status

### migrate:status

Show the status of each migration.

```bash
php neo migrate:status
```

#### Options

- `--database=<name>` - Specify database connection
- `--path=<path>` - Specify custom migration path

#### Output Example

```
+------+-------------------------------------------------------+-------+
| Ran? | Migration                                             | Batch |
+------+-------------------------------------------------------+-------+
| Yes  | 2024_01_01_000000_create_users_table                  | 1     |
| Yes  | 2024_01_02_000000_create_posts_table                  | 1     |
| Yes  | 2024_01_03_000000_create_comments_table               | 2     |
| No   | 2024_01_04_000000_add_status_to_posts_table           |       |
| No   | 2024_01_05_000000_create_categories_table             |       |
+------+-------------------------------------------------------+-------+

Pending migrations: 2
Completed migrations: 3
```

## 🎯 Best Practices

### Migration Workflow

**Development Environment**

```bash
# Create and test new migration
php neo make:migration create_products_table
# Edit migration file
php neo migrate

# If issues found, rollback and fix
php neo migrate:rollback
# Fix migration file
php neo migrate
```

**Testing Environment**

```bash
# Fresh start with test data
php neo migrate:fresh --seed

# Run tests
php neo test
```

**Production Environment**

```bash
# Always backup database first!
# Run migrations with pretend first
php neo migrate --pretend

# Review output, then run for real
php neo migrate --force

# Verify application works
# If issues, rollback
php neo migrate:rollback --force
```

### Seeding Strategy

**Development Seeding**

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        \App\Models\User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);
        
        // Create test users
        \App\Models\User::factory(20)->create();
        
        // Create test posts
        \App\Models\Post::factory(50)->create();
    }
}
```

**Production Seeding**

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed essential data
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            DefaultSettingsSeeder::class,
        ]);
    }
}
```

### Database Backup Before Operations

```bash
# Backup before major operations
mysqldump -u user -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Run migration
php neo migrate

# If issues, restore from backup
mysql -u user -p database_name < backup_20240101_120000.sql
```

## 🔧 Common Workflows

### Fresh Development Setup

```bash
# Clone repository
git clone https://github.com/youruser/yourapp.git
cd yourapp

# Install dependencies
composer install

# Setup environment
cp .env.example .env
# Edit .env with database credentials

# Run migrations and seeders
php neo migrate:fresh --seed

# Start development server
php neo serve
```

### Adding New Feature with Migration

```bash
# Create migration
php neo make:migration add_featured_to_posts_table

# Edit migration file
# database/migrations/2024_01_01_000000_add_featured_to_posts_table.php

# Run migration
php neo migrate

# Test feature
php neo test

# Commit changes
git add database/migrations/
git commit -m "Add featured flag to posts"
```

### Resetting Test Database

```bash
# Reset and seed test database
php neo migrate:fresh --seed --database=testing

# Run test suite
php neo test
```

## 🚨 Troubleshooting

### Migration Already Exists

**Problem:** Migration appears to have run but table doesn't exist.

```bash
# Check migration status
php neo migrate:status

# If shown as migrated but table missing, manually fix
# Option 1: Rollback and re-run
php neo migrate:rollback
php neo migrate

# Option 2: Delete from migrations table and re-run
# DELETE FROM migrations WHERE migration = '2024_01_01_000000_create_users_table';
php neo migrate
```

### Foreign Key Constraint Errors

**Problem:** Cannot drop table due to foreign key constraints.

```php
// In migration down() method, drop foreign keys first
public function down(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
    });
    
    Schema::dropIfExists('posts');
}
```

### Batch Rollback Issues

**Problem:** Want to rollback specific migration.

```bash
# Check current status
php neo migrate:status

# Rollback specific number of migrations
php neo migrate:rollback --step=2

# Or reset and re-run to specific point
php neo migrate:reset
php neo migrate --path=database/migrations/2024_01_*.php
```

### Seeder Class Not Found

**Problem:** Seeder class cannot be loaded.

```bash
# Regenerate autoload files
composer dump-autoload

# Run seeder again
php neo db:seed --class=UserSeeder
```

### Production Safety

**Problem:** Cannot run migrations in production.

```bash
# Set APP_ENV in .env
APP_ENV=production

# Use --force flag (with caution!)
php neo migrate --force

# Or temporarily set environment
APP_ENV=local php neo migrate
```

## 🎨 Advanced Patterns

### Conditional Migrations

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only create if not exists
        if (!Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->timestamps();
            });
        }
        
        // Only add column if not exists
        if (Schema::hasTable('posts') && !Schema::hasColumn('posts', 'status')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->string('status')->default('draft');
            });
        }
    }
};
```

### Data Migrations

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add column
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name')->nullable();
        });
        
        // Migrate data
        DB::table('users')->get()->each(function ($user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'full_name' => $user->first_name . ' ' . $user->last_name
                ]);
        });
        
        // Make not nullable
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name')->nullable(false)->change();
        });
    }
};
```

### Multi-Database Migrations

```php
<?php

use Neo\Database\Migrations\Migration;
use Neo\Database\Schema\Blueprint;
use Neo\Database\Schema\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';
    
    public function up(): void
    {
        Schema::connection($this->connection)->create('tenant_data', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }
};
```

## 📚 Related Documentation

- [Migrations](../database/migrations.md) - Detailed migration documentation
- [Seeders](database-commands.md#seeding-commands) - Database seeding guide
- [Schema Builder](../database/getting-started.md#schema-builder) - Schema builder reference
- [Database Configuration](../getting-started/configuration.md#database) - Database setup

## 🔗 Quick Reference

```bash
# Migrations
php neo migrate                          # Run migrations
php neo migrate:rollback                 # Rollback last batch
php neo migrate:reset                    # Rollback all
php neo migrate:fresh                    # Drop and re-migrate
php neo migrate:fresh --seed             # Fresh with seeding
php neo migrate:status                   # Show status

# Seeding
php neo db:seed                          # Run DatabaseSeeder
php neo db:seed --class=UserSeeder       # Run specific seeder

# Maintenance
php neo db:wipe                          # Drop all tables

# Common flags
--force                                  # Force in production
--pretend                                # Preview SQL
--database=<name>                        # Specific connection
--path=<path>                            # Custom path
```
