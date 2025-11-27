# Task Scheduling

## Introduction

NeoFramework's command scheduler offers a fresh approach to managing scheduled tasks on your server. The scheduler allows you to fluently and expressively define your command schedule within your NeoFramework application itself.

## Defining Schedules

All scheduled tasks are defined in the `app/Console/Kernel.php` file's `schedule` method:

```php
<?php

namespace App\Console;

use NeoPhp\Console\Scheduling\Schedule;
use NeoPhp\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('emails:send')->daily();
        $schedule->command('backup:create')->dailyAt('02:00');
        $schedule->call(function () {
            // Clean up old records
        })->weekly();
    }
}
```

## Starting the Scheduler

Add a single cron entry to your server:

```bash
* * * * * cd /path-to-your-project && php neo schedule:run >> /dev/null 2>&1
```

This cron will call the scheduler every minute, which will evaluate your scheduled tasks and run the tasks that are due.

## Schedule Frequency Options

### Common Frequencies

```php
->cron('* * * * *');        // Custom cron schedule
->everyMinute();             // Every minute
->everyTwoMinutes();         // Every two minutes
->everyFiveMinutes();        // Every five minutes
->everyTenMinutes();         // Every ten minutes
->everyFifteenMinutes();     // Every fifteen minutes
->everyThirtyMinutes();      // Every thirty minutes
->hourly();                  // Every hour
->hourlyAt(17);              // Every hour at 17 minutes past
->everyTwoHours();           // Every two hours
->daily();                   // Daily at midnight
->dailyAt('13:00');          // Daily at 1:00 PM
->twiceDaily(1, 13);         // Daily at 1:00 AM and 1:00 PM
->weekly();                  // Weekly on Sunday at midnight
->weeklyOn(1, '8:00');       // Weekly on Monday at 8:00 AM
->monthly();                 // Monthly on the 1st at midnight
->monthlyOn(4, '15:00');     // Monthly on the 4th at 3:00 PM
->quarterly();               // Quarterly on the 1st at midnight
->yearly();                  // Yearly on Jan 1st at midnight
```

### Day Constraints

```php
->weekdays();                // Monday through Friday
->weekends();                // Saturday and Sunday
->sundays();                 // Every Sunday
->mondays();                 // Every Monday
->tuesdays();                // Every Tuesday
->wednesdays();              // Every Wednesday
->thursdays();               // Every Thursday
->fridays();                 // Every Friday
->saturdays();               // Every Saturday
->days([0, 3]);              // Every Sunday and Wednesday
```

### Time Constraints

```php
->between('7:00', '22:00');  // Between 7 AM and 10 PM
->unlessBetween('23:00', '4:00'); // Unless between 11 PM and 4 AM
```

### Environment Constraints

```php
->when(function () {
    return true; // Run when this returns true
});

->skip(function () {
    return false; // Skip when this returns true
});

->environments(['production']); // Only in production
```

## Scheduling Artisan Commands

### Basic Command Scheduling

```php
$schedule->command('emails:send')->daily();

// Using command class
$schedule->command(SendEmails::class)->daily();

// With parameters
$schedule->command('emails:send --force')->daily();
$schedule->command('emails:send', ['--force'])->daily();
```

### Running Commands in Background

```php
$schedule->command('emails:send')->daily()->runInBackground();
```

### Running on One Server

```php
$schedule->command('report:generate')
    ->daily()
    ->onOneServer();
```

## Scheduling Closures

```php
$schedule->call(function () {
    DB::table('recent_users')->delete();
})->daily();
```

## Scheduling Shell Commands

```php
$schedule->exec('node /home/forge/script.js')->daily();
```

## Task Output

### Sending Output to File

```php
$schedule->command('emails:send')
    ->daily()
    ->sendOutputTo('/path/to/file.log');

// Append to file
$schedule->command('emails:send')
    ->daily()
    ->appendOutputTo('/path/to/file.log');
```

### Email Output

```php
$schedule->command('report:generate')
    ->daily()
    ->sendOutputTo('/path/to/report.log')
    ->emailOutputTo('admin@example.com');

// Email only on failure
$schedule->command('report:generate')
    ->daily()
    ->emailOutputOnFailure('admin@example.com');
```

## Task Hooks

### Before & After Callbacks

```php
$schedule->command('emails:send')
    ->daily()
    ->before(function () {
        // Task is about to execute
        Log::info('Starting email send');
    })
    ->after(function () {
        // Task is complete
        Log::info('Email send completed');
    });
```

### Success & Failure Callbacks

```php
$schedule->command('emails:send')
    ->daily()
    ->onSuccess(function () {
        // Task succeeded
        Log::info('Emails sent successfully');
    })
    ->onFailure(function () {
        // Task failed
        Log::error('Email send failed');
        Notification::send($admins, new TaskFailed('emails:send'));
    });
```

## Preventing Task Overlaps

```php
$schedule->command('emails:send')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// With custom expiration time (in minutes)
$schedule->command('emails:send')
    ->everyFiveMinutes()
    ->withoutOverlapping(10);
```

