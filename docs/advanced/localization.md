# Localization

## Introduction

NeoFramework's localization features provide a convenient way to retrieve strings in various languages, allowing you to easily support multiple languages within your application. Language strings are stored in files within the `resources/lang` directory.

## Configuration

The default language for your application is stored in the `config/app.php` configuration file:

```php
'locale' => 'en',
'fallback_locale' => 'en',
```

## Defining Translation Strings

### Using Short Keys

Translation strings are stored in files within `resources/lang`:

```
/resources
    /lang
        /en
            messages.php
            validation.php
        /th
            messages.php
            validation.php
```

Example `resources/lang/en/messages.php`:

```php
<?php

return [
    'welcome' => 'Welcome to our application!',
    'goodbye' => 'Goodbye!',
    'greeting' => 'Hello, :name',
    'messages' => [
        'success' => 'Operation completed successfully',
        'error' => 'An error occurred',
    ],
];
```

Thai translation `resources/lang/th/messages.php`:

```php
<?php

return [
    'welcome' => 'ยินดีต้อนรับสู่แอปพล  ิเคชันของเรา!',
    'goodbye' => 'ลาก่อน!',
    'greeting' => 'สวัสดี, :name',
    'messages' => [
        'success' => 'ดำเนินการเสร็จสมบูรณ์',
        'error' => 'เกิดข้อผิดพลาด',
    ],
];
```

## Retrieving Translation Strings

### Using the __ Helper

```php
echo __('messages.welcome');
// Welcome to our application!

echo __('messages.goodbye');
// Goodbye!
```

### Replacing Parameters

```php
echo __('messages.greeting', ['name' => 'John']);
// Hello, John

echo __('messages.greeting', ['name' => 'สมชาย']);
// สวัสดี, สมชาย
```

### Pluralization

```php
// resources/lang/en/messages.php
'apples' => '{0} There are none|{1} There is one|[2,*] There are :count',

// Usage
echo trans_choice('messages.apples', 0); // There are none
echo trans_choice('messages.apples', 1); // There is one
echo trans_choice('messages.apples', 10); // There are 10
```

## Setting the Locale

### Changing the Current Locale

```php
use NeoPhp\Support\Facades\App;

App::setLocale('th');
```

### Getting the Current Locale

```php
$locale = App::getLocale();

if (App::isLocale('en')) {
    //
}
```

### Middleware for Locale

```php
<?php

namespace App\Http\Middleware;

use Closure;
use NeoPhp\Support\Facades\App;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = $request->segment(1);

        if (in_array($locale, ['en', 'th'])) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
```

## Translation in Views

### Blade Directive

```blade
{{ __('messages.welcome') }}

{{ __('messages.greeting', ['name' => $user->name]) }}

@lang('messages.welcome')
```

### Pluralization in Blade

```blade
{{ trans_choice('messages.apples', $count) }}
```

## JSON Translation Strings

For packages or when you don't want to nest keys:

Create `resources/lang/en.json`:

```json
{
    "Welcome": "Welcome to our application",
    "Goodbye": "Goodbye",
    "Hello :name": "Hello :name"
}
```

Thai version `resources/lang/th.json`:

```json
{
    "Welcome": "ยินดีต้อนรับสู่แอปพลิเคชันของเรา",
    "Goodbye": "ลาก่อน",
    "Hello :name": "สวัสดี :name"
}
```

Usage:

```php
echo __('Welcome');
echo __('Hello :name', ['name' => 'John']);
```

## Practical Examples

### Example 1: Multi-language Website

```php
<?php

// Middleware to detect and set language
namespace App\Http\Middleware;

use Closure;
use NeoPhp\Support\Facades\App;
use NeoPhp\Support\Facades\Session;

class SetLanguage
{
    public function handle($request, Closure $next)
    {
        // Check URL segment
        $locale = $request->segment(1);
        $availableLocales = config('app.available_locales', ['en', 'th']);

        if (in_array($locale, $availableLocales)) {
            App::setLocale($locale);
            Session::put('locale', $locale);
        } else {
            // Check session
            $locale = Session::get('locale', config('app.locale'));
            App::setLocale($locale);
        }

        return $next($request);
    }
}

// Language switcher controller
namespace App\Http\Controllers;

use NeoPhp\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch($locale)
    {
        $availableLocales = config('app.available_locales', ['en', 'th']);

        if (in_array($locale, $availableLocales)) {
            Session::put('locale', $locale);
        }

        return redirect()->back();
    }
}

// Routes with language prefix
Route::group(['prefix' => '{locale}', 'middleware' => 'set.language'], function () {
    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/about', 'HomeController@about')->name('about');
    Route::get('/contact', 'HomeController@contact')->name('contact');
});

Route::get('/language/{locale}', 'LanguageController@switch')->name('language.switch');
```

Language files:

```php
// resources/lang/en/site.php
return [
    'home' => 'Home',
    'about' => 'About Us',
    'contact' => 'Contact',
    'welcome_message' => 'Welcome to :site_name',
    'about_text' => 'We are a leading company in...',
    'contact_us' => 'Contact Us',
    'phone' => 'Phone',
    'email' => 'Email',
];

// resources/lang/th/site.php
return [
    'home' => 'หน้าหลัก',
    'about' => 'เกี่ยวกับเรา',
    'contact' => 'ติดต่อ',
    'welcome_message' => 'ยินดีต้อนรับสู่ :site_name',
    'about_text' => 'เราเป็นบริษัทชั้นนำใน...',
    'contact_us' => 'ติดต่อเรา',
    'phone' => 'โทรศัพท์',
    'email' => 'อีเมล',
];
```

View with language switcher:

```blade
<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('site.home') }} - {{ config('app.name') }}</title>
</head>
<body>
    <nav>
        <ul>
            <li><a href="{{ route('home', App::getLocale()) }}">{{ __('site.home') }}</a></li>
            <li><a href="{{ route('about', App::getLocale()) }}">{{ __('site.about') }}</a></li>
            <li><a href="{{ route('contact', App::getLocale()) }}">{{ __('site.contact') }}</a></li>
        </ul>
        
        <!-- Language Switcher -->
        <div class="language-switcher">
            <a href="{{ route('language.switch', 'en') }}" class="{{ App::getLocale() == 'en' ? 'active' : '' }}">English</a>
            <a href="{{ route('language.switch', 'th') }}" class="{{ App::getLocale() == 'th' ? 'active' : '' }}">ไทย</a>
        </div>
    </nav>

    <main>
        <h1>{{ __('site.welcome_message', ['site_name' => config('app.name')]) }}</h1>
        @yield('content')
    </main>
</body>
</html>
```

### Example 2: Localized Validation Messages

```php
// resources/lang/en/validation.php
return [
    'required' => 'The :attribute field is required.',
    'email' => 'The :attribute must be a valid email address.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'max' => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'string' => 'The :attribute may not be greater than :max characters.',
    ],
    'attributes' => [
        'name' => 'name',
        'email' => 'email address',
        'password' => 'password',
    ],
];

// resources/lang/th/validation.php
return [
    'required' => 'กรุณากรอก :attribute',
    'email' => ':attribute ต้องเป็นอีเมลที่ถูกต้อง',
    'min' => [
        'numeric' => ':attribute ต้องมีค่าอย่างน้อย :min',
        'string' => ':attribute ต้องมีอย่างน้อย :min ตัวอักษร',
    ],
    'max' => [
        'numeric' => ':attribute ต้องไม่เกิน :max',
        'string' => ':attribute ต้องไม่เกิน :max ตัวอักษร',
    ],
    'attributes' => [
        'name' => 'ชื่อ',
        'email' => 'อีเมล',
        'password' => 'รหัสผ่าน',
    ],
];

// Controller
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|max:255',
        'email' => 'required|email',
        'password' => 'required|min:8',
    ]);

    // Messages will be displayed in current locale
}
```

### Example 3: Localized Date and Numbers

```php
<?php

namespace App\Helpers;

use NeoPhp\Support\Facades\App;
use Carbon\Carbon;

class LocalizationHelper
{
    public static function formatDate($date, $format = 'long')
    {
        $locale = App::getLocale();

        if ($locale === 'th') {
            Carbon::setLocale('th');
            
            if ($format === 'long') {
                return $date->translatedFormat('j F Y');
            }
            
            return $date->translatedFormat('d/m/Y');
        }

        if ($format === 'long') {
            return $date->format('F j, Y');
        }

        return $date->format('m/d/Y');
    }

    public static function formatNumber($number, $decimals = 0)
    {
        $locale = App::getLocale();

        if ($locale === 'th') {
            return number_format($number, $decimals, '.', ',');
        }

        return number_format($number, $decimals, '.', ',');
    }

    public static function formatCurrency($amount)
    {
        $locale = App::getLocale();

        if ($locale === 'th') {
            return '฿' . number_format($amount, 2);
        }

        return '$' . number_format($amount, 2);
    }
}

// Usage in view
{{ LocalizationHelper::formatDate($post->created_at) }}
{{ LocalizationHelper::formatCurrency($product->price) }}
```

### Example 4: Multi-language Email Notifications

