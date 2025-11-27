# Events

## Introduction

NeoFramework's event system provides a simple observer pattern implementation, allowing you to subscribe and listen for various events in your application. Events serve as a great way to decouple various aspects of your application, since a single event can have multiple listeners that do not depend on each other.

## Configuration

Event service providers are registered in `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\EventServiceProvider::class,
],
```

## Registering Events & Listeners

### Event Service Provider

Register events and listeners in `app/Providers/EventServiceProvider.php`:

```php
<?php

namespace App\Providers;

use NeoPhp\Events\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        'App\Events\OrderPlaced' => [
            'App\Listeners\SendOrderConfirmation',
            'App\Listeners\UpdateInventory',
            'App\Listeners\NotifyAdministrator',
        ],

        'App\Events\UserRegistered' => [
            'App\Listeners\SendWelcomeEmail',
        ],
    ];
}
```

## Generating Events & Listeners

### Generate Event

```bash
php neo make:event OrderPlaced
php neo make:event User/UserRegistered
```

This creates `app/Events/OrderPlaced.php`:

```php
<?php

namespace App\Events;

use App\Models\Order;
use NeoPhp\Events\Event;

class OrderPlaced extends Event
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
```

### Generate Listener

```bash
php neo make:listener SendOrderConfirmation --event=OrderPlaced
```

This creates `app/Listeners/SendOrderConfirmation.php`:

```php
<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use NeoPhp\Support\Facades\Mail;
use App\Mail\OrderConfirmation;

class SendOrderConfirmation
{
    public function handle(OrderPlaced $event)
    {
        Mail::to($event->order->user->email)
            ->send(new OrderConfirmation($event->order));
    }
}
```

## Dispatching Events

### Using dispatch() Method

```php
use App\Events\OrderPlaced;

$order = Order::create([...]);

OrderPlaced::dispatch($order);
```

### Using Event Facade

```php
use NeoPhp\Support\Facades\Event;

Event::dispatch(new OrderPlaced($order));
```

### Using event() Helper

```php
event(new OrderPlaced($order));
```

## Event Subscribers

### Creating Subscribers

Event subscribers can subscribe to multiple events:

```bash
php neo make:subscriber UserEventSubscriber
```

```php
<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Events\UserLoggedIn;
use NeoPhp\Events\Dispatcher;

class UserEventSubscriber
{
    public function handleUserRegistration($event)
    {
        // Send welcome email
    }

    public function handleUserLogin($event)
    {
        // Log user activity
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            UserRegistered::class,
            [UserEventSubscriber::class, 'handleUserRegistration']
        );

        $events->listen(
            UserLoggedIn::class,
            [UserEventSubscriber::class, 'handleUserLogin']
        );
    }
}
```

Register in EventServiceProvider:

```php
protected $subscribe = [
    'App\Listeners\UserEventSubscriber',
];
```

## Queued Event Listeners

### Making Listeners Queued

Implement `ShouldQueue` interface:

```php
<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use NeoPhp\Contracts\Queue\ShouldQueue;
use NeoPhp\Support\Facades\Mail;

class SendOrderConfirmation implements ShouldQueue
{
    public $queue = 'emails';
    public $delay = 60; // seconds

    public function handle(OrderPlaced $event)
    {
        Mail::to($event->order->user->email)
            ->send(new OrderConfirmation($event->order));
    }
}
```

### Failed Queue Jobs

```php
class SendOrderConfirmation implements ShouldQueue
{
    public $tries = 3;
    public $timeout = 120;

    public function handle(OrderPlaced $event)
    {
        // Send email
    }

    public function failed(OrderPlaced $event, $exception)
    {
        // Handle the failed job
        Log::error('Failed to send order confirmation', [
            'order_id' => $event->order->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

## Event Discovery

### Automatic Discovery

NeoFramework can automatically discover events by scanning your listeners:

```php
class EventServiceProvider extends ServiceProvider
{
    public function shouldDiscoverEvents()
    {
        return true;
    }
}
```

This will automatically register all listeners in `app/Listeners`.

## Model Events

### Available Model Events

```php
creating, created
updating, updated
saving, saved
deleting, deleted
restoring, restored
retrieved
```

### Using Model Events

```php
<?php