## Running Tasks on One Server

```php
$schedule->command('report:generate')
    ->daily()
    ->onOneServer();
```

## Maintenance Mode

### Skip During Maintenance

```php
$schedule->command('emails:send')
    ->daily()
    ->evenInMaintenanceMode();
```

## Practical Examples

### Example 1: Daily Backup System

```php
<?php

namespace App\Console;

use NeoPhp\Console\Scheduling\Schedule;
use NeoPhp\Foundation\Console\Kernel as ConsoleKernel;
use NeoPhp\Support\Facades\Notification;
use App\Notifications\BackupCompleted;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Daily database backup at 2 AM
        $schedule->command('backup:database')
            ->dailyAt('02:00')
            ->onOneServer()
            ->withoutOverlapping()
            ->sendOutputTo(storage_path('logs/backup.log'))
            ->onSuccess(function () {
                Log::info('Database backup completed successfully');
            })
            ->onFailure(function () {
                $admins = User::where('is_admin', true)->get();
                Notification::send($admins, new BackupFailed('database'));
            });

        // Weekly full backup on Sunday at 3 AM
        $schedule->command('backup:full')
            ->weeklyOn(0, '03:00')
            ->onOneServer()
            ->withoutOverlapping(120) // 2 hour timeout
            ->appendOutputTo(storage_path('logs/backup-full.log'))
            ->onSuccess(function () {
                $admins = User::where('is_admin', true)->get();
                Notification::send($admins, new BackupCompleted('full'));
            });

        // Clean old backups daily at 4 AM
        $schedule->command('backup:clean --days=30')
            ->dailyAt('04:00')
            ->onOneServer();
    }
}
```

### Example 2: Data Processing Pipeline

```php
<?php

protected function schedule(Schedule $schedule)
{
    // Import data from external API every hour
    $schedule->command('data:import')
        ->hourly()
        ->between('6:00', '22:00')
        ->onOneServer()
        ->withoutOverlapping(30)
        ->before(function () {
            Cache::put('import_status', 'running', 3600);
        })
        ->after(function () {
            Cache::forget('import_status');
        });

    // Process imported data every 30 minutes
    $schedule->command('data:process')
        ->everyThirtyMinutes()
        ->weekdays()
        ->onOneServer()
        ->withoutOverlapping()
        ->when(function () {
            return DB::table('pending_imports')->count() > 0;
        });

    // Generate reports daily at 7 AM
    $schedule->command('reports:daily')
        ->dailyAt('07:00')
        ->onOneServer()
        ->environments(['production'])
        ->onSuccess(function () {
            event(new DailyReportGenerated());
        });

    // Send weekly summary every Monday at 9 AM
    $schedule->command('reports:weekly')
        ->weeklyOn(1, '09:00')
        ->onOneServer()
        ->sendOutputTo(storage_path('logs/weekly-reports.log'));

    // Clean processed data monthly
    $schedule->call(function () {
        DB::table('processed_data')
            ->where('created_at', '<', now()->subMonths(3))
            ->delete();
        
        Log::info('Cleaned old processed data');
    })->monthly();
}
```

### Example 3: User Engagement System

```php
<?php

protected function schedule(Schedule $schedule)
{
    // Send daily digest emails at 8 AM
    $schedule->command('emails:daily-digest')
        ->dailyAt('08:00')
        ->onOneServer()
        ->runInBackground()
        ->onSuccess(function () {
            Log::info('Daily digest emails queued', [
                'count' => Cache::get('digest_count', 0),
            ]);
        });

    // Check for inactive users every day at 10 AM
    $schedule->call(function () {
        $inactiveUsers = User::where('last_login_at', '<', now()->subDays(30))
            ->where('notified_inactive', false)
            ->get();

        foreach ($inactiveUsers as $user) {
            $user->notify(new InactivityReminder());
            $user->update(['notified_inactive' => true]);
        }

        Log::info('Sent inactivity reminders', ['count' => $inactiveUsers->count()]);
    })->dailyAt('10:00');

    // Process subscription renewals every 6 hours
    $schedule->command('subscriptions:process-renewals')
        ->everySixHours()
        ->onOneServer()
        ->withoutOverlapping()
        ->emailOutputOnFailure('admin@example.com');

    // Send birthday wishes daily at 9 AM
    $schedule->call(function () {
        $users = User::whereRaw('DATE_FORMAT(birthday, "%m-%d") = ?', [
            now()->format('m-d')
        ])->get();

        foreach ($users as $user) {
            $user->notify(new BirthdayWish());
        }
    })->dailyAt('09:00');

    // Clean expired sessions daily at midnight
    $schedule->command('session:gc')
        ->daily()
        ->onOneServer();
}
```

### Example 4: E-commerce Maintenance