```php
<?php

namespace App\Mail;

use NeoPhp\Mail\Mailable;
use NeoPhp\Support\Facades\App;

class OrderConfirmation extends Mailable
{
    public $order;
    public $locale;

    public function __construct($order)
    {
        $this->order = $order;
        $this->locale = $order->user->locale ?? config('app.locale');
    }

    public function build()
    {
        App::setLocale($this->locale);

        return $this->subject(__('emails.order_confirmation'))
                    ->view('emails.order-confirmation')
                    ->with([
                        'greeting' => __('emails.greeting', ['name' => $this->order->user->name]),
                        'thanks' => __('emails.thanks'),
                    ]);
    }
}

// Language files
// resources/lang/en/emails.php
return [
    'order_confirmation' => 'Order Confirmation',
    'greeting' => 'Hello :name,',
    'thanks' => 'Thank you for your order!',
    'order_details' => 'Order Details',
    'total' => 'Total',
];

// resources/lang/th/emails.php
return [
    'order_confirmation' => 'ยืนยันคำสั่งซื้อ',
    'greeting' => 'สวัสดีคุณ :name',
    'thanks' => 'ขอบคุณสำหรับคำสั่งซื้อของคุณ!',
    'order_details' => 'รายละเอียดคำสั่งซื้อ',
    'total' => 'ยอดรวม',
];

// Email view
@component('mail::message')
# {{ __('emails.order_confirmation') }}

{{ __('emails.greeting', ['name' => $order->user->name]) }}

{{ __('emails.thanks') }}

## {{ __('emails.order_details') }}

@component('mail::table')
| {{ __('emails.product') }} | {{ __('emails.quantity') }} | {{ __('emails.price') }} |
| :------------- | :----------: | -----------: |
@foreach($order->items as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | {{ LocalizationHelper::formatCurrency($item->price) }} |
@endforeach
@endcomponent

**{{ __('emails.total') }}:** {{ LocalizationHelper::formatCurrency($order->total) }}

@component('mail::button', ['url' => route('orders.show', $order->id)])
{{ __('emails.view_order') }}
@endcomponent

{{ __('emails.signature') }}<br>
{{ config('app.name') }}
@endcomponent
```

### Example 5: Localized API Responses

```php
<?php

namespace App\Http\Controllers\API;

use NeoPhp\Support\Facades\App;

class BaseController extends Controller
{
    protected function respondWithSuccess($data, $message = null)
    {
        return response()->json([
            'success' => true,
            'message' => $message ?? __('api.success'),
            'data' => $data,
        ]);
    }

    protected function respondWithError($message, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message ?? __('api.error'),
        ], $code);
    }

    protected function setLocaleFromRequest()
    {
        $locale = request()->header('Accept-Language', 'en');
        
        // Parse locale from header (e.g., "th-TH,th;q=0.9,en-US;q=0.8")
        $locale = substr($locale, 0, 2);
        
        if (in_array($locale, config('app.available_locales', ['en', 'th']))) {
            App::setLocale($locale);
        }
    }
}

class ProductController extends BaseController
{
    public function index()
    {
        $this->setLocaleFromRequest();

        $products = Product::all();

        return $this->respondWithSuccess($products, __('api.products_retrieved'));
    }

    public function store(Request $request)
    {
        $this->setLocaleFromRequest();

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->respondWithError($validator->errors()->first(), 422);
        }

        $product = Product::create($request->all());

        return $this->respondWithSuccess($product, __('api.product_created'));
    }
}

// Language files
// resources/lang/en/api.php
return [
    'success' => 'Operation completed successfully',
    'error' => 'An error occurred',
    'products_retrieved' => 'Products retrieved successfully',
    'product_created' => 'Product created successfully',
    'product_updated' => 'Product updated successfully',
    'product_deleted' => 'Product deleted successfully',
    'not_found' => 'Resource not found',
];

// resources/lang/th/api.php
return [
    'success' => 'ดำเนินการสำเร็จ',
    'error' => 'เกิดข้อผิดพลาด',
    'products_retrieved' => 'ดึงข้อมูลสินค้าสำเร็จ',
    'product_created' => 'สร้างสินค้าสำเร็จ',
    'product_updated' => 'อัพเดทสินค้าสำเร็จ',
    'product_deleted' => 'ลบสินค้าสำเร็จ',
    'not_found' => 'ไม่พบข้อมูล',
];
```

## Best Practices

### 1. Organize Translation Files

```
/resources/lang
    /en
        auth.php
        pagination.php
        validation.php
        messages.php
        site.php
    /th
        auth.php
        pagination.php
        validation.php
        messages.php
        site.php
```

### 2. Use Consistent Naming

```php
// Good
'user.created' => 'User created successfully'
'user.updated' => 'User updated successfully'
'user.deleted' => 'User deleted successfully'

// Avoid
'success1' => 'User created'
'msg2' => 'Updated'
```

### 3. Store User Locale Preference

```php
// In User model
public function setLocale($locale)
{
    $this->update(['locale' => $locale]);
    App::setLocale($locale);
}

// Usage
auth()->user()->setLocale('th');
```

### 4. Fallback to Default Locale

```php
$message = __('messages.welcome');
// Falls back to 'en' if translation doesn't exist in current locale
```

### 5. Use JSON for Simple Translations

```json
{
    "Save": "บันทึก",
    "Cancel": "ยกเลิก",
    "Delete": "ลบ",
    "Edit": "แก้ไข"
}
```

## Testing Localization

```php
public function test_displays_thai_translation()
{
    App::setLocale('th');

    $response = $this->get('/');

    $response->assertSee('ยินดีต้อนรับ');
}

public function test_email_sent_in_user_locale()
{
    $user = User::factory()->create(['locale' => 'th']);

    Mail::to($user)->send(new WelcomeEmail($user));

    // Assert email contains Thai text
}
```

## Next Steps

- [Validation](../basics/validation.md) - Localized validation messages
- [Mail](mail.md) - Multi-language emails
- [Views](../basics/views.md) - Localized views
- [API Resources](../api/resources.md) - Localized API responses
