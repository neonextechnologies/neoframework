# 🌱 Seeder Generator

Generate database seeder classes for populating your database with test or default data in your NeoFramework application. Seeders provide a convenient way to insert data into your database.

## 📋 Table of Contents

- [Basic Usage](#basic-usage)
- [Generated Code](#generated-code)
- [Seeder Types](#seeder-types)
- [Using Factories](#using-factories)
- [Advanced Examples](#advanced-examples)
- [Best Practices](#best-practices)

## 🚀 Basic Usage

### Generate Seeder

```bash
php neo make:seeder UserSeeder
```

**Generated:** `database/seeders/UserSeeder.php`

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
    }
}
```

## 📝 Generated Code Examples

### Basic Seeder

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    }
}
```

### Using Factories

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 random users
        User::factory()
            ->count(50)
            ->create();

        // Create specific user
        User::factory()
            ->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'admin',
            ]);

        // Create users with posts
        User::factory()
            ->count(10)
            ->hasPosts(5)
            ->create();
    }
}
```

### Bulk Insert

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology'],
            ['name' => 'Business', 'slug' => 'business'],
            ['name' => 'Science', 'slug' => 'science'],
            ['name' => 'Health', 'slug' => 'health'],
            ['name' => 'Entertainment', 'slug' => 'entertainment'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Or use insert for better performance
        Category::insert($categories);
    }
}
```

### DatabaseSeeder

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
        // Call other seeders in order
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            PostSeeder::class,
            CommentSeeder::class,
        ]);
    }
}
```

## 🎯 Seeder Types

### Admin User Seeder

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin role if not exists
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Full system access',
            ]
        );

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign role
        $admin->roles()->syncWithoutDetaching($adminRole);

        $this->command->info('Admin user created successfully!');
    }
}
```

### Permission Seeder

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User permissions
            ['name' => 'view-users', 'display_name' => 'View Users'],
            ['name' => 'create-users', 'display_name' => 'Create Users'],
            ['name' => 'edit-users', 'display_name' => 'Edit Users'],
            ['name' => 'delete-users', 'display_name' => 'Delete Users'],
            
            // Post permissions
            ['name' => 'view-posts', 'display_name' => 'View Posts'],
            ['name' => 'create-posts', 'display_name' => 'Create Posts'],
            ['name' => 'edit-posts', 'display_name' => 'Edit Posts'],
            ['name' => 'delete-posts', 'display_name' => 'Delete Posts'],
            ['name' => 'publish-posts', 'display_name' => 'Publish Posts'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Assign permissions to roles
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->sync(Permission::all());
        }

        $this->command->info('Permissions seeded successfully!');
    }
}
```

### Settings Seeder

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General settings
            ['key' => 'site_name', 'value' => 'NeoFramework', 'type' => 'string'],
            ['key' => 'site_description', 'value' => 'A modern PHP framework', 'type' => 'string'],
            ['key' => 'site_email', 'value' => 'info@example.com', 'type' => 'string'],
            
            // Feature flags
            ['key' => 'registration_enabled', 'value' => '1', 'type' => 'boolean'],
            ['key' => 'comments_enabled', 'value' => '1', 'type' => 'boolean'],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean'],
            
            // Limits
            ['key' => 'posts_per_page', 'value' => '15', 'type' => 'integer'],
            ['key' => 'max_upload_size', 'value' => '2048', 'type' => 'integer'],
            
            // Social media
            ['key' => 'facebook_url', 'value' => '', 'type' => 'string'],
            ['key' => 'twitter_url', 'value' => '', 'type' => 'string'],
            ['key' => 'instagram_url', 'value' => '', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Settings seeded successfully!');
    }
}
```