namespace App\Models;

use NeoPhp\Database\Eloquent\Model;

class User extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->uuid = Str::uuid();
        });

        static::created(function ($user) {
            event(new UserRegistered($user));
        });

        static::updating(function ($user) {
            if ($user->isDirty('email')) {
                // Email is being changed
            }
        });

        static::deleted(function ($user) {
            // Clean up user data
        });
    }
}
```

## Observers

### Creating Observers

```bash
php neo make:observer UserObserver --model=User
```

```php
<?php

namespace App\Observers;

use App\Models\User;
use App\Events\UserRegistered;

class UserObserver
{
    public function created(User $user)
    {
        event(new UserRegistered($user));
    }

    public function updated(User $user)
    {
        if ($user->wasChanged('email')) {
            // Email changed
        }
    }

    public function deleted(User $user)
    {
        // Clean up related data
        $user->posts()->delete();
        $user->comments()->delete();
    }

    public function restored(User $user)
    {
        // Restore related data
    }
}
```

### Registering Observers

In `AppServiceProvider`:

```php
use App\Models\User;
use App\Observers\UserObserver;

public function boot()
{
    User::observe(UserObserver::class);
}
```

## Stopping Event Propagation

### Return False

```php
class SendOrderConfirmation
{
    public function handle(OrderPlaced $event)
    {
        if (!$event->order->user->verified) {
            return false; // Stop propagation
        }

        // Send email
    }
}
```

## Practical Examples

### Example 1: User Registration Flow

```php
<?php

// Event
namespace App\Events;

use App\Models\User;
use NeoPhp\Events\Event;

class UserRegistered extends Event
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}

// Listener 1: Send Welcome Email
namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\WelcomeEmail;
use NeoPhp\Support\Facades\Mail;
use NeoPhp\Contracts\Queue\ShouldQueue;

class SendWelcomeEmail implements ShouldQueue
{
    public function handle(UserRegistered $event)
    {
        Mail::to($event->user->email)
            ->send(new WelcomeEmail($event->user));
    }
}

// Listener 2: Create User Profile
namespace App\Listeners;

use App\Events\UserRegistered;
use App\Models\UserProfile;

class CreateUserProfile
{
    public function handle(UserRegistered $event)
    {
        UserProfile::create([
            'user_id' => $event->user->id,
            'bio' => '',
            'avatar' => 'default.png',
        ]);
    }
}

// Listener 3: Log Registration
namespace App\Listeners;

use App\Events\UserRegistered;
use NeoPhp\Support\Facades\Log;

class LogUserRegistration
{
    public function handle(UserRegistered $event)
    {
        Log::info('New user registered', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'ip' => request()->ip(),
        ]);
    }
}

// Listener 4: Assign Default Role
namespace App\Listeners;

use App\Events\UserRegistered;
use App\Models\Role;

class AssignDefaultRole
{
    public function handle(UserRegistered $event)
    {
        $defaultRole = Role::where('name', 'user')->first();
        $event->user->roles()->attach($defaultRole);
    }
}

// Register in EventServiceProvider
protected $listen = [
    UserRegistered::class => [
        SendWelcomeEmail::class,
        CreateUserProfile::class,
        LogUserRegistration::class,
        AssignDefaultRole::class,
    ],
];

// Dispatch in controller
class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        event(new UserRegistered($user));

        return redirect('/dashboard')->with('success', 'Welcome!');
    }
}
```

### Example 2: Order Processing System

```php
<?php

// Events
namespace App\Events;

use App\Models\Order;
use NeoPhp\Events\Event;

class OrderPlaced extends Event
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}

class OrderShipped extends Event
{
    public $order;
    public $trackingNumber;

