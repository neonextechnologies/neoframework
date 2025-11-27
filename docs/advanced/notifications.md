# Notifications

## Introduction

In addition to sending emails, NeoFramework provides support for sending notifications across a variety of delivery channels, including mail, SMS, and Slack. Notifications may also be stored in a database so they may be displayed in your web interface.

## Creating Notifications

### Generate Notification

```bash
php neo make:notification InvoicePaid
php neo make:notification Orders/OrderShipped
```

This creates `app/Notifications/InvoicePaid.php`:

```php
<?php

namespace App\Notifications;

use NeoPhp\Notifications\Notification;
use NeoPhp\Notifications\Messages\MailMessage;

class InvoicePaid extends Notification
{
    protected $invoice;

    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Invoice Paid')
            ->line('Your invoice has been paid!')
            ->action('View Invoice', url('/invoices/'.$this->invoice->id))
            ->line('Thank you for your business!');
    }

    public function toArray($notifiable)
    {
        return [
            'invoice_id' => $this->invoice->id,
            'amount' => $this->invoice->amount,
        ];
    }
}
```

## Sending Notifications

### Using Notifiable Trait

Add `Notifiable` trait to your model:

```php
<?php

namespace App\Models;

use NeoPhp\Database\Eloquent\Model;
use NeoPhp\Notifications\Notifiable;

class User extends Model
{
    use Notifiable;
}
```

### Send Notification

```php
use App\Notifications\InvoicePaid;

$user->notify(new InvoicePaid($invoice));
```

### Using Notification Facade

```php
use NeoPhp\Support\Facades\Notification;

Notification::send($users, new InvoicePaid($invoice));
```

## Mail Notifications

### Formatting Mail Messages

```php
public function toMail($notifiable)
{
    return (new MailMessage)
        ->subject('Order Shipped')
        ->greeting('Hello!')
        ->line('Your order has been shipped.')
        ->action('Track Shipment', $url)
        ->line('Thank you for shopping with us!');
}
```

### Customizing Recipient

```php
public function toMail($notifiable)
{
    return (new MailMessage)
        ->to($notifiable->email, $notifiable->name)
        ->subject('Welcome!')
        ->line('Welcome to our platform!');
}
```

### Adding Attachments

```php
public function toMail($notifiable)
{
    return (new MailMessage)
        ->subject('Invoice')
        ->line('Your invoice is attached.')
        ->attach('/path/to/invoice.pdf')
        ->attachData($pdfData, 'invoice.pdf', [
            'mime' => 'application/pdf',
        ]);
}
```

## Database Notifications

### Creating Notifications Table

```bash
php neo make:migration create_notifications_table
```

```php
Schema::create('notifications', function ($table) {
    $table->uuid('id')->primary();
    $table->string('type');
    $table->morphs('notifiable');
    $table->text('data');
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
});
```

### Accessing Notifications

```php
$user = User::find(1);

// Get all notifications
foreach ($user->notifications as $notification) {
    echo $notification->type;
}

// Get unread notifications
foreach ($user->unreadNotifications as $notification) {
    echo $notification->data['message'];
}
```

### Marking as Read

```php
// Mark specific notification as read
$user->unreadNotifications
    ->where('id', $notificationId)
    ->markAsRead();

// Mark all as read
$user->unreadNotifications->markAsRead();

// Mark as read when accessing
$notification = $user->notifications()->find($id);
$notification->markAsRead();
```

## Broadcast Notifications

### Broadcasting Configuration

In `config/broadcasting.php`:

```php
'connections' => [
    'pusher' => [
        'driver' => 'pusher',
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'app_id' => env('PUSHER_APP_ID'),
        'options' => [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'encrypted' => true,
        ],
    ],
],
```

### Broadcast Notification

```php
use NeoPhp\Notifications\Messages\BroadcastMessage;

public function toBroadcast($notifiable)
{
    return new BroadcastMessage([
        'message' => 'New order received',
        'order_id' => $this->order->id,
    ]);
}
```

## SMS Notifications

### Nexmo/Vonage Channel

```php
use NeoPhp\Notifications\Messages\NexmoMessage;

public function toNexmo($notifiable)
{
    return (new NexmoMessage)
        ->content('Your order has been shipped!');
}
```

### Routing SMS Notifications

```php
public function routeNotificationForNexmo($notification)
{
    return $this->phone_number;
}
```

## Slack Notifications

