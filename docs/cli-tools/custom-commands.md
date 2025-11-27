# 🛠️ Custom Commands

NeoFramework's CLI system allows you to create powerful custom Artisan-style commands for your application. Build interactive command-line tools with input validation, formatted output, and progress indicators.

## 📋 Table of Contents

- [Creating Commands](#creating-commands)
- [Command Structure](#command-structure)
- [Input Handling](#input-handling)
- [Output Formatting](#output-formatting)
- [Validation](#validation)
- [Interactive Commands](#interactive-commands)
- [Progress Indicators](#progress-indicators)
- [Best Practices](#best-practices)

## 🎯 Creating Commands

### Generate Command

```bash
php neo make:command SendEmailsCommand
```

#### Generated Command Structure

```php
<?php

namespace App\Console\Commands;

use Neo\Console\Command;

class SendEmailsCommand extends Command
{
    /**
     * The name and signature of the command.
     *
     * @var string
     */
    protected $signature = 'emails:send';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Send pending emails to users';

    /**
     * Execute the command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Sending emails...');
        
        // Command logic here
        
        $this->success('Emails sent successfully!');
        
        return Command::SUCCESS;
    }
}
```

### Registering Commands

**In AppServiceProvider**

```php
<?php

namespace App\Providers;

use Neo\Foundation\ServiceProvider;
use App\Console\Commands\SendEmailsCommand;
use App\Console\Commands\GenerateReportCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                SendEmailsCommand::class,
                GenerateReportCommand::class,
            ]);
        }
    }
}
```

## 🏗️ Command Structure

### Command Signature

Define your command's name, arguments, and options.

```php
/**
 * Basic command signature
 */
protected $signature = 'emails:send';

/**
 * Command with required argument
 */
protected $signature = 'user:delete {id}';

/**
 * Command with optional argument
 */
protected $signature = 'user:show {id?}';

/**
 * Command with argument default value
 */
protected $signature = 'user:show {id=1}';

/**
 * Command with option
 */
protected $signature = 'emails:send {--queue}';

/**
 * Command with option shortcut
 */
protected $signature = 'emails:send {--Q|queue}';

/**
 * Command with option value
 */
protected $signature = 'user:create {--role=admin}';

/**
 * Command with array argument
 */
protected $signature = 'user:delete {ids*}';

/**
 * Command with array option
 */
protected $signature = 'emails:send {--to=*}';

/**
 * Complete example
 */
protected $signature = 'report:generate 
                        {type : The type of report to generate}
                        {--format=pdf : Output format (pdf, csv, excel)}
                        {--email= : Email address to send report}
                        {--verbose : Display detailed output}';
```

### Command Description

```php
/**
 * Short description
 */
protected $description = 'Send pending emails';

/**
 * Detailed description with help text
 */
protected $description = 'Send pending emails to users with queued notifications';

/**
 * Override help text
 */
public function getHelp(): string
{
    return <<<'HELP'
This command sends all pending emails in the queue.

Usage:
  php neo emails:send

Options:
  --queue         Queue the email sending process
  --batch=<size>  Number of emails to send per batch

Examples:
  php neo emails:send
  php neo emails:send --queue
  php neo emails:send --batch=100
HELP;
}
```

### Return Codes

```php
public function handle(): int
{
    try {
        // Command logic
        
        return Command::SUCCESS;      // 0 - Success
    } catch (\Exception $e) {
        $this->error($e->getMessage());
        return Command::FAILURE;      // 1 - General failure
    }
}

// Custom exit codes
public function handle(): int
{
    if (!$this->validateInput()) {
        return 2; // Invalid input
    }
    
    if (!$this->hasPermission()) {
        return 3; // Permission denied
    }
    
    return Command::SUCCESS;
}
```

## 📥 Input Handling

### Arguments

```php
public function handle(): int
{
    // Get required argument
    $userId = $this->argument('id');
    
    // Get optional argument with default
    $limit = $this->argument('limit') ?? 10;
    
    // Get array argument
    $ids = $this->argument('ids');
    
    // Get all arguments
    $arguments = $this->arguments();
    
    return Command::SUCCESS;
}
```

**Example Command: User Delete**

```php
<?php

namespace App\Console\Commands;

use Neo\Console\Command;
use App\Models\User;

class UserDeleteCommand extends Command
{
    protected $signature = 'user:delete {id : The ID of the user to delete}';
    protected $description = 'Delete a user from the database';

    public function handle(): int
    {
        $userId = $this->argument('id');
        
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return Command::FAILURE;
        }
        
        if ($this->confirm("Are you sure you want to delete {$user->name}?")) {
            $user->delete();
            $this->success("User deleted successfully");
            return Command::SUCCESS;
        }
        
        $this->info('Operation cancelled');
        return Command::SUCCESS;
    }
}
```

### Options

```php
public function handle(): int
{
    // Check if option exists
    if ($this->option('queue')) {
        // Queue logic
    }
    
    // Get option value
    $format = $this->option('format');
    
    // Get option with default
    $batch = $this->option('batch') ?? 100;
    
    // Get array option
    $recipients = $this->option('to');
    
    // Get all options
    $options = $this->options();
    
    return Command::SUCCESS;
}
```

**Example Command: Report Generator**

```php
<?php

namespace App\Console\Commands;

use Neo\Console\Command;

class ReportGenerateCommand extends Command
{
    protected $signature = 'report:generate 
                            {type : Report type (sales, users, analytics)}
                            {--format=pdf : Output format}
                            {--start-date= : Start date (Y-m-d)}
                            {--end-date= : End date (Y-m-d)}
                            {--email= : Email report to address}';
    
    protected $description = 'Generate system reports';

    public function handle(): int
    {
        $type = $this->argument('type');
        $format = $this->option('format');
        $startDate = $this->option('start-date') ?? now()->subDays(30);
        $endDate = $this->option('end-date') ?? now();
        
        $this->info("Generating {$type} report...");
        $this->line("Format: {$format}");
        $this->line("Period: {$startDate} to {$endDate}");
        
        // Generate report logic
        $filename = $this->generateReport($type, $format, $startDate, $endDate);
        
        // Email if requested
        if ($email = $this->option('email')) {
            $this->sendEmail($email, $filename);
            $this->success("Report sent to {$email}");
        }
        
        $this->success("Report saved: {$filename}");
        return Command::SUCCESS;
    }
    
    private function generateReport($type, $format, $start, $end): string
    {
        // Report generation logic
        return "reports/{$type}_" . date('Y-m-d') . ".{$format}";
    }
    
    private function sendEmail($email, $filename): void
    {
        // Email sending logic
    }
}
```

## 🎨 Output Formatting

### Basic Output Methods

```php
public function handle(): int
{
    // Information (blue)
    $this->info('Processing data...');
    
    // Success (green)
    $this->success('Operation completed!');
    
    // Warning (yellow)
    $this->warn('This action cannot be undone');
    
    // Error (red)
    $this->error('Failed to connect to database');
    
    // Plain line
    $this->line('Regular text output');
    
    // New line
    $this->newLine();
    $this->newLine(3); // Multiple lines
    
    return Command::SUCCESS;
}
```

### Formatted Output

```php
public function handle(): int
{
    // Comment style (gray)
    $this->comment('// Processing users...');
    
    // Question style (cyan)
    $this->question('What would you like to do?');
    
    // Custom colored output
    $this->line('<fg=magenta>Custom color text</>');
    $this->line('<bg=yellow;fg=black>Background color</>');
    
    // Bold, underline, and formatting
    $this->line('<options=bold>Bold text</>');
    $this->line('<options=underscore>Underlined text</>');
    
    return Command::SUCCESS;
}
```

### Tables

```php
public function handle(): int
{
    // Simple table
    $this->table(
        ['ID', 'Name', 'Email'],
        [
            [1, 'John Doe', 'john@example.com'],
            [2, 'Jane Smith', 'jane@example.com'],
            [3, 'Bob Wilson', 'bob@example.com'],
        ]
    );
    
    // Table from collection
    $users = User::all();
    $this->table(
        ['ID', 'Name', 'Email', 'Created'],
        $users->map(fn($user) => [
            $user->id,
            $user->name,
            $user->email,
            $user->created_at->format('Y-m-d'),
        ])
    );
    
    return Command::SUCCESS;
}
```

**Example Output:**

```
+----+-------------+-------------------+------------+
| ID | Name        | Email             | Created    |
+----+-------------+-------------------+------------+
| 1  | John Doe    | john@example.com  | 2024-01-01 |
| 2  | Jane Smith  | jane@example.com  | 2024-01-02 |
| 3  | Bob Wilson  | bob@example.com   | 2024-01-03 |
+----+-------------+-------------------+------------+
```

### Lists and Sections

```php
public function handle(): int
{
    // Horizontal rule
    $this->line('---');
    
    // Title section
    $this->title('System Status Report');
    
    // Section
    $this->section('Database Statistics');
    
    // Bullet list
    $this->listing([
        'Total Users: 1,234',
        'Active Sessions: 56',
        'Pending Jobs: 12',
    ]);
    
    // Definition list
    $this->definitionList(
        'Server' => 'production-01',
        'Memory Usage' => '2.5 GB',
        'CPU Usage' => '45%',
        'Uptime' => '30 days'
    );
    
    return Command::SUCCESS;
}
```

## ✅ Validation

### Input Validation

```php
<?php

namespace App\Console\Commands;

use Neo\Console\Command;
use Validator;

class UserCreateCommand extends Command
{
    protected $signature = 'user:create 
                            {name : User name}
                            {email : User email}
                            {--role=user : User role}';
    
    protected $description = 'Create a new user';

    public function handle(): int
    {
        // Validate arguments
        $validator = Validator::make([
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'role' => $this->option('role'),
        ], [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:user,admin,moderator',
        ]);
        
        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->line("  - {$error}");
            }
            return Command::FAILURE;
        }
        
        // Create user
        $user = User::create($validator->validated());
        
        $this->success("User created: {$user->name}");
        return Command::SUCCESS;
    }
}
```

### Custom Validation

```php
public function handle(): int
{
    $id = $this->argument('id');
    
    // Custom validation
    if (!is_numeric($id) || $id <= 0) {
        $this->error('ID must be a positive number');
        return Command::FAILURE;
    }
    
    // File existence validation
    $file = $this->argument('file');
    if (!file_exists($file)) {
        $this->error("File not found: {$file}");
        return Command::FAILURE;
    }
    
    // Database validation
    $user = User::find($id);
    if (!$user) {
        $this->error("User not found with ID: {$id}");
        return Command::FAILURE;
    }
    
    return Command::SUCCESS;
}
```

## 💬 Interactive Commands

### Asking Questions

```php
public function handle(): int
{
    // Simple question
    $name = $this->ask('What is your name?');
    
    // Question with default
    $role = $this->ask('What is your role?', 'user');
    
    // Secret input (hidden)
    $password = $this->secret('Enter password');
    
    // Confirmation
    if ($this->confirm('Do you want to continue?')) {
        // Proceed
    }
    
    // Confirmation with default yes
    if ($this->confirm('Delete all records?', false)) {
        // Delete logic
    }
    
    return Command::SUCCESS;
}
```

### Choice Selection

```php
public function handle(): int
{
    // Single choice
    $role = $this->choice(
        'Select a role',
        ['user', 'admin', 'moderator'],
        0 // Default index
    );
    
    // Multiple choice
    $permissions = $this->multiChoice(
        'Select permissions',
        ['read', 'write', 'delete', 'admin'],
        [0, 1] // Default indices
    );
    
    return Command::SUCCESS;
}
```

**Example: Interactive User Setup**

```php
<?php

namespace App\Console\Commands;

use Neo\Console\Command;
use App\Models\User;

class UserSetupCommand extends Command
{
    protected $signature = 'user:setup';
    protected $description = 'Interactive user setup wizard';

    public function handle(): int
    {
        $this->title('User Setup Wizard');
        
        // Gather information
        $name = $this->ask('Enter user name');
        $email = $this->ask('Enter email address');
        
        // Validate email
        while (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email format');
            $email = $this->ask('Enter email address');
        }
        
        $password = $this->secret('Enter password');
        $confirmPassword = $this->secret('Confirm password');
        
        if ($password !== $confirmPassword) {
            $this->error('Passwords do not match');
            return Command::FAILURE;
        }
        
        $role = $this->choice(
            'Select role',
            ['user', 'admin', 'moderator'],
            0
        );
        
        $permissions = $this->multiChoice(
            'Select permissions',
            ['read', 'write', 'delete', 'manage_users'],
            [0, 1]
        );
        
        // Confirmation
        $this->section('User Details');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $name],
                ['Email', $email],
                ['Role', $role],
                ['Permissions', implode(', ', $permissions)],
            ]
        );
        
        if (!$this->confirm('Create user with these details?')) {
            $this->info('User creation cancelled');
            return Command::SUCCESS;
        }
        
        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'role' => $role,
        ]);
        
        $user->givePermissions($permissions);
        
        $this->success("User created successfully! ID: {$user->id}");
        return Command::SUCCESS;
    }
}
```

## 📊 Progress Indicators

### Progress Bar

```php
public function handle(): int
{
    $users = User::all();
    
    // Create progress bar
    $bar = $this->output->createProgressBar(count($users));
    $bar->start();
    
    foreach ($users as $user) {
        // Process user
        $this->processUser($user);
        
        // Advance progress
        $bar->advance();
    }
    
    $bar->finish();
    $this->newLine();
    
    $this->success('All users processed!');
    return Command::SUCCESS;
}
```

### Progress Bar with Details

```php
public function handle(): int
{
    $items = $this->getItemsToProcess();
    
    $bar = $this->output->createProgressBar(count($items));
    $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
    $bar->setMessage('Starting...');
    $bar->start();
    
    foreach ($items as $item) {
        $bar->setMessage("Processing: {$item->name}");
        $this->processItem($item);
        $bar->advance();
        usleep(100000); // Simulate work
    }
    
    $bar->setMessage('Complete!');
    $bar->finish();
    $this->newLine(2);
    
    return Command::SUCCESS;
}
```

### Spinner

```php
public function handle(): int
{
    $this->info('Connecting to API...');
    
    // Show spinner while processing
    $this->withSpinner(function () {
        sleep(3); // Simulate API call
        return true;
    }, 'Fetching data');
    
    $this->success('Data fetched successfully!');
    return Command::SUCCESS;
}
```

## 🎯 Best Practices

### Command Organization

```
app/Console/Commands/
├── User/
│   ├── UserCreateCommand.php
│   ├── UserDeleteCommand.php
│   └── UserListCommand.php
├── Email/
│   ├── EmailSendCommand.php
│   └── EmailQueueCommand.php
└── Report/
    ├── ReportGenerateCommand.php
    └── ReportScheduleCommand.php
```

### Error Handling

```php
public function handle(): int
{
    try {
        $this->info('Starting process...');
        
        $this->processData();
        
        $this->success('Process completed successfully!');
        return Command::SUCCESS;
        
    } catch (\InvalidArgumentException $e) {
        $this->error('Invalid argument: ' . $e->getMessage());
        return 2; // Custom exit code for invalid arguments
        
    } catch (\Exception $e) {
        $this->error('An error occurred: ' . $e->getMessage());
        
        if ($this->option('verbose')) {
            $this->line($e->getTraceAsString());
        }
        
        return Command::FAILURE;
    }
}
```

### Logging

```php
use Neo\Support\Facades\Log;

public function handle(): int
{
    Log::info('Command started', [
        'command' => $this->signature,
        'arguments' => $this->arguments(),
        'options' => $this->options(),
    ]);
    
    try {
        $this->processData();
        
        Log::info('Command completed successfully');
        return Command::SUCCESS;
        
    } catch (\Exception $e) {
        Log::error('Command failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        return Command::FAILURE;
    }
}
```

### Testing Commands

```php
<?php

namespace Tests\Feature\Console;

use Tests\TestCase;
use Neo\Foundation\Testing\RefreshDatabase;

class UserDeleteCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_user_successfully()
    {
        $user = User::factory()->create();
        
        $this->artisan('user:delete', ['id' => $user->id])
            ->expectsQuestion('Are you sure you want to delete', true)
            ->expectsOutput('User deleted successfully')
            ->assertExitCode(0);
        
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
    
    public function test_fails_when_user_not_found()
    {
        $this->artisan('user:delete', ['id' => 999])
            ->expectsOutput('User with ID 999 not found')
            ->assertExitCode(1);
    }
    
    public function test_cancels_deletion_when_not_confirmed()
    {
        $user = User::factory()->create();
        
        $this->artisan('user:delete', ['id' => $user->id])
            ->expectsQuestion('Are you sure you want to delete', false)
            ->expectsOutput('Operation cancelled')
            ->assertExitCode(0);
        
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
```

## 📚 Complete Example

**Comprehensive Data Import Command**

```php
<?php

namespace App\Console\Commands;

use Neo\Console\Command;
use App\Services\ImportService;
use Validator;

class DataImportCommand extends Command
{
    protected $signature = 'data:import 
                            {file : CSV file path}
                            {--type=users : Data type to import}
                            {--batch=100 : Batch size}
                            {--validate : Validate data before import}
                            {--dry-run : Preview import without saving}';
    
    protected $description = 'Import data from CSV file';
    
    protected ImportService $importService;

    public function __construct(ImportService $importService)
    {
        parent::__construct();
        $this->importService = $importService;
    }

    public function handle(): int
    {
        $this->title('Data Import Tool');
        
        // Validate file
        $file = $this->argument('file');
        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return Command::FAILURE;
        }
        
        // Show import details
        $this->section('Import Configuration');
        $this->table(
            ['Setting', 'Value'],
            [
                ['File', $file],
                ['Type', $this->option('type')],
                ['Batch Size', $this->option('batch')],
                ['Validate', $this->option('validate') ? 'Yes' : 'No'],
                ['Dry Run', $this->option('dry-run') ? 'Yes' : 'No'],
            ]
        );
        
        // Confirm
        if (!$this->confirm('Proceed with import?', true)) {
            $this->info('Import cancelled');
            return Command::SUCCESS;
        }
        
        try {
            // Read file
            $this->info('Reading file...');
            $data = $this->importService->readCsv($file);
            $this->info("Found {$data->count()} records");
            
            // Validate if requested
            if ($this->option('validate')) {
                $this->info('Validating data...');
                $errors = $this->validateData($data);
                
                if ($errors->isNotEmpty()) {
                    $this->error("Validation failed with {$errors->count()} errors");
                    $this->displayErrors($errors);
                    return Command::FAILURE;
                }
                
                $this->success('Validation passed!');
            }
            
            // Dry run
            if ($this->option('dry-run')) {
                $this->warn('DRY RUN - No data will be saved');
                $this->displayPreview($data->take(5));
                return Command::SUCCESS;
            }
            
            // Import data
            $this->info('Importing data...');
            $bar = $this->output->createProgressBar($data->count());
            $bar->start();
            
            $imported = 0;
            $failed = 0;
            
            foreach ($data->chunk($this->option('batch')) as $batch) {
                foreach ($batch as $record) {
                    try {
                        $this->importService->import($record, $this->option('type'));
                        $imported++;
                    } catch (\Exception $e) {
                        $failed++;
                    }
                    $bar->advance();
                }
            }
            
            $bar->finish();
            $this->newLine(2);
            
            // Summary
            $this->section('Import Summary');
            $this->table(
                ['Status', 'Count'],
                [
                    ['Successful', $imported],
                    ['Failed', $failed],
                    ['Total', $data->count()],
                ]
            );
            
            if ($failed > 0) {
                $this->warn("Import completed with {$failed} failures");
                return Command::FAILURE;
            }
            
            $this->success('Import completed successfully!');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    private function validateData($data)
    {
        $errors = collect();
        
        foreach ($data as $index => $record) {
            $validator = Validator::make($record, $this->getValidationRules());
            
            if ($validator->fails()) {
                $errors->push([
                    'row' => $index + 1,
                    'errors' => $validator->errors()->all(),
                ]);
            }
        }
        
        return $errors;
    }
    
    private function getValidationRules(): array
    {
        return match ($this->option('type')) {
            'users' => [
                'name' => 'required|string',
                'email' => 'required|email',
            ],
            'products' => [
                'name' => 'required|string',
                'price' => 'required|numeric',
            ],
            default => [],
        };
    }
    
    private function displayErrors($errors): void
    {
        foreach ($errors->take(10) as $error) {
            $this->error("Row {$error['row']}:");
            foreach ($error['errors'] as $message) {
                $this->line("  - {$message}");
            }
        }
        
        if ($errors->count() > 10) {
            $this->line("... and " . ($errors->count() - 10) . " more errors");
        }
    }
    
    private function displayPreview($data): void
    {
        $this->section('Preview (first 5 records)');
        $headers = array_keys($data->first());
        $rows = $data->map(fn($record) => array_values($record))->toArray();
        $this->table($headers, $rows);
    }
}
```

## 📚 Related Documentation

- [Introduction](introduction.md) - CLI tools overview
- [Database Commands](database-commands.md) - Database CLI commands
- [Generators](generators/) - Code generation commands
- [Scheduling](../advanced/scheduling.md) - Command scheduling

## 🔗 Quick Reference

```bash
# Create command
php neo make:command CommandName

# Run command
php neo command:name

# List all commands
php neo list

# Get help
php neo help command:name
```

**Common Patterns:**

```php
// Output
$this->info('Info message');
$this->error('Error message');
$this->warn('Warning message');
$this->success('Success message');

// Input
$arg = $this->argument('name');
$opt = $this->option('flag');

// Interactive
$answer = $this->ask('Question?');
$confirmed = $this->confirm('Continue?');
$choice = $this->choice('Select', ['a', 'b']);

// Progress
$bar = $this->output->createProgressBar($count);
$bar->advance();
$bar->finish();

// Table
$this->table($headers, $rows);
```