    public function __construct(Order $order, $trackingNumber)
    {
        $this->order = $order;
        $this->trackingNumber = $trackingNumber;
    }
}

class OrderCancelled extends Event
{
    public $order;
    public $reason;

    public function __construct(Order $order, $reason)
    {
        $this->order = $order;
        $this->reason = $reason;
    }
}

// Listeners for OrderPlaced
namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Mail\OrderConfirmation;
use NeoPhp\Support\Facades\Mail;
use NeoPhp\Contracts\Queue\ShouldQueue;

class SendOrderConfirmation implements ShouldQueue
{
    public function handle(OrderPlaced $event)
    {
        Mail::to($event->order->user->email)
            ->send(new OrderConfirmation($event->order));
    }
}

class UpdateInventory
{
    public function handle(OrderPlaced $event)
    {
        foreach ($event->order->items as $item) {
            $product = $item->product;
            $product->decrement('stock', $item->quantity);

            if ($product->stock < 10) {
                event(new LowStockAlert($product));
            }
        }
    }
}

class CreateInvoice
{
    public function handle(OrderPlaced $event)
    {
        $invoice = Invoice::create([
            'order_id' => $event->order->id,
            'number' => 'INV-' . str_pad($event->order->id, 8, '0', STR_PAD_LEFT),
            'amount' => $event->order->total,
            'status' => 'pending',
        ]);

        // Generate PDF invoice
        $pdf = PDF::make($invoice);
        $pdf->save(storage_path("invoices/invoice-{$invoice->id}.pdf"));
    }
}

class NotifyAdministrator
{
    public function handle(OrderPlaced $event)
    {
        Mail::to(config('mail.admin'))
            ->send(new NewOrderNotification($event->order));
    }
}

// Listeners for OrderShipped
class SendShippingNotification implements ShouldQueue
{
    public function handle(OrderShipped $event)
    {
        Mail::to($event->order->user->email)
            ->send(new OrderShippedMail($event->order, $event->trackingNumber));
    }
}

class UpdateOrderStatus
{
    public function handle(OrderShipped $event)
    {
        $event->order->update([
            'status' => 'shipped',
            'tracking_number' => $event->trackingNumber,
            'shipped_at' => now(),
        ]);
    }
}

// Register events
protected $listen = [
    OrderPlaced::class => [
        SendOrderConfirmation::class,
        UpdateInventory::class,
        CreateInvoice::class,
        NotifyAdministrator::class,
    ],
    OrderShipped::class => [
        SendShippingNotification::class,
        UpdateOrderStatus::class,
    ],
    OrderCancelled::class => [
        RefundPayment::class,
        RestoreInventory::class,
        NotifyCustomer::class,
    ],
];

// Usage in controller
class OrderController extends Controller
{
    public function store(Request $request)
    {
        $order = Order::create([
            'user_id' => auth()->id(),
            'total' => $request->total,
            'status' => 'pending',
        ]);

        // Create order items
        foreach ($request->items as $item) {
            $order->items()->create($item);
        }

        // Dispatch event
        event(new OrderPlaced($order));

        return redirect()->route('orders.show', $order);
    }

    public function ship(Order $order, Request $request)
    {
        $trackingNumber = $request->input('tracking_number');

        event(new OrderShipped($order, $trackingNumber));

        return back()->with('success', 'Order shipped successfully');
    }

    public function cancel(Order $order, Request $request)
    {
        $reason = $request->input('reason');

        event(new OrderCancelled($order, $reason));

        return back()->with('success', 'Order cancelled');
    }
}
```

### Example 3: Activity Logging with Events

```php
<?php

// Event
namespace App\Events;

use NeoPhp\Events\Event;

class ActivityLogged extends Event
{
    public $user;
    public $action;
    public $model;
    public $modelId;
    public $changes;

    public function __construct($user, $action, $model, $modelId, $changes = [])
    {
        $this->user = $user;
        $this->action = $action;
        $this->model = $model;
        $this->modelId = $modelId;
        $this->changes = $changes;
    }
}