```php
<?php

protected function schedule(Schedule $schedule)
{
    // Update product prices from supplier API every 2 hours
    $schedule->command('products:sync-prices')
        ->everyTwoHours()
        ->between('6:00', '23:00')
        ->weekdays()
        ->onOneServer()
        ->withoutOverlapping()
        ->onFailure(function () {
            Log::error('Failed to sync product prices');
        });

    // Check low stock alerts every hour during business hours
    $schedule->call(function () {
        $lowStockProducts = Product::where('stock', '<', 10)
            ->where('active', true)
            ->get();

        if ($lowStockProducts->count() > 0) {
            $admins = User::where('is_admin', true)->get();
            Notification::send($admins, new LowStockAlert($lowStockProducts));
        }
    })->hourly()->between('9:00', '18:00')->weekdays();

    // Process abandoned carts - send reminder after 1 hour
    $schedule->command('carts:send-reminders')
        ->hourly()
        ->onOneServer()
        ->runInBackground();

    // Archive old orders monthly
    $schedule->call(function () {
        $archivedCount = Order::where('created_at', '<', now()->subYear())
            ->where('archived', false)
            ->update(['archived' => true]);

        Log::info('Archived old orders', ['count' => $archivedCount]);
    })->monthly();

    // Generate sales reports daily at 6 AM
    $schedule->command('reports:sales')
        ->dailyAt('06:00')
        ->onOneServer()
        ->sendOutputTo(storage_path('logs/sales-reports.log'));

    // Send weekly inventory report every Friday at 5 PM
    $schedule->command('reports:inventory')
        ->weeklyOn(5, '17:00')
        ->emailOutputTo('inventory@example.com');

    // Clean up expired coupons daily
    $schedule->call(function () {
        Coupon::where('expires_at', '<', now())
            ->where('active', true)
            ->update(['active' => false]);
    })->daily();
}
```

### Example 5: System Maintenance

```php
<?php

protected function schedule(Schedule $schedule)
{
    // Clear cache daily at 3 AM
    $schedule->command('cache:clear')
        ->dailyAt('03:00')
        ->onOneServer()
        ->environments(['production']);

    // Optimize database tables weekly
    $schedule->command('db:optimize')
        ->weeklyOn(0, '04:00')
        ->onOneServer()
        ->sendOutputTo(storage_path('logs/db-optimize.log'));

    // Clean old log files monthly
    $schedule->call(function () {
        $files = Storage::files('logs');
        $oneMonthAgo = now()->subMonth();

        foreach ($files as $file) {
            if (Storage::lastModified($file) < $oneMonthAgo->timestamp) {
                Storage::delete($file);
            }
        }

        Log::info('Cleaned old log files');
    })->monthly();

    // Monitor disk space every 30 minutes
    $schedule->call(function () {
        $freeSpace = disk_free_space('/');
        $totalSpace = disk_total_space('/');
        $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;

        if ($usagePercent > 90) {
            $admins = User::where('is_admin', true)->get();
            Notification::send($admins, new DiskSpaceAlert($usagePercent));
        }

        Log::info('Disk usage', ['percent' => round($usagePercent, 2)]);
    })->everyThirtyMinutes();

    // Check for application updates daily
    $schedule->command('app:check-updates')
        ->daily()
        ->onOneServer();

    // Generate sitemap daily at 5 AM
    $schedule->command('sitemap:generate')
        ->dailyAt('05:00')
        ->onOneServer();

    // Prune old notifications
    $schedule->call(function () {
        DB::table('notifications')
            ->where('created_at', '<', now()->subDays(90))
            ->where('read_at', '!=', null)
            ->delete();
    })->weekly();
}
```

## Testing Scheduled Tasks

### Manual Testing

```bash
# Run the scheduler manually
php neo schedule:run

# List all scheduled tasks
php neo schedule:list
```

### Testing in Code

```php
public function test_scheduled_tasks()
{
    $schedule = app()->make(Schedule::class);

    // Get scheduled events
    $events = $schedule->events();

    // Assert specific command is scheduled
    $this->assertTrue(
        collect($events)->contains(function ($event) {
            return str_contains($event->command, 'emails:send');
        })
    );
}
```

## Best Practices

### 1. Use `onOneServer()` for Critical Tasks

```php
$schedule->command('backup:database')
    ->daily()
    ->onOneServer();
```

### 2. Prevent Overlaps

```php
$schedule->command('process:data')
    ->everyFiveMinutes()
    ->withoutOverlapping();
```

### 3. Monitor Task Failures

```php
$schedule->command('important:task')
    ->daily()
    ->onFailure(function () {
        Notification::send($admins, new TaskFailed());
    });
```

### 4. Log Important Tasks

```php
$schedule->command('backup:database')
    ->daily()
    ->sendOutputTo(storage_path('logs/backup.log'))
    ->emailOutputOnFailure('admin@example.com');
```

### 5. Use Appropriate Timeouts

```php
$schedule->command('long:task')
    ->daily()
    ->withoutOverlapping(120); // 2 hour timeout
```

## Next Steps

- [Queue](queue.md) - Background job processing
- [Logging](logging.md) - Log scheduled tasks
- [Notifications](notifications.md) - Notify on task completion
- [Console Commands](../cli-tools/introduction.md) - Create custom commands
