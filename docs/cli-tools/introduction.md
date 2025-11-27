# CLI Tools

## Introduction

NeoFramework provides a powerful command-line interface (CLI) called **Neo** that helps you quickly build, manage, and maintain your application. The Neo CLI includes generators, database management tools, and utility commands.

## The Neo Command

Access the Neo CLI from your project root:

```bash
php neo [command] [options] [arguments]
```

### Getting Help

```bash
# List all commands
php neo list

# Get help for specific command
php neo help [command]
php neo [command] --help

# Command description
php neo make:controller --help
```

### Common Options

All commands support these options:

```bash
--help, -h        # Display help message
--quiet, -q       # Suppress output
--verbose, -v     # Increase verbosity
--version, -V     # Display version
--ansi            # Force ANSI output
--no-ansi         # Disable ANSI output
--no-interaction  # Non-interactive mode
```

## Command Categories

### Application Commands

```bash
# Start development server
php neo serve
php neo serve --port=8080
php neo serve --host=0.0.0.0

# Display application information
php neo about

# List all routes
php neo route:list
php neo route:list --filter=api

# Clear caches
php neo cache:clear
php neo config:clear
php neo view:clear
php neo route:clear

# Environment
php neo env
php neo env:set KEY=value
```

### Generator Commands

```bash
# Generate controller
php neo make:controller UserController
php neo make:controller Admin/UserController --resource

# Generate model
php neo make:model User
php neo make:model Post --migration --factory

# Generate middleware
php neo make:middleware AuthMiddleware

# Generate migration
php neo make:migration create_users_table
php neo make:migration add_status_to_users

# Generate seeder
php neo make:seeder UserSeeder

# Generate factory
php neo make:factory UserFactory

# Generate mail class
php neo make:mail WelcomeEmail

# Generate form
php neo make:form ContactForm

# Generate CRUD
php neo make:crud Post
```

### Database Commands

```bash
# Run migrations
php neo migrate
php neo migrate --force
php neo migrate --pretend

# Rollback migrations
php neo migrate:rollback
php neo migrate:rollback --step=2

# Reset migrations
php neo migrate:reset

# Refresh database
php neo migrate:refresh
php neo migrate:refresh --seed

# Migration status
php neo migrate:status

# Seed database
php neo db:seed
php neo db:seed --class=UserSeeder

# Database operations
php neo db:wipe
php neo db:show
php neo db:table users
```

### Plugin Commands

```bash
# List plugins
php neo plugin:list
php neo plugin:list --active

# Install plugin
php neo plugin:install plugin-name

# Activate/deactivate
php neo plugin:activate plugin-name
php neo plugin:deactivate plugin-name

# Uninstall plugin
php neo plugin:uninstall plugin-name

# Plugin info
php neo plugin:info plugin-name

# Make plugin
php neo make:plugin MyPlugin
```

### Testing Commands

```bash
# Run tests
php neo test
php neo test --filter=UserTest
php neo test --coverage

# Run specific test suite
php neo test:unit
php neo test:feature
php neo test:integration
```

### Queue Commands

```bash
# Start queue worker
php neo queue:work
php neo queue:work --queue=emails
php neo queue:work --tries=3

# List failed jobs
php neo queue:failed

# Retry failed job
php neo queue:retry [id]
php neo queue:retry all

# Flush failed jobs
php neo queue:flush
```

### Scheduler Commands

```bash
# Run scheduled tasks
php neo schedule:run

# List scheduled tasks
php neo schedule:list

# Test schedule
php neo schedule:test
```

### Cache Commands

```bash
# Clear all caches
php neo cache:clear

# Clear specific cache
php neo cache:forget key
php neo cache:forget prefix:*

# Cache application config
php neo config:cache

# Cache routes
php neo route:cache

# Cache views
php neo view:cache
```

### Maintenance Commands

```bash
# Enable maintenance mode
php neo down
php neo down --secret=my-secret

# Disable maintenance mode
php neo up

# Optimize application
php neo optimize
php neo optimize:clear
```