// Listener
namespace App\Listeners;

use App\Events\ActivityLogged;
use App\Models\ActivityLog;

class StoreActivityLog
{
    public function handle(ActivityLogged $event)
    {
        ActivityLog::create([
            'user_id' => $event->user?->id,
            'action' => $event->action,
            'model_type' => $event->model,
            'model_id' => $event->modelId,
            'changes' => json_encode($event->changes),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

// Observer for automatic logging
namespace App\Observers;

use App\Events\ActivityLogged;

class PostObserver
{
    public function created($post)
    {
        event(new ActivityLogged(
            auth()->user(),
            'created',
            'Post',
            $post->id
        ));
    }

    public function updated($post)
    {
        event(new ActivityLogged(
            auth()->user(),
            'updated',
            'Post',
            $post->id,
            $post->getChanges()
        ));
    }

    public function deleted($post)
    {
        event(new ActivityLogged(
            auth()->user(),
            'deleted',
            'Post',
            $post->id
        ));
    }
}

// Register observer
public function boot()
{
    Post::observe(PostObserver::class);
}

// View activity logs
class ActivityLogController extends Controller
{
    public function index()
    {
        $activities = ActivityLog::with('user')
            ->latest()
            ->paginate(50);

        return view('admin.activity-logs', compact('activities'));
    }

    public function userActivity($userId)
    {
        $activities = ActivityLog::where('user_id', $userId)
            ->latest()
            ->paginate(50);

        return view('admin.user-activity', compact('activities'));
    }
}
```

## Best Practices

### 1. Use Descriptive Event Names

```php
// Good
class OrderPlaced extends Event {}
class PaymentProcessed extends Event {}

// Bad
class Event1 extends Event {}
class DoSomething extends Event {}
```

### 2. Queue Heavy Listeners

```php
class SendOrderConfirmation implements ShouldQueue
{
    public function handle(OrderPlaced $event)
    {
        // Heavy operation
    }
}
```

### 3. Keep Listeners Focused

Each listener should do one thing:

```php
// Good - separate listeners
SendOrderConfirmation
UpdateInventory
CreateInvoice

// Bad - one listener doing everything
ProcessOrder
```

### 4. Use Event Data Efficiently

```php
class OrderPlaced extends Event
{
    public $order;
    public $user;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->user = $order->user; // Load related data once
    }
}
```

### 5. Handle Failures Gracefully

```php
class SendOrderConfirmation implements ShouldQueue
{
    public $tries = 3;

    public function handle(OrderPlaced $event)
    {
        Mail::to($event->order->user->email)
            ->send(new OrderConfirmation($event->order));
    }

    public function failed(OrderPlaced $event, $exception)
    {
        Log::error('Failed to send order confirmation', [
            'order_id' => $event->order->id,
            'error' => $exception->getMessage(),
        ]);

        // Notify administrator
        Mail::to(config('mail.admin'))
            ->send(new FailedOrderConfirmation($event->order));
    }
}
```

## Testing

### Event Fake

```php
use NeoPhp\Support\Facades\Event;

public function test_order_placed_event_is_dispatched()
{
    Event::fake();

    // Perform action
    $order = $this->placeOrder();

    // Assert event was dispatched
    Event::assertDispatched(OrderPlaced::class, function ($event) use ($order) {
        return $event->order->id === $order->id;
    });
}

public function test_listeners_are_called()
{
    Event::fake([OrderPlaced::class]);

    $order = $this->placeOrder();

    Event::assertDispatched(OrderPlaced::class);
    Event::assertListening(OrderPlaced::class, SendOrderConfirmation::class);
}
```

## Next Steps

- [Queue](queue.md) - Queue event listeners
- [Mail](mail.md) - Send emails from listeners
- [Logging](logging.md) - Log events
- [Observers](../database/eloquent.md#observers) - Model observers
