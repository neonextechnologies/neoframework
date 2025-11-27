# Error Handling

## Introduction

NeoFramework includes robust error and exception handling out of the box. All exceptions are handled by the `App\Exceptions\Handler` class. This guide covers how to configure error handling, customize exception responses, and implement error logging.

## Configuration

Error handling configuration is in `config/app.php`:

```php
'debug' => env('APP_DEBUG', false),
'log_level' => env('LOG_LEVEL', 'debug'),
```

Environment configuration (`.env`):

```env
APP_DEBUG=true
APP_ENV=local
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

## Exception Handler

### The Handler Class

Located at `app/Exceptions/Handler.php`:

```php
<?php

namespace App\Exceptions;

use Exception;
use NeoPhp\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }
}
```

## Reporting Exceptions

### The Report Method

```php
public function report(Exception $exception)
{
    if ($exception instanceof CustomException) {
        // Custom reporting logic
        Log::critical('Custom exception occurred', [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
        ]);
    }

    parent::report($exception);
}
```

### Don't Report Exceptions

```php
protected $dontReport = [
    \NeoPhp\Auth\AuthenticationException::class,
    \NeoPhp\Auth\AuthorizationException::class,
    \NeoPhp\Database\Eloquent\ModelNotFoundException::class,
    \NeoPhp\Validation\ValidationException::class,
];
```

### Report Using Closures

```php
use NeoPhp\Support\Facades\Log;

public function register()
{
    $this->reportable(function (CustomException $e) {
        Log::error('Custom exception', [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
        ]);
    });
}
```

## Rendering Exceptions

### Custom Exception Responses

```php
public function render($request, Exception $exception)
{
    if ($exception instanceof CustomException) {
        return response()->json([
            'error' => $exception->getMessage(),
        ], 400);
    }

    if ($exception instanceof ModelNotFoundException) {
        return response()->json([
            'error' => 'Resource not found',
        ], 404);
    }

    return parent::render($request, $exception);
}
```

### HTTP Exceptions

```php
use NeoPhp\Http\Exceptions\HttpException;

throw new HttpException(404, 'Page not found');
throw new HttpException(403, 'Access denied');
```

### Helper Functions

```php
abort(404);
abort(403, 'Unauthorized action');
abort_if($condition, 403, 'Access denied');
abort_unless($condition, 404);
```

## Custom Exceptions

### Creating Custom Exceptions

```bash
php neo make:exception InvalidOrderException
```

```php
<?php

namespace App\Exceptions;

use Exception;

class InvalidOrderException extends Exception
{
    protected $order;

    public function __construct($order, $message = 'Invalid order', $code = 400)
    {
        parent::__construct($message, $code);
        $this->order = $order;
    }

    public function report()
    {
        Log::error('Invalid order exception', [
            'order_id' => $this->order->id,
            'message' => $this->getMessage(),
        ]);
    }

    public function render($request)
    {
        return response()->json([
            'error' => $this->getMessage(),
            'order_id' => $this->order->id,
        ], $this->code);
    }
}
```

### Throwing Custom Exceptions

```php
use App\Exceptions\InvalidOrderException;

public function process(Order $order)
{
    if ($order->total < 0) {
        throw new InvalidOrderException($order, 'Order total cannot be negative');
    }

    // Process order
}
```

## HTTP Exceptions

### Common HTTP Exceptions

```php
// 400 Bad Request
abort(400, 'Invalid request');

// 401 Unauthorized
abort(401, 'Unauthenticated');

// 403 Forbidden
abort(403, 'Unauthorized action');

// 404 Not Found
abort(404, 'Resource not found');

// 419 Page Expired (CSRF)
abort(419, 'Page expired');

// 429 Too Many Requests
abort(429, 'Too many requests');

// 500 Internal Server Error
abort(500, 'Server error');

// 503 Service Unavailable
abort(503, 'Service unavailable');
```

### Custom Error Pages

Create views in `resources/views/errors/`:

```php
// resources/views/errors/404.blade.php
<!DOCTYPE html>
<html>
<head>
    <title>Page Not Found</title>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you are looking for could not be found.</p>