### Slack Configuration

```php
public function via($notifiable)
{
    return ['slack'];
}

public function toSlack($notifiable)
{
    return (new SlackMessage)
        ->content('New order received!')
        ->attachment(function ($attachment) use ($order) {
            $attachment->title('Order #'.$order->id)
                ->fields([
                    'Customer' => $order->customer->name,
                    'Total' => '$'.$order->total,
                ]);
        });
}
```

### Routing Slack Notifications

```php
public function routeNotificationForSlack($notification)
{
    return config('services.slack.webhook_url');
}
```

## Queueing Notifications

### Using ShouldQueue

```php
use NeoPhp\Contracts\Queue\ShouldQueue;

class InvoicePaid extends Notification implements ShouldQueue
{
    //
}
```

### Delaying Notifications

```php
$delay = now()->addMinutes(10);

$user->notify((new InvoicePaid($invoice))->delay($delay));
```

## Practical Examples

### Example 1: Order Notification System

```php
<?php

// Notifications
namespace App\Notifications;

use App\Models\Order;
use NeoPhp\Notifications\Notification;
use NeoPhp\Notifications\Messages\MailMessage;
use NeoPhp\Contracts\Queue\ShouldQueue;

class OrderPlaced extends Notification implements ShouldQueue
{
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Order Confirmation #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for your order.')
            ->line('Order Total: $' . number_format($this->order->total, 2))
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('We will send you a notification when your order ships.');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'total' => $this->order->total,
            'status' => $this->order->status,
            'message' => 'Your order has been placed successfully',
        ];
    }
}

class OrderShipped extends Notification implements ShouldQueue
{
    protected $order;
    protected $trackingNumber;

    public function __construct(Order $order, $trackingNumber)
    {
        $this->order = $order;
        $this->trackingNumber = $trackingNumber;
    }

    public function via($notifiable)
    {
        $channels = ['mail', 'database'];

        if ($notifiable->notify_sms) {
            $channels[] = 'nexmo';
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Order Has Shipped!')
            ->greeting('Great news!')
            ->line('Your order #' . $this->order->id . ' has been shipped.')
            ->line('Tracking Number: ' . $this->trackingNumber)
            ->action('Track Shipment', url('/track/' . $this->trackingNumber))
            ->line('Estimated delivery: 3-5 business days');
    }

    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
            ->content("Your order #{$this->order->id} has shipped! Tracking: {$this->trackingNumber}");
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'tracking_number' => $this->trackingNumber,
            'message' => 'Your order has been shipped',
        ];
    }
}

// Usage in controller
class OrderController extends Controller
{
    public function store(Request $request)
    {
        $order = Order::create([
            'user_id' => auth()->id(),
            'total' => $request->total,
        ]);

        // Send notification
        auth()->user()->notify(new OrderPlaced($order));

        return redirect()->route('orders.show', $order);
    }

    public function ship(Order $order, Request $request)
    {
        $trackingNumber = $request->tracking_number;

        $order->update([
            'status' => 'shipped',
            'tracking_number' => $trackingNumber,
        ]);

        // Notify customer
        $order->user->notify(new OrderShipped($order, $trackingNumber));

        return back()->with('success', 'Order shipped and customer notified');
    }
}
```

### Example 2: Real-time Notification System

```php
<?php

namespace App\Http\Controllers;

use NeoPhp\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function unread()
    {
        $notifications = auth()->user()
            ->unreadNotifications()
            ->get();

        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read');
    }

    public function delete($id)
    {
        auth()->user()
            ->notifications()
            ->findOrFail($id)
            ->delete();

        return back()->with('success', 'Notification deleted');
    }

    public function deleteAll()
    {
        auth()->user()->notifications()->delete();

        return back()->with('success', 'All notifications deleted');
    }
}

// View component
@if(auth()->user()->unreadNotifications->count())
<div class="notifications-dropdown">
    <button class="notification-bell">
        <i class="icon-bell"></i>
        <span class="badge">{{ auth()->user()->unreadNotifications->count() }}</span>
    </button>
    <div class="dropdown-menu">
        @foreach(auth()->user()->unreadNotifications->take(5) as $notification)
        <div class="notification-item">
            <a href="{{ route('notifications.read', $notification->id) }}">
                {{ $notification->data['message'] }}
            </a>
            <small>{{ $notification->created_at->diffForHumans() }}</small>
        </div>
        @endforeach
        <a href="{{ route('notifications.index') }}" class="view-all">View All</a>
    </div>
</div>
@endif
```