## Creating Custom Commands

### Command Class

Create a command in `app/Console/Commands/`:

```php
<?php

namespace App\Console\Commands;

use Neo\Console\Command;

class CustomCommand extends Command
{
    /**
     * Command signature
     */
    protected string $signature = 'app:custom {user} {--force}';
    
    /**
     * Command description
     */
    protected string $description = 'Custom command description';
    
    /**
     * Execute command
     */
    public function handle(): int
    {
        $user = $this->argument('user');
        $force = $this->option('force');
        
        $this->info("Processing user: {$user}");
        
        if ($force) {
            $this->warn('Force mode enabled');
        }
        
        // Command logic here
        
        $this->line('Command completed');
        
        return self::SUCCESS;
    }
}
```

### Command Signature

Define command signature with arguments and options:

```php
<?php

// Basic command
protected string $signature = 'app:command';

// With required argument
protected string $signature = 'app:command {user}';

// With optional argument
protected string $signature = 'app:command {user?}';

// With default value
protected string $signature = 'app:command {user=default}';

// With argument description
protected string $signature = 'app:command {user : The user ID}';

// With option
protected string $signature = 'app:command {--force}';

// Option with value
protected string $signature = 'app:command {--queue=}';

// Option with default
protected string $signature = 'app:command {--queue=default}';

// Option shortcut
protected string $signature = 'app:command {--Q|queue}';

// Array argument
protected string $signature = 'app:command {users*}';

// Array option
protected string $signature = 'app:command {--id=*}';

// Complete example
protected string $signature = 'app:process 
    {user : The user ID}
    {--force : Force the operation}
    {--queue= : The queue name}
    {--verbose : Verbose output}';
```

### Input/Output

```php
<?php

public function handle(): int
{
    // Get arguments
    $user = $this->argument('user');
    $all = $this->arguments();
    
    // Get options
    $force = $this->option('force');
    $queue = $this->option('queue');
    $all = $this->options();
    
    // Output
    $this->line('Regular text');
    $this->info('Info message');
    $this->comment('Comment message');
    $this->question('Question message');
    $this->error('Error message');
    $this->warn('Warning message');
    
    // New line
    $this->newLine();
    $this->newLine(3);
    
    // Table
    $this->table(
        ['Name', 'Email'],
        [
            ['John', 'john@example.com'],
            ['Jane', 'jane@example.com'],
        ]
    );
    
    // Ask questions
    $name = $this->ask('What is your name?');
    $password = $this->secret('Enter password');
    $confirmed = $this->confirm('Continue?');
    $choice = $this->choice(
        'Choose option',
        ['Option 1', 'Option 2', 'Option 3'],
        0  // default
    );
    
    // Multiple choice
    $choices = $this->multiChoice(
        'Select items',
        ['Item 1', 'Item 2', 'Item 3']
    );
    
    // Anticipate (autocomplete)
    $color = $this->anticipate(
        'Favorite color?',
        ['red', 'blue', 'green']
    );
    
    // Progress bar
    $users = User::all();
    $this->progressBar($users->count(), function($bar) use ($users) {
        foreach ($users as $user) {
            // Process user
            $bar->advance();
        }
    });
    
    // Manually controlled progress
    $bar = $this->createProgressBar(100);
    $bar->start();
    for ($i = 0; $i < 100; $i++) {
        // Do work
        $bar->advance();
    }
    $bar->finish();
    
    return self::SUCCESS;
}
```

### Calling Other Commands

```php
<?php

public function handle(): int
{
    // Call command
    $this->call('cache:clear');
    
    // Call with arguments
    $this->call('make:controller', [
        'name' => 'UserController',
        '--resource' => true,
    ]);
    
    // Call silently
    $this->callSilent('db:seed');
    
    // Call in background (async)
    $this->callAsync('queue:work');
    
    return self::SUCCESS;
}
```

### Registering Commands

Register in `app/Console/Kernel.php`:

```php
<?php

namespace App\Console;

use Neo\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register commands
     */
    protected array $commands = [
        Commands\CustomCommand::class,
        Commands\ProcessUsers::class,
        Commands\SyncData::class,
    ];
    
    /**
     * Define command schedules
     */
    protected function schedule(): void
    {
        $this->command('app:sync')
            ->daily()
            ->at('01:00');
            
        $this->command('app:cleanup')
            ->hourly();
    }
}
```

## Command Testing

### Writing Tests

```php
<?php

namespace Tests\Feature\Console;

use Tests\TestCase;

class CustomCommandTest extends TestCase
{
    public function test_command_executes_successfully()
    {
        $this->artisan('app:custom', ['user' => 'john'])
            ->assertExitCode(0)
            ->assertSuccessful();
    }
    
    public function test_command_displays_output()
    {
        $this->artisan('app:custom', ['user' => 'john'])
            ->expectsOutput('Processing user: john')
            ->assertExitCode(0);
    }
    
    public function test_command_asks_question()
    {
        $this->artisan('app:interactive')
            ->expectsQuestion('What is your name?', 'John')
            ->expectsOutput('Hello John')
            ->assertExitCode(0);
    }
    
    public function test_command_confirms_action()
    {
        $this->artisan('app:dangerous')
            ->expectsConfirmation('Are you sure?', 'yes')
            ->assertExitCode(0);
    }
    
    public function test_command_with_option()
    {
        $this->artisan('app:custom', [
            'user' => 'john',
            '--force' => true
        ])->assertExitCode(0);
    }
}
```

## Command Scheduling

### Defining Schedules

In `app/Console/Kernel.php`:

```php
<?php

protected function schedule(): void
{
    // Run command every minute
    $this->command('app:check')->everyMinute();
    
    // Run command hourly
    $this->command('app:hourly')->hourly();
    
    // Run command daily at specific time
    $this->command('app:daily')->dailyAt('13:00');
    
    // Run command weekly
    $this->command('app:weekly')->weekly();
    
    // Run command monthly
    $this->command('app:monthly')->monthly();
    
    // Run on specific days
    $this->command('app:report')->weekdays();
    $this->command('app:cleanup')->weekends();
    $this->command('app:backup')
        ->mondays()
        ->at('02:00');
    
    // Custom cron expression
    $this->command('app:custom')->cron('0 */6 * * *');
    
    // With conditions
    $this->command('app:sync')
        ->daily()
        ->when(function() {
            return config('app.sync_enabled');
        });
    
    // Skip conditions
    $this->command('app:task')
        ->daily()
        ->skip(function() {
            return Cache::has('task_completed');
        });
    
    // With callbacks
    $this->command('app:process')
        ->daily()
        ->before(function() {
            // Before command
        })
        ->after(function() {
            // After command
        })
        ->onSuccess(function() {
            // On success
        })
        ->onFailure(function() {
            // On failure
        });
}
```

### Running Scheduler

Set up cron job:

```bash
* * * * * cd /path-to-project && php neo schedule:run >> /dev/null 2>&1
```

## Best Practices

1. **Descriptive Names**: Use clear, descriptive command names
2. **Help Text**: Provide comprehensive help and descriptions
3. **Validation**: Validate input arguments and options
4. **Error Handling**: Handle errors gracefully
5. **Progress Indication**: Show progress for long-running tasks
6. **Logging**: Log important operations
7. **Exit Codes**: Return appropriate exit codes
8. **Testing**: Write tests for commands
9. **Idempotency**: Make commands safe to run multiple times
10. **Documentation**: Document command usage

## Troubleshooting

### Common Issues

**Command not found**:
```bash
# Clear cache
php neo cache:clear

# Check if command is registered
php neo list
```

**Permission denied**:
```bash
# Make neo executable
chmod +x neo

# Check file permissions
ls -la neo
```

**Database connection failed**:
```bash
# Check database configuration
php neo env

# Test connection
php neo db:show
```

## Next Steps

- Learn about [Database Commands](database-commands.md)
- Explore [Custom Commands](custom-commands.md)
- Study [Generator Commands](generators/controller.md)