</body>
</html>
```

## Practical Examples

### Example 1: API Error Handling

```php
<?php

namespace App\Exceptions;

use Exception;
use NeoPhp\Foundation\Exceptions\Handler as ExceptionHandler;
use NeoPhp\Database\Eloquent\ModelNotFoundException;
use NeoPhp\Validation\ValidationException;
use NeoPhp\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    public function render($request, Exception $exception)
    {
        // API requests
        if ($request->expectsJson()) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    protected function handleApiException($request, Exception $exception)
    {
        $status = 500;
        $message = 'Internal server error';

        if ($exception instanceof ModelNotFoundException) {
            $status = 404;
            $message = 'Resource not found';
        } elseif ($exception instanceof ValidationException) {
            $status = 422;
            return response()->json([
                'message' => 'The given data was invalid',
                'errors' => $exception->errors(),
            ], $status);
        } elseif ($exception instanceof AuthenticationException) {
            $status = 401;
            $message = 'Unauthenticated';
        } elseif ($exception instanceof HttpException) {
            $status = $exception->getStatusCode();
            $message = $exception->getMessage();
        }

        $response = [
            'message' => $message,
            'status' => $status,
        ];

        // Include trace in development
        if (config('app.debug')) {
            $response['trace'] = $exception->getTrace();
            $response['exception'] = get_class($exception);
        }

        return response()->json($response, $status);
    }
}
```

### Example 2: Payment Processing Exceptions

```php
<?php

namespace App\Exceptions;

use Exception;

class PaymentException extends Exception
{
    protected $payment;
    protected $errorCode;

    public function __construct($payment, $message, $errorCode = null)
    {
        parent::__construct($message);
        $this->payment = $payment;
        $this->errorCode = $errorCode;
    }

    public function report()
    {
        Log::error('Payment failed', [
            'payment_id' => $this->payment->id,
            'order_id' => $this->payment->order_id,
            'amount' => $this->payment->amount,
            'error_code' => $this->errorCode,
            'message' => $this->getMessage(),
        ]);

        // Notify administrators
        $admins = User::where('is_admin', true)->get();
        Notification::send($admins, new PaymentFailedNotification($this->payment));
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Payment processing failed',
                'message' => $this->getMessage(),
                'error_code' => $this->errorCode,
            ], 402);
        }

        return redirect()->back()->withErrors([
            'payment' => $this->getMessage(),
        ]);
    }
}

// Usage in payment service
class PaymentService
{
    public function processPayment(Order $order, $paymentMethod)
    {
        try {
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total,
                'status' => 'pending',
            ]);

            $result = $this->gateway->charge(
                $paymentMethod,
                $order->total
            );

            if (!$result->success) {
                throw new PaymentException(
                    $payment,
                    $result->message,
                    $result->errorCode
                );
            }

            $payment->update([
                'status' => 'completed',
                'transaction_id' => $result->transactionId,
            ]);

            return $payment;
        } catch (PaymentException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new PaymentException(
                $payment,
                'An unexpected error occurred during payment processing'
            );
        }
    }
}
```

### Example 3: File Upload Exception Handling

```php
<?php

namespace App\Exceptions;

use Exception;

class FileUploadException extends Exception
{
    protected $file;
    protected $reason;

    public function __construct($file, $reason, $message = 'File upload failed')
    {
        parent::__construct($message);
        $this->file = $file;
        $this->reason = $reason;
    }

    public function report()
    {
        Log::warning('File upload failed', [
            'filename' => $this->file->getClientOriginalName(),
            'size' => $this->file->getSize(),
            'mime_type' => $this->file->getMimeType(),
            'reason' => $this->reason,
        ]);
    }

    public function render($request)
    {
        return redirect()->back()->withErrors([
            'file' => $this->getMessage() . ': ' . $this->reason,
        ]);
    }
}