### Blog Content Seeder

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $categories = Category::all();
        $tags = Tag::all();

        if ($users->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Please run UserSeeder and CategorySeeder first!');
            return;
        }

        // Create posts
        Post::factory()
            ->count(50)
            ->create([
                'user_id' => $users->random()->id,
                'category_id' => $categories->random()->id,
            ])
            ->each(function ($post) use ($tags) {
                // Attach random tags
                $post->tags()->attach(
                    $tags->random(rand(1, 5))->pluck('id')->toArray()
                );
            });

        // Create featured posts
        Post::factory()
            ->count(5)
            ->create([
                'is_featured' => true,
                'status' => 'published',
                'user_id' => $users->random()->id,
                'category_id' => $categories->random()->id,
            ]);

        $this->command->info('Posts seeded successfully!');
    }
}
```

## 🔧 Using Factories

### Simple Factory Usage

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\User;
use App\Models\Post;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 users
        User::factory()->count(10)->create();

        // Create 50 posts
        Post::factory()->count(50)->create();

        // Create user with specific attributes
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
```

### Factory States

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin users
        User::factory()
            ->count(3)
            ->admin()
            ->create();

        // Create verified users
        User::factory()
            ->count(20)
            ->verified()
            ->create();

        // Create unverified users
        User::factory()
            ->count(10)
            ->unverified()
            ->create();

        // Create suspended users
        User::factory()
            ->count(5)
            ->suspended()
            ->create();
    }
}
```

### Factory Relationships

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\User;

class CompleteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create users with posts and comments
        User::factory()
            ->count(10)
            ->has(Post::factory()->count(5)->has(
                Comment::factory()->count(3)
            ))
            ->create();

        // Alternative syntax
        User::factory()
            ->count(10)
            ->hasPosts(5)
            ->create();
    }
}
```

## 🎨 Advanced Examples

### E-commerce Seeder

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Tag;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        $brands = Brand::all();
        $tags = Tag::all();

        // Create featured products
        Product::factory()
            ->count(10)
            ->create([
                'is_featured' => true,
                'status' => 'active',
                'category_id' => $categories->random()->id,
                'brand_id' => $brands->random()->id,
            ])
            ->each(function ($product) use ($tags) {
                // Add images
                $product->images()->createMany([
                    ['path' => 'products/image1.jpg', 'order' => 1],
                    ['path' => 'products/image2.jpg', 'order' => 2],
                    ['path' => 'products/image3.jpg', 'order' => 3],
                ]);

                // Attach tags
                $product->tags()->attach($tags->random(3));

                // Create reviews
                $product->reviews()->createMany(
                    factory(Review::class, 5)->make()->toArray()
                );
            });

        // Create regular products
        Product::factory()
            ->count(100)
            ->create([
                'category_id' => $categories->random()->id,
                'brand_id' => $brands->random()->id,
            ]);

        $this->command->info('Products seeded successfully!');
    }
}
```

### Multi-tenant Seeder

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = [
            [
                'name' => 'Acme Corporation',
                'domain' => 'acme.example.com',
                'database' => 'tenant_acme',
            ],
            [
                'name' => 'Tech Solutions',
                'domain' => 'techsolutions.example.com',
                'database' => 'tenant_techsolutions',
            ],
        ];

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::create($tenantData);

            // Create admin user for tenant
            $admin = User::create([
                'name' => 'Admin',
                'email' => "admin@{$tenant->domain}",
                'password' => bcrypt('password'),
            ]);

            // Associate user with tenant
            $tenant->users()->attach($admin, ['role' => 'owner']);

            // Create regular users
            User::factory()
                ->count(5)
                ->create()
                ->each(function ($user) use ($tenant) {
                    $tenant->users()->attach($user, ['role' => 'member']);
                });

            $this->command->info("Tenant {$tenant->name} seeded successfully!");
        }
    }
}
```

### CSV Import Seeder

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\File;

class CsvProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('data/products.csv');

        if (!File::exists($csvFile)) {
            $this->command->error('CSV file not found!');
            return;
        }

        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file);

        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            Product::create([
                'name' => $data['name'],
                'sku' => $data['sku'],
                'price' => $data['price'],
                'quantity' => $data['quantity'],
                'category_id' => $data['category_id'],
            ]);

            $count++;
        }

        fclose($file);

        $this->command->info("{$count} products imported successfully!");
    }
}
```

### JSON Import Seeder

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Support\Facades\File;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonFile = database_path('data/locations.json');

        if (!File::exists($jsonFile)) {
            $this->command->error('JSON file not found!');
            return;
        }

        $data = json_decode(File::get($jsonFile), true);

        foreach ($data['countries'] as $countryData) {
            $country = Country::create([
                'name' => $countryData['name'],
                'code' => $countryData['code'],
            ]);

            foreach ($countryData['states'] as $stateData) {
                $state = $country->states()->create([
                    'name' => $stateData['name'],
                    'code' => $stateData['code'],
                ]);

                if (isset($stateData['cities'])) {
                    foreach ($stateData['cities'] as $cityName) {
                        $state->cities()->create(['name' => $cityName]);
                    }
                }
            }

            $this->command->info("Country {$country->name} seeded!");
        }
    }
}
```

### Progress Bar Seeder

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\User;
use App\Models\Post;

class LargeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $totalUsers = 1000;
        $batchSize = 100;

        $this->command->info('Creating users...');
        $bar = $this->command->getOutput()->createProgressBar($totalUsers);
        $bar->start();

        for ($i = 0; $i < $totalUsers; $i += $batchSize) {
            User::factory()->count($batchSize)->create();
            $bar->advance($batchSize);
        }

        $bar->finish();
        $this->command->newLine();

        $this->command->info('Creating posts...');
        $users = User::all();
        $totalPosts = 5000;
        $bar = $this->command->getOutput()->createProgressBar($totalPosts);
        $bar->start();

        for ($i = 0; $i < $totalPosts; $i += $batchSize) {
            Post::factory()
                ->count($batchSize)
                ->create(['user_id' => $users->random()->id]);
            $bar->advance($batchSize);
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Seeding completed!');
    }
}
```

## 🎯 Best Practices

### Order Dependencies

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
        // Seed in correct order (parent tables first)
        $this->call([
            // 1. Lookup tables
            RoleSeeder::class,
            PermissionSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            
            // 2. User data
            UserSeeder::class,
            
            // 3. Content
            PostSeeder::class,
            CommentSeeder::class,
            
            // 4. Settings
            SettingSeeder::class,
        ]);
    }
}
```

### Environment-Specific Seeding

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
        if (app()->environment('production')) {
            $this->call([
                RoleSeeder::class,
                PermissionSeeder::class,
                SettingSeeder::class,
            ]);
        } else {
            $this->call([
                DevelopmentSeeder::class,
            ]);
        }
    }
}
```

### Use Transactions

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            User::factory()->count(100)->create();
        });
    }
}
```

### Idempotent Seeders

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology'],
            ['name' => 'Business', 'slug' => 'business'],
        ];

        foreach ($categories as $category) {
            // Use firstOrCreate to prevent duplicates
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
```

### Performance Optimization

```php
<?php

namespace Database\Seeders;

use Neo\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks for faster seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Use chunk for large datasets
        $products = [];
        for ($i = 0; $i < 10000; $i++) {
            $products[] = [
                'name' => "Product $i",
                'price' => rand(10, 1000),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert in batches of 500
            if (count($products) >= 500) {
                Product::insert($products);
                $products = [];
            }
        }

        // Insert remaining
        if (!empty($products)) {
            Product::insert($products);
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('Products seeded successfully!');
    }
}
```

## 📚 Related Documentation

- [Database Commands](../cli-tools/database-commands.md) - Running seeders
- [Factories](../database/getting-started.md#factories) - Model factories
- [Migrations](../database/migrations.md) - Database migrations

## 🔗 Quick Reference

```bash
# Generate seeder
php neo make:seeder UserSeeder

# Run all seeders
php neo db:seed

# Run specific seeder
php neo db:seed --class=UserSeeder

# Run with migrations
php neo migrate:fresh --seed
```

**Common Patterns:**

```php
// Create single record
Model::create([...]);

// Create multiple records
Model::insert([...]);

// Use factory
Model::factory()->count(10)->create();

// First or create
Model::firstOrCreate(['email' => '...'], [...]);

// Call other seeders
$this->call([SeederClass::class]);
```