### Example 3: Team Collaboration Notifications

```php
<?php

namespace App\Notifications;

use App\Models\Task;
use NeoPhp\Notifications\Notification;
use NeoPhp\Notifications\Messages\MailMessage;
use NeoPhp\Notifications\Messages\SlackMessage;

class TaskAssigned extends Notification
{
    protected $task;
    protected $assignedBy;

    public function __construct(Task $task, $assignedBy)
    {
        $this->task = $task;
        $this->assignedBy = $assignedBy;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'slack'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Task Assigned: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->assignedBy->name . ' has assigned you a task.')
            ->line('Task: ' . $this->task->title)
            ->line('Description: ' . $this->task->description)
            ->line('Due Date: ' . $this->task->due_date->format('M d, Y'))
            ->action('View Task', url('/tasks/' . $this->task->id));
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->content('New task assigned!')
            ->attachment(function ($attachment) {
                $attachment
                    ->title($this->task->title, url('/tasks/' . $this->task->id))
                    ->fields([
                        'Assigned To' => $this->task->assignee->name,
                        'Assigned By' => $this->assignedBy->name,
                        'Due Date' => $this->task->due_date->format('M d, Y'),
                        'Priority' => $this->task->priority,
                    ]);
            });
    }

    public function toArray($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'assigned_by' => $this->assignedBy->name,
            'message' => "{$this->assignedBy->name} assigned you a task: {$this->task->title}",
        ];
    }
}

class TaskCompleted extends Notification
{
    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Task Completed: ' . $this->task->title)
            ->line($this->task->assignee->name . ' has completed a task.')
            ->action('View Task', url('/tasks/' . $this->task->id));
    }

    public function toArray($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'completed_by' => $this->task->assignee->name,
            'message' => "Task completed: {$this->task->title}",
        ];
    }
}

// Usage in task management
class TaskController extends Controller
{
    public function assign(Request $request, Task $task)
    {
        $assignee = User::findOrFail($request->assignee_id);

        $task->update(['assigned_to' => $assignee->id]);

        // Notify assignee
        $assignee->notify(new TaskAssigned($task, auth()->user()));

        return back()->with('success', 'Task assigned successfully');
    }

    public function complete(Task $task)
    {
        $task->update(['status' => 'completed', 'completed_at' => now()]);

        // Notify task creator
        $task->creator->notify(new TaskCompleted($task));

        return back()->with('success', 'Task marked as completed');
    }
}
```

## Best Practices

### 1. Queue Heavy Notifications

```php
class OrderPlaced extends Notification implements ShouldQueue
{
    public $tries = 3;
    public $timeout = 120;
}
```

### 2. Use Multiple Channels Wisely

```php
public function via($notifiable)
{
    $channels = ['database'];

    if ($notifiable->email_notifications) {
        $channels[] = 'mail';
    }

    if ($notifiable->sms_notifications) {
        $channels[] = 'nexmo';
    }

    return $channels;
}
```

### 3. Keep Notifications Concise

```php
public function toMail($notifiable)
{
    return (new MailMessage)
        ->subject('Order Shipped')
        ->line('Your order has shipped.')
        ->action('Track Order', $url);
}
```

### 4. Handle Failures

```php
class OrderPlaced extends Notification implements ShouldQueue
{
    public function failed(\Exception $exception)
    {
        Log::error('Failed to send notification', [
            'notification' => static::class,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

### 5. Clean Up Old Notifications

```php
// In scheduled task
$schedule->call(function () {
    DB::table('notifications')
        ->where('created_at', '<', now()->subDays(30))
        ->delete();
})->daily();
```

## Testing

### Notification Fake

```php
use NeoPhp\Support\Facades\Notification;

public function test_order_placed_notification()
{
    Notification::fake();

    // Perform action
    $order = $this->placeOrder();

    // Assert notification was sent
    Notification::assertSentTo(
        $order->user,
        OrderPlaced::class,
        function ($notification) use ($order) {
            return $notification->order->id === $order->id;
        }
    );
}
```

## Next Steps

- [Mail](mail.md) - Email notifications
- [Queue](queue.md) - Queue notifications
- [Broadcasting](broadcasting.md) - Real-time notifications
- [Events](events.md) - Trigger notifications from events