// Usage in controller
class FileController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $file = $request->file('file');

            // Validate file
            if (!$file->isValid()) {
                throw new FileUploadException($file, 'Invalid file');
            }

            if ($file->getSize() > 10 * 1024 * 1024) {
                throw new FileUploadException($file, 'File too large (max 10MB)');
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                throw new FileUploadException($file, 'Invalid file type');
            }

            // Store file
            $path = $file->store('uploads');

            return response()->json(['path' => $path]);
        } catch (FileUploadException $e) {
            throw $e;
        }
    }
}
```

### Example 4: Database Transaction Error Handling

```php
<?php

namespace App\Services;

use NeoPhp\Support\Facades\DB;
use NeoPhp\Support\Facades\Log;
use Exception;

class OrderService
{
    public function createOrder(array $orderData, array $items)
    {
        DB::beginTransaction();

        try {
            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'total' => $orderData['total'],
                'status' => 'pending',
            ]);

            // Create order items
            foreach ($items as $item) {
                $orderItem = $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // Update inventory
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->stock < $item['quantity']) {
                    throw new Exception("Insufficient stock for {$product->name}");
                }

                $product->decrement('stock', $item['quantity']);
            }

            // Process payment
            $payment = $this->processPayment($order);

            if (!$payment->success) {
                throw new PaymentException(
                    $payment,
                    'Payment processing failed'
                );
            }

            DB::commit();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Order creation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
```

### Example 5: Rate Limiting with Custom Exception

```php
<?php

namespace App\Exceptions;

use Exception;

class TooManyRequestsException extends Exception
{
    protected $retryAfter;

    public function __construct($retryAfter = 60)
    {
        parent::__construct('Too many requests', 429);
        $this->retryAfter = $retryAfter;
    }

    public function render($request)
    {
        return response()->json([
            'error' => 'Too many requests',
            'retry_after' => $this->retryAfter,
        ], 429)->header('Retry-After', $this->retryAfter);
    }
}

// Middleware
namespace App\Http\Middleware;

use App\Exceptions\TooManyRequestsException;
use Closure;
use NeoPhp\Support\Facades\Cache;

class RateLimiter
{
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);

        if (Cache::has($key) && Cache::get($key) >= $maxAttempts) {
            throw new TooManyRequestsException($decayMinutes * 60);
        }

        Cache::put($key, Cache::get($key, 0) + 1, $decayMinutes * 60);

        return $next($request);
    }

    protected function resolveRequestSignature($request)
    {
        return sha1(
            $request->method() .
            '|' . $request->server('SERVER_NAME') .
            '|' . $request->path() .
            '|' . $request->ip()
        );
    }
}
```

## Best Practices

### 1. Use Specific Exception Classes

```php
// Good
throw new InvalidOrderException($order, 'Order cannot be processed');

// Bad
throw new Exception('Error');
```

### 2. Log Important Exceptions

```php
public function report()
{
    Log::error('Payment failed', [
        'payment_id' => $this->payment->id,
        'error' => $this->getMessage(),
    ]);
}
```

### 3. Provide Meaningful Error Messages

```php
// Good
throw new PaymentException($payment, 'Insufficient funds');

// Bad
throw new Exception('Error');
```

### 4. Use Try-Catch Blocks

```php
try {
    $result = $this->riskyOperation();
} catch (SpecificException $e) {
    // Handle specific exception
} catch (Exception $e) {
    // Handle general exception
}
```

### 5. Clean Up Resources

```php
try {
    DB::beginTransaction();
    // Operations
    DB::commit();
} catch (Exception $e) {
    DB::rollBack();
    throw $e;
}
```

## Testing Exception Handling

```php
public function test_throws_invalid_order_exception()
{
    $this->expectException(InvalidOrderException::class);

    $order = Order::factory()->create(['total' => -10]);
    $this->service->processOrder($order);
}

public function test_api_returns_404_for_missing_resource()
{
    $response = $this->getJson('/api/users/999');

    $response->assertStatus(404)
        ->assertJson(['message' => 'Resource not found']);
}
```

## Next Steps

- [Logging](logging.md) - Application logging
- [Testing](../testing/getting-started.md) - Test exception handling
- [API Resources](../api/resources.md) - API error responses
- [Validation](../basics/validation.md) - Input validation
