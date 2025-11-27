# 📧 Mail Generator

Generate mailable classes for sending emails in your NeoFramework application. The mail generator creates fully-featured email classes with support for Markdown templates, attachments, and custom styling.

## 📋 Table of Contents

- [Basic Usage](#basic-usage)
- [Command Options](#command-options)
- [Generated Code](#generated-code)
- [Markdown Emails](#markdown-emails)
- [Advanced Examples](#advanced-examples)
- [Best Practices](#best-practices)

## 🚀 Basic Usage

### Generate Mail Class

```bash
php neo make:mail WelcomeEmail
```

**Generated:** `app/Mail/WelcomeEmail.php`

```php
<?php

namespace App\Mail;

use Neo\Mail\Mailable;
use Neo\Mail\Mailables\Content;
use Neo\Mail\Mailables\Envelope;

class WelcomeEmail extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Neo\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
```

## ⚙️ Command Options

### Available Options

| Option | Description |
|--------|-------------|
| `--markdown` | Create email with Markdown template |
| `--force` | Overwrite existing mail class |

### Create Markdown Email

```bash
php neo make:mail OrderShipped --markdown=emails.orders.shipped
```

**Generated:** 
- `app/Mail/OrderShipped.php`
- `resources/views/emails/orders/shipped.blade.php`

**Mail Class:**

```php
<?php

namespace App\Mail;

use Neo\Mail\Mailable;
use Neo\Mail\Mailables\Content;
use Neo\Mail\Mailables\Envelope;

class OrderShipped extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Shipped',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.shipped',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Neo\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
```

**Markdown Template:**

```markdown
@component('mail::message')
# Order Shipped

Your order has been shipped!

@component('mail::button', ['url' => $url])
Track Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
```

## 📝 Complete Examples

### Welcome Email

```php
<?php

namespace App\Mail;

use Neo\Mail\Mailable;
use Neo\Mail\Mailables\Content;
use Neo\Mail\Mailables\Envelope;
use Neo\Mail\Mailables\Address;
use App\Models\User;

class WelcomeEmail extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('noreply@example.com', 'NeoFramework'),
            replyTo: [
                new Address('support@example.com', 'Support Team'),
            ],
            subject: 'Welcome to ' . config('app.name'),
            tags: ['welcome', 'new-user'],
            metadata: [
                'user_id' => $this->user->id,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'userName' => $this->user->name,
                'loginUrl' => route('login'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Neo\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
```

**View Template:** `resources/views/emails/welcome.blade.php`

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #4a5568;">Welcome, {{ $userName }}!</h1>
        
        <p>Thank you for joining {{ config('app.name') }}. We're excited to have you on board!</p>
        
        <p>To get started, please click the button below to log in:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $loginUrl }}" 
               style="background-color: #4299e1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;">
                Log In
            </a>
        </div>
        
        <p>If you have any questions, feel free to reach out to our support team.</p>
        
        <p>
            Best regards,<br>
            The {{ config('app.name') }} Team
        </p>
    </div>
</body>
</html>
```

### Order Confirmation Email

```php
<?php

namespace App\Mail;

use Neo\Mail\Mailable;
use Neo\Mail\Mailables\Content;
use Neo\Mail\Mailables\Envelope;
use Neo\Mail\Mailables\Attachment;
use App\Models\Order;

class OrderConfirmation extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order Confirmation #{$this->order->id}",
            tags: ['order', 'confirmation'],
            metadata: [
                'order_id' => $this->order->id,
                'customer_id' => $this->order->user_id,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.confirmation',
            with: [
                'order' => $this->order,
                'total' => $this->order->total,
                'items' => $this->order->items,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Neo\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->order->invoice_path)
                ->as('invoice.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
```

**Markdown Template:** `resources/views/emails/orders/confirmation.blade.php`

```markdown
@component('mail::message')
# Order Confirmation

Thank you for your order! Here are the details:

**Order Number:** {{ $order->id }}  
**Order Date:** {{ $order->created_at->format('F d, Y') }}

## Order Items

@component('mail::table')
| Item | Quantity | Price |
|:-----|:--------:|------:|
@foreach($items as $item)
| {{ $item->name }} | {{ $item->quantity }} | ${{ number_format($item->price, 2) }} |
@endforeach
| **Total** | | **${{ number_format($total, 2) }}** |
@endcomponent

@component('mail::button', ['url' => route('orders.show', $order->id)])
View Order
@endcomponent

You can track your order status anytime by logging into your account.

Thanks for shopping with us!

{{ config('app.name') }}
@endcomponent
```

### Password Reset Email

```php
<?php

namespace App\Mail;

use Neo\Mail\Mailable;
use Neo\Mail\Mailables\Content;
use Neo\Mail\Mailables\Envelope;
use App\Models\User;

class PasswordReset extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public string $token
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password',
            tags: ['password-reset', 'security'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $this->user->email,
        ], false));

        return new Content(
            markdown: 'emails.auth.password-reset',
            with: [
                'user' => $this->user,
                'resetUrl' => $resetUrl,
                'expiresIn' => config('auth.passwords.users.expire'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Neo\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
```

### Invoice Email

```php
<?php

namespace App\Mail;

use Neo\Mail\Mailable;
use Neo\Mail\Mailables\Content;
use Neo\Mail\Mailables\Envelope;
use Neo\Mail\Mailables\Attachment;
use App\Models\Invoice;

class InvoicePaid extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        public Invoice $invoice
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice #{$this->invoice->number} - Payment Received",
            tags: ['invoice', 'payment'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoices.paid',
            with: [
                'invoice' => $this->invoice,
                'customer' => $this->invoice->customer,
                'amount' => $this->invoice->total,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Neo\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            // From disk
            Attachment::fromStorage('invoices/' . $this->invoice->pdf_file)
                ->as("invoice-{$this->invoice->number}.pdf")
                ->withMime('application/pdf'),
            
            // From raw data
            Attachment::fromData(fn () => $this->invoice->generatePdf(), 'invoice.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
```

### Newsletter Email

```php
<?php

namespace App\Mail;

use Neo\Mail\Mailable;
use Neo\Mail\Mailables\Content;
use Neo\Mail\Mailables\Envelope;
use App\Models\Newsletter;
use App\Models\User;

class NewsletterEmail extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        public Newsletter $newsletter,
        public User $subscriber
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->newsletter->subject,
            tags: ['newsletter', $this->newsletter->category],
            metadata: [
                'newsletter_id' => $this->newsletter->id,
                'subscriber_id' => $this->subscriber->id,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter',
            text: 'emails.newsletter-plain',
            with: [
                'newsletter' => $this->newsletter,
                'subscriber' => $this->subscriber,
                'unsubscribeUrl' => route('newsletter.unsubscribe', [
                    'token' => $this->subscriber->unsubscribe_token
                ]),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Neo\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
```

## 🎨 Markdown Components

### Button Component

```markdown
@component('mail::button', ['url' => $actionUrl, 'color' => 'success'])
Get Started
@endcomponent
```

**Available colors:** `primary`, `success`, `error`

### Panel Component

```markdown
@component('mail::panel')
This is a highlighted panel of text.
@endcomponent
```

### Table Component

```markdown
@component('mail::table')
| Name | Price |
|:-----|------:|
| Product 1 | $10.00 |
| Product 2 | $20.00 |
@endcomponent
```

### Promotion Component

```markdown
@component('mail::promotion')
Use code **SAVE20** for 20% off your next order!
@endcomponent
```

### Subcopy Component

```markdown
@component('mail::subcopy')
If you're having trouble clicking the button, copy and paste the URL below into your browser:
[{{ $actionUrl }}]({{ $actionUrl }})
@endcomponent
```

## 🔧 Sending Emails

### Basic Sending

```php
use App\Mail\WelcomeEmail;
use Neo\Support\Facades\Mail;

// Send immediately
Mail::to($user->email)->send(new WelcomeEmail($user));

// Send to multiple recipients
Mail::to([$user1->email, $user2->email])
    ->send(new WelcomeEmail($user));

// CC and BCC
Mail::to($user->email)
    ->cc('manager@example.com')
    ->bcc('admin@example.com')
    ->send(new WelcomeEmail($user));
```

### Queue Emails

```php
use App\Mail\WelcomeEmail;
use Neo\Support\Facades\Mail;

// Queue the email
Mail::to($user->email)->queue(new WelcomeEmail($user));

// Queue with delay
Mail::to($user->email)
    ->later(now()->addMinutes(10), new WelcomeEmail($user));

// Queue on specific connection
Mail::to($user->email)
    ->queue((new WelcomeEmail($user))->onQueue('emails'));
```

### Conditional Sending

```php
use App\Mail\WelcomeEmail;
use Neo\Support\Facades\Mail;

// Send only if condition is true
Mail::to($user->email)
    ->when($user->wants_emails, function ($mail) use ($user) {
        $mail->send(new WelcomeEmail($user));
    });

// Send unless condition is true
Mail::to($user->email)
    ->unless($user->unsubscribed, function ($mail) use ($user) {
        $mail->send(new WelcomeEmail($user));
    });
```

## 🎯 Advanced Examples

### Multi-part Email

```php
<?php

namespace App\Mail;

use Neo\Mail\Mailable;
use Neo\Mail\Mailables\Content;
use Neo\Mail\Mailables\Envelope;

class NewsUpdate extends Mailable
{
    public function __construct(
        public array $news
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Weekly News Update',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.news.update',
            text: 'emails.news.update-text',
            with: ['news' => $this->news],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
```

### Email with Inline Images

```php
<?php

namespace App\Mail;

use Neo\Mail\Mailable;
use Neo\Mail\Mailables\Content;
use Neo\Mail\Mailables\Envelope;
use Neo\Mail\Mailables\Attachment;

class ProductLaunch extends Mailable
{
    public function __construct(
        public Product $product
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Product: {$this->product->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.products.launch',
        );
    }

    public function attachments(): array
    {
        return [
            // Inline attachment (embedded in email)
            Attachment::fromPath($this->product->image_path)
                ->as('product.jpg')
                ->withMime('image/jpeg')
                ->inline(),
        ];
    }
}
```

**In the view:**

```html
<img src="{{ $message->embed($product->image_path) }}" alt="Product">
```

### Email with Custom Headers

```php
<?php

namespace App\Mail;

use Neo\Mail\Mailable;
use Neo\Mail\Mailables\Content;
use Neo\Mail\Mailables\Envelope;
use Neo\Mail\Mailables\Headers;

class CustomHeaderMail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Custom Headers Email',
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            messageId: 'custom-message-id@example.com',
            references: ['previous-message-id@example.com'],
            text: [
                'X-Custom-Header' => 'Custom Value',
                'X-Priority' => '1',
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.custom',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
```

## 🎯 Best Practices

### Use Constructor Injection

```php
// Good
public function __construct(
    public User $user,
    public Order $order
) {}

// Bad
public User $user;
public Order $order;

public function __construct($user, $order)
{
    $this->user = $user;
    $this->order = $order;
}
```

### Keep Mail Classes Focused

```php
// Good: One email per class
class OrderConfirmation extends Mailable { }
class OrderShipped extends Mailable { }
class OrderDelivered extends Mailable { }

// Bad: One class for all order emails
class OrderEmail extends Mailable {
    public function __construct($type) { }
}
```

### Use Markdown for Simple Emails

```php
// Good for transactional emails
public function content(): Content
{
    return new Content(
        markdown: 'emails.simple-notification',
    );
}

// Use HTML for complex branded emails
public function content(): Content
{
    return new Content(
        view: 'emails.marketing.campaign',
    );
}
```

### Include Unsubscribe Links

```markdown
@component('mail::subcopy')
Don't want to receive these emails? 
[Unsubscribe]({{ $unsubscribeUrl }})
@endcomponent
```

### Test Emails

```php
<?php

namespace Tests\Feature\Mail;

use Tests\TestCase;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Neo\Support\Facades\Mail;

class WelcomeEmailTest extends TestCase
{
    public function test_welcome_email_is_sent()
    {
        Mail::fake();

        $user = User::factory()->create();

        Mail::to($user)->send(new WelcomeEmail($user));

        Mail::assertSent(WelcomeEmail::class, function ($mail) use ($user) {
            return $mail->user->id === $user->id;
        });
    }
}
```

## 📚 Related Documentation

- [Mail Configuration](../advanced/mail.md) - Email configuration
- [Queues](../advanced/queue.md) - Queue email sending
- [Notifications](../advanced/notifications.md) - User notifications

## 🔗 Quick Reference

```bash
# Generate mail class
php neo make:mail WelcomeEmail

# Generate with Markdown template
php neo make:mail OrderShipped --markdown=emails.orders.shipped

# Force overwrite
php neo make:mail WelcomeEmail --force
```

**Sending emails:**

```php
// Send now
Mail::to($user)->send(new WelcomeEmail($user));

// Queue
Mail::to($user)->queue(new WelcomeEmail($user));

// Delay
Mail::to($user)->later(now()->addMinutes(10), new Email($user));
```
