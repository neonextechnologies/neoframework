# Mail

## Introduction

NeoFramework provides a clean, simple email API powered by popular SwiftMailer library. The framework supports sending mail via SMTP, Mailgun, Postmark, Amazon SES, and `sendmail`, allowing you to quickly get started sending mail through a local or cloud-based service of your choice.

## Configuration

All email configuration is stored in `config/mail.php`. This file allows you to configure your email drivers, as well as set various options like your mail "from" address:

```php
return [
    'default' => env('MAIL_DRIVER', 'smtp'),

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
        ],

        'mailgun' => [
            'transport' => 'mailgun',
            'domain' => env('MAILGUN_DOMAIN'),
            'secret' => env('MAILGUN_SECRET'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],
];
```

## Generating Mailables

Use the Artisan command to generate a mailable class:

```bash
php neo make:mail WelcomeEmail
php neo make:mail Orders/OrderShipped
```

This creates a mailable class in `app/Mail/`:

```php
<?php

namespace App\Mail;

use NeoPhp\Mail\Mailable;

class WelcomeEmail extends Mailable
{
    public function build()
    {
        return $this->view('emails.welcome');
    }
}
```

## Building Mailables

### Setting the Subject

```php
public function build()
{
    return $this->subject('Welcome to Our Platform')
                ->view('emails.welcome');
}
```

### Passing Data to Views

```php
class WelcomeEmail extends Mailable
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->view('emails.welcome');
    }
}
```

Access data in view:

```html
<h1>Welcome, {{ $user->name }}!</h1>
<p>Thank you for joining us.</p>
```

### Using `with()` Method

```php
public function build()
{
    return $this->view('emails.welcome')
                ->with([
                    'orderName' => $this->order->name,
                    'orderPrice' => $this->order->price,
                ]);
}
```

### Attachments

```php
public function build()
{
    return $this->view('emails.order')
                ->attach('/path/to/file.pdf');
}

// Attach from storage
public function build()
{
    return $this->view('emails.order')
                ->attachFromStorage('/path/to/file.pdf');
}

// Attach with options
public function build()
{
    return $this->view('emails.order')
                ->attach('/path/to/file.pdf', [
                    'as' => 'invoice.pdf',
                    'mime' => 'application/pdf',
                ]);
}
```

### Inline Attachments

```php
public function build()
{
    return $this->view('emails.welcome')
                ->attach('/path/to/logo.png', [
                    'as' => 'logo.png',
                    'mime' => 'image/png',
                ]);
}
```

In view:

```html
<img src="{{ $message->embed($pathToLogo) }}">
```

## Sending Mail

### Send Method

```php
use NeoPhp\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

Mail::to('user@example.com')->send(new WelcomeEmail($user));
```

### Multiple Recipients

```php
Mail::to('user@example.com')
    ->cc('manager@example.com')
    ->bcc('admin@example.com')
    ->send(new OrderShipped($order));
```

### Sending to Multiple Addresses

```php
Mail::to([
    'user1@example.com',
    'user2@example.com',
])->send(new WelcomeEmail());

// Or using an array of users
Mail::to($users)->send(new Newsletter());
```

### Using Different Mailers

```php
Mail::mailer('mailgun')
    ->to('user@example.com')
    ->send(new OrderShipped($order));
```

## Queueing Mail

### Queue Method

Since sending email messages can drastically lengthen the response time of your application, many developers choose to queue email messages for background sending:

```php
Mail::to('user@example.com')->queue(new WelcomeEmail($user));
```

### Delayed Queue

```php
$when = now()->addMinutes(10);

Mail::to('user@example.com')
    ->later($when, new WelcomeEmail($user));
```

### Customizing the Queue

```php
class WelcomeEmail extends Mailable
{
    public $queue = 'emails';
    public $delay = 60; // seconds
}
```

## Mail Views

### Creating Email Views

Create a view in `resources/views/emails/welcome.blade.php`:

```html
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .header {
            background: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .footer {
            background: #f4f4f4;
            padding: 10px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to {{ config('app.name') }}</h1>
    </div>
    <div class="content">
        <p>Hello {{ $user->name }},</p>
        <p>Thank you for joining our platform. We're excited to have you on board!</p>
        <p>
            <a href="{{ $verificationUrl }}" style="background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                Verify Your Email
            </a>
        </p>
    </div>
    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
```

### Text Version

```php
public function build()
{
    return $this->view('emails.welcome')
                ->text('emails.welcome_plain');
}
```

## Markdown Mail

### Creating Markdown Mailables

```bash
php neo make:mail OrderShipped --markdown=emails.orders.shipped
```

```php
class OrderShipped extends Mailable
{
    public function build()
    {
        return $this->markdown('emails.orders.shipped');
    }
}
```

### Markdown Syntax

```markdown
@component('mail::message')
# Order Shipped

Your order has been shipped!

@component('mail::button', ['url' => $url])
View Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
```

### Customizing Markdown Components

Publish mail components:

```bash
php neo vendor:publish --tag=mail
```

## Testing

### Mail Fake

```php
use NeoPhp\Support\Facades\Mail;

public function test_welcome_email_is_sent()
{
    Mail::fake();

    // Perform action
    $user = User::factory()->create();

    // Assert email was sent
    Mail::assertSent(WelcomeEmail::class, function ($mail) use ($user) {
        return $mail->user->id === $user->id;
    });

    // Assert email was queued
    Mail::assertQueued(WelcomeEmail::class);

    // Assert no emails were sent
    Mail::assertNothingSent();
}
```

## Practical Examples

### Example 1: User Registration Email

