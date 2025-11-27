# Database: Getting Started

## Introduction

NeoFramework makes interacting with databases extremely simple across a variety of supported databases using raw SQL, a fluent query builder, and the Eloquent ORM. Currently, NeoFramework provides first-party support for five databases:

- MySQL 5.7+ / MariaDB 10.3+
- PostgreSQL 10.0+
- SQLite 3.8.8+
- SQL Server 2017+
- Oracle Database 12c+

## Configuration

Database configuration is located in `config/database.php`. In this file, you may define all of your database connections, as well as specify which connection should be used by default.

### Environment Configuration

Configure your database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=neoframework
DB_USERNAME=root
DB_PASSWORD=
```

### Multiple Connections

You can define multiple database connections:

```php
// config/database.php
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'forge'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],
    
    'pgsql' => [
        'driver' => 'pgsql',
        'host' => env('DB_HOST_PGSQL', '127.0.0.1'),
        'port' => env('DB_PORT_PGSQL', '5432'),
        'database' => env('DB_DATABASE_PGSQL', 'forge'),
        'username' => env('DB_USERNAME_PGSQL', 'forge'),
        'password' => env('DB_PASSWORD_PGSQL', ''),
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public',
    ],
],
```

## Running Raw SQL Queries

### Select Queries

```php
use NeoPhp\Database\DB;

$users = DB::select('SELECT * FROM users WHERE active = ?', [1]);

foreach ($users as $user) {
    echo $user->name;
}
```

### Named Bindings

```php
$users = DB::select('SELECT * FROM users WHERE id = :id', ['id' => 1]);
```

### Insert Statements

```php
DB::insert('INSERT INTO users (name, email) VALUES (?, ?)', [
    'John Doe',
    'john@example.com'
]);
```

### Update Statements

```php
$affected = DB::update(
    'UPDATE users SET active = ? WHERE id = ?',
    [1, 100]
);
```

### Delete Statements

```php
$deleted = DB::delete('DELETE FROM users WHERE id = ?', [1]);
```

### General Statements

For DDL statements (CREATE, ALTER, DROP):

```php
DB::statement('DROP TABLE users');
DB::statement('CREATE INDEX idx_users_email ON users(email)');
```

## Using Multiple Database Connections

```php
$users = DB::connection('pgsql')->select('SELECT * FROM users');

// Or using the query builder
$users = DB::connection('pgsql')
    ->table('users')
    ->get();
```

## Query Builder

The database query builder provides a convenient, fluent interface to creating and running database queries.

### Retrieving Results

#### Get All Rows

```php
$users = DB::table('users')->get();
```

#### Get Single Row

```php
$user = DB::table('users')->where('id', 1)->first();
```

#### Get Single Value

```php
$email = DB::table('users')->where('id', 1)->value('email');
```

#### Get Column Values

```php
$names = DB::table('users')->pluck('name');
// ['John', 'Jane', 'Bob']

$names = DB::table('users')->pluck('name', 'id');
// [1 => 'John', 2 => 'Jane', 3 => 'Bob']
```

#### Chunking Results

```php
DB::table('users')->orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        // Process user
    }
});
```

Stop processing by returning false:

```php
DB::table('users')->orderBy('id')->chunk(100, function ($users) {
    // Stop after first chunk
    return false;
});
```

### Aggregates

```php
$count = DB::table('users')->count();
$max = DB::table('users')->max('price');
$min = DB::table('users')->min('price');
$avg = DB::table('users')->avg('price');
$sum = DB::table('users')->sum('price');
```

### Select Statements

#### Specifying Columns

```php
$users = DB::table('users')
    ->select('name', 'email')
    ->get();

$users = DB::table('users')
    ->select('name', 'email as user_email')
    ->get();
```

#### Distinct

```php
$users = DB::table('users')->distinct()->get();
```

### Where Clauses

#### Basic Where

```php
$users = DB::table('users')
    ->where('active', 1)
    ->get();

$users = DB::table('users')
    ->where('votes', '>=', 100)
    ->get();
```

#### Or Where

```php
$users = DB::table('users')
    ->where('votes', '>', 100)
    ->orWhere('name', 'John')
    ->get();
```

#### Additional Where Clauses

```php
// whereBetween
DB::table('users')
    ->whereBetween('votes', [1, 100])
    ->get();

// whereNotBetween
DB::table('users')
    ->whereNotBetween('votes', [1, 100])
    ->get();

// whereIn
DB::table('users')
    ->whereIn('id', [1, 2, 3])
    ->get();

// whereNotIn
DB::table('users')
    ->whereNotIn('id', [1, 2, 3])
    ->get();

// whereNull
DB::table('users')
    ->whereNull('updated_at')
    ->get();

// whereNotNull
DB::table('users')
    ->whereNotNull('updated_at')
    ->get();

// whereDate
DB::table('users')
    ->whereDate('created_at', '2024-01-01')
    ->get();

// whereMonth, whereDay, whereYear, whereTime
DB::table('users')
    ->whereMonth('created_at', 12)
    ->get();

// whereColumn
DB::table('users')
    ->whereColumn('first_name', 'last_name')
    ->get();
```

### Ordering, Grouping, Limit & Offset

```php
$users = DB::table('users')
    ->orderBy('name', 'desc')
    ->get();