```php
<?php

namespace App\Mail;

use App\Models\User;
use NeoPhp\Mail\Mailable;
use NeoPhp\Support\Facades\URL;

class UserRegistered extends Mailable
{
    public $user;
    public $verificationUrl;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['user' => $user->id]
        );
    }

    public function build()
    {
        return $this->subject('Welcome to ' . config('app.name'))
                    ->view('emails.user-registered')
                    ->with([
                        'userName' => $this->user->name,
                        'verificationUrl' => $this->verificationUrl,
                    ]);
    }
}

// Sending
Mail::to($user->email)->send(new UserRegistered($user));

// Or queue it
Mail::to($user->email)->queue(new UserRegistered($user));
```

View (`resources/views/emails/user-registered.blade.php`):

```html
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4CAF50; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .button { display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to {{ config('app.name') }}</h1>
        </div>
        <div class="content">
            <p>Hello {{ $userName }},</p>
            <p>Thank you for registering with us! Please verify your email address by clicking the button below:</p>
            <p style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
            </p>
            <p>This verification link will expire in 24 hours.</p>
            <p>If you did not create an account, no further action is required.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
```

### Example 2: Order Confirmation with Invoice

```php
<?php

namespace App\Mail;

use App\Models\Order;
use NeoPhp\Mail\Mailable;

class OrderConfirmation extends Mailable
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject('Order Confirmation #' . $this->order->id)
                    ->view('emails.order-confirmation')
                    ->attach(storage_path("invoices/order-{$this->order->id}.pdf"), [
                        'as' => "Invoice-{$this->order->id}.pdf",
                        'mime' => 'application/pdf',
                    ]);
    }
}

// Usage in controller
public function placeOrder(Request $request)
{
    $order = Order::create([...]);

    // Generate invoice PDF
    $pdf = PDF::make($order);
    $pdf->save(storage_path("invoices/order-{$order->id}.pdf"));

    // Send confirmation email
    Mail::to($order->user->email)
        ->send(new OrderConfirmation($order));

    return redirect()->route('orders.show', $order);
}
```

View (`resources/views/emails/order-confirmation.blade.php`):

```html
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: #2196F3; color: white; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f4f4f4; }
        .total { font-weight: bold; font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Confirmation</h1>
            <p>Order #{{ $order->id }}</p>
        </div>
        <div style="padding: 20px;">
            <p>Hello {{ $order->user->name }},</p>
            <p>Thank you for your order! Here are the details:</p>
            
            <h3>Order Items:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->price, 2) }}</td>
                        <td>${{ number_format($item->quantity * $item->price, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total">
                        <td colspan="3">Total:</td>
                        <td>${{ number_format($order->total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>Shipping Address:</h3>
            <p>
                {{ $order->shipping_address }}<br>
                {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}
            </p>
            
            <p>Your order will be shipped within 2-3 business days.</p>
            <p>A PDF invoice is attached to this email for your records.</p>
        </div>
    </div>
</body>
</html>
```

### Example 3: Password Reset Email

```php
<?php

namespace App\Mail;

use App\Models\User;
use NeoPhp\Mail\Mailable;

class PasswordReset extends Mailable
{
    public $user;
    public $token;
    public $resetUrl;

    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
        $this->resetUrl = url("/password/reset/{$token}?email=" . urlencode($user->email));
    }

    public function build()
    {
        return $this->subject('Reset Your Password')
                    ->view('emails.password-reset');
    }
}

// Usage in controller
public function sendResetLink(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return back()->withErrors(['email' => 'User not found']);
    }

    $token = Str::random(60);

    DB::table('password_resets')->insert([
        'email' => $user->email,
        'token' => Hash::make($token),
        'created_at' => now(),
    ]);

    Mail::to($user->email)->send(new PasswordReset($user, $token));

    return back()->with('status', 'Password reset link sent!');
}
```

View (`resources/views/emails/password-reset.blade.php`):

```html
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .content { background: #f9f9f9; padding: 20px; }
        .button { display: inline-block; padding: 12px 30px; background: #FF5722; color: white; text-decoration: none; border-radius: 5px; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <h2>Password Reset Request</h2>
            <p>Hello {{ $user->name }},</p>
            <p>You are receiving this email because we received a password reset request for your account.</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $resetUrl }}" class="button">Reset Password</a>
            </p>
            <p>This password reset link will expire in 60 minutes.</p>
            <div class="warning">
                <strong>Security Notice:</strong> If you did not request a password reset, no further action is required. Your password remains secure.
            </div>
            <p>If you're having trouble clicking the button, copy and paste the URL below into your web browser:</p>
            <p style="word-break: break-all; color: #666;">{{ $resetUrl }}</p>
        </div>
    </div>
</body>
</html>
```

## Best Practices

### 1. Queue Emails

Always queue emails to avoid blocking requests:

```php
Mail::to($user)->queue(new WelcomeEmail($user));
```

### 2. Use Environment Variables

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Test with Mailtrap

Use Mailtrap for development:

```env
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
```

### 4. Handle Mail Failures

```php
try {
    Mail::to($user)->send(new WelcomeEmail($user));
} catch (\Exception $e) {
    Log::error('Failed to send email: ' . $e->getMessage());
    // Handle the error appropriately
}
```

### 5. Use Markdown for Simple Emails

```php
public function build()
{
    return $this->markdown('emails.welcome');
}
```

## Next Steps

- [Queue](queue.md) - Queue emails for background processing
- [Events](events.md) - Listen to mail events
- [Notifications](notifications.md) - Use notifications for multi-channel messaging
- [Testing](../testing/getting-started.md) - Test your mailables