$users = DB::table('users')
    ->groupBy('account_id')
    ->having('account_id', '>', 100)
    ->get();

$users = DB::table('users')
    ->skip(10)
    ->take(5)
    ->get();

// Or
$users = DB::table('users')
    ->offset(10)
    ->limit(5)
    ->get();
```

### Joins

#### Inner Join

```php
$users = DB::table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', 'posts.title')
    ->get();
```

#### Left Join

```php
$users = DB::table('users')
    ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
```

#### Advanced Join Clauses

```php
DB::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')
             ->where('contacts.user_id', '>', 5);
    })
    ->get();
```

### Unions

```php
$first = DB::table('users')->whereNull('first_name');

$users = DB::table('users')
    ->whereNull('last_name')
    ->union($first)
    ->get();
```

## Insert, Update, Delete

### Inserts

```php
DB::table('users')->insert([
    'email' => 'john@example.com',
    'votes' => 0
]);

// Insert multiple records
DB::table('users')->insert([
    ['email' => 'john@example.com', 'votes' => 0],
    ['email' => 'jane@example.com', 'votes' => 0],
]);

// Get auto-incrementing ID
$id = DB::table('users')->insertGetId([
    'email' => 'john@example.com',
    'votes' => 0
]);
```

### Updates

```php
$affected = DB::table('users')
    ->where('id', 1)
    ->update(['votes' => 1]);

// Update or insert
DB::table('users')
    ->updateOrInsert(
        ['email' => 'john@example.com'],
        ['name' => 'John Doe', 'votes' => 100]
    );

// Increment / Decrement
DB::table('users')->increment('votes');
DB::table('users')->increment('votes', 5);
DB::table('users')->decrement('votes');
DB::table('users')->decrement('votes', 5);

// With additional columns
DB::table('users')->increment('votes', 1, ['name' => 'John']);
```

### Deletes

```php
DB::table('users')->where('votes', '>', 100)->delete();

DB::table('users')->delete();

// Truncate
DB::table('users')->truncate();
```

## Transactions

Run queries within a database transaction:

```php
use NeoPhp\Database\DB;

DB::transaction(function () {
    DB::table('users')->update(['votes' => 1]);
    DB::table('posts')->delete();
});
```

### Manual Transactions

```php
DB::beginTransaction();

try {
    DB::table('users')->update(['votes' => 1]);
    DB::table('posts')->delete();
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

### Nested Transactions

```php
DB::transaction(function () {
    DB::table('users')->update(['votes' => 1]);
    
    DB::transaction(function () {
        DB::table('posts')->delete();
    });
});
```

## Debugging

### Dump Query

```php
DB::table('users')->where('id', 1)->dd();
// Dumps the SQL query and bindings
```

### Listen for Queries

```php
DB::listen(function ($query) {
    echo $query->sql;
    print_r($query->bindings);
    echo $query->time;
});
```

## Practical Examples

### Example 1: User Dashboard Statistics

```php
class DashboardController extends Controller
{
    public function stats()
    {
        // Total users
        $totalUsers = DB::table('users')->count();
        
        // Active users (logged in last 30 days)
        $activeUsers = DB::table('users')
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count();
        
        // New users this month
        $newUsers = DB::table('users')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // Revenue by month
        $revenueByMonth = DB::table('orders')
            ->selectRaw('MONTH(created_at) as month, SUM(total) as revenue')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->get();
        
        // Top products
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();
        
        return view('dashboard', compact(
            'totalUsers',
            'activeUsers',
            'newUsers',
            'revenueByMonth',
            'topProducts'
        ));
    }
}
```

### Example 2: Complex Report Query

```php
$report = DB::table('orders')
    ->join('users', 'orders.user_id', '=', 'users.id')
    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
    ->join('products', 'order_items.product_id', '=', 'products.id')
    ->select(
        'users.name as customer_name',
        'users.email',
        DB::raw('COUNT(DISTINCT orders.id) as order_count'),
        DB::raw('SUM(order_items.quantity * order_items.price) as total_spent')
    )
    ->whereBetween('orders.created_at', [$startDate, $endDate])
    ->groupBy('users.id', 'users.name', 'users.email')
    ->having('total_spent', '>', 1000)
    ->orderBy('total_spent', 'desc')
    ->get();
```

## Best Practices

### 1. Use Parameter Binding

**Good:**
```php
DB::select('SELECT * FROM users WHERE id = ?', [$id]);
```

**Bad:**
```php
DB::select("SELECT * FROM users WHERE id = $id"); // SQL injection risk!
```

### 2. Use Transactions for Related Operations

```php
DB::transaction(function () use ($user, $post) {
    $user->increment('post_count');
    $post->save();
});
```

### 3. Use Query Builder Over Raw SQL

Query builder is safer and more maintainable.

### 4. Index Your Queries

```php
DB::statement('CREATE INDEX idx_users_email ON users(email)');
```

### 5. Chunk Large Result Sets

```php
DB::table('users')->orderBy('id')->chunk(100, function ($users) {
    // Process 100 users at a time
});
```

## Next Steps

- [Query Builder](query-builder.md) - Advanced query building
- [Migrations](migrations.md) - Database version control
- [Seeding](seeding.md) - Populate database with test data
- [Eloquent ORM](eloquent.md) - Work with models
