# Authentication

## Introduction

NeoFramework provides a complete authentication system out of the box, making it effortless to secure your application. Authentication facilities include login, registration, password reset, email verification, and multi-guard support.

## Configuration

Authentication configuration is located in `config/auth.php`:

```php
return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],
    
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        
        'api' => [
            'driver' => 'token',
            'provider' => 'users',
        ],
    ],
    
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],
];
```

## User Model

Your `User` model should use the `Authenticatable` trait:

```php
<?php

namespace App\Models;

use NeoPhp\Database\Eloquent\Model;
use NeoPhp\Auth\Authenticatable;

class User extends Model
{
    use Authenticatable;
    
    protected array $fillable = [
        'name',
        'email',
        'password',
    ];
    
    protected array $hidden = [
        'password',
        'remember_token',
    ];
}
```

## Authentication Quickstart

### Login

Create login form:

```php
// routes/web.php
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
```

Login controller:

```php
<?php

namespace App\Controllers;

use NeoPhp\Http\Request;
use NeoPhp\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended('/dashboard');
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
}
```

Login view:

```php
<!-- resources/views/auth/login.php -->
<form method="POST" action="<?= route('login') ?>">
    <?= csrf_field() ?>
    
    <div>
        <label>Email</label>
        <input type="email" name="email" value="<?= old('email') ?>" required>
        <?php if ($errors->has('email')): ?>
            <span><?= $errors->first('email') ?></span>
        <?php endif; ?>
    </div>
    
    <div>
        <label>Password</label>
        <input type="password" name="password" required>
    </div>
    
    <div>
        <label>
            <input type="checkbox" name="remember">
            Remember Me
        </label>
    </div>
    
    <button type="submit">Login</button>
</form>
```

### Registration

```php
// routes/web.php
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
```

Registration controller:

```php
public function showRegisterForm()
{
    return view('auth.register');
}

public function register(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ]);
    
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);
    
    Auth::login($user);
    
    return redirect('/dashboard');
}
```

### Logout

```php
public function logout(Request $request)
{
    Auth::logout();
    
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect('/');
}
```

## Manually Authenticating Users

### Attempting Authentication

```php
use NeoPhp\Support\Facades\Auth;

if (Auth::attempt(['email' => $email, 'password' => $password])) {
    // Authentication passed
}

// With remember me
if (Auth::attempt($credentials, true)) {
    // User will be remembered
}

// Specify guard
if (Auth::guard('admin')->attempt($credentials)) {
    // Admin authentication passed
}
```

### Additional Conditions

```php
if (Auth::attempt(['email' => $email, 'password' => $password, 'active' => 1])) {
    // User is active and authenticated
}
```

### Login by ID

```php
Auth::loginUsingId(1);

Auth::loginUsingId(1, true); // With remember
```

### Login Once (No Session)

```php
if (Auth::once($credentials)) {
    // User authenticated for single request
}
```

### Logging Out

```php
Auth::logout();
```

## Retrieving Authenticated User

### Using Auth Helper

```php
$user = auth()->user();
$id = auth()->id();

if (auth()->check()) {
    // User is logged in
}

if (auth()->guest()) {
    // User is not logged in
}
```

### Using Auth Facade

```php
use NeoPhp\Support\Facades\Auth;

$user = Auth::user();
$id = Auth::id();

if (Auth::check()) {
    // User is logged in
}
```

### In Controllers

```php
class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        
        return view('profile.show', compact('user'));
    }
}
```

## Protecting Routes

### Using Middleware

```php
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::get('/settings', [SettingsController::class, 'index']);
});
```

### In Controllers

```php
class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified')->only('update');
    }
}
```

### Redirecting Unauthenticated Users

Configure redirect path in `app/Middleware/Authenticate.php`:

```php
protected function redirectTo($request)
{
    if (!$request->expectsJson()) {
        return route('login');
    }
}
```

## Password Confirmation

### Requiring Password Confirmation

```php
Route::get('/settings', [SettingsController::class, 'index'])
    ->middleware('password.confirm');
```

### Password Confirmation Form

```php
public function showConfirmForm()
{
    return view('auth.confirm-password');
}

public function confirm(Request $request)
{
    if (!Hash::check($request->password, $request->user()->password)) {
        return back()->withErrors([
            'password' => 'The provided password does not match our records.'
        ]);
    }
    
    $request->session()->passwordConfirmed();
    
    return redirect()->intended();
}
```

## Remember Me

Enable "remember me" functionality:

```php
if (Auth::attempt($credentials, $request->filled('remember'))) {
    // User will be remembered
}
```

Database requirements:

```php
Schema::table('users', function ($table) {
    $table->string('remember_token', 100)->nullable();
});
```

## HTTP Basic Authentication

Protect routes with HTTP Basic Auth:

```php
Route::get('/admin', function () {
    // Only authenticated users may access...
})->middleware('auth.basic');
```

Using specific field:

```php
public function __construct()
{
    $this->middleware('auth.basic:username');
}
```

## API Token Authentication

### Database Setup

```php
Schema::table('users', function ($table) {
    $table->string('api_token', 80)->unique()->nullable();
});
```

### Generating Tokens

```php
use Illuminate\Support\Str;

public function register(Request $request)
{
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'api_token' => Str::random(80),
    ]);
    
    return response()->json([
        'user' => $user,
        'token' => $user->api_token,
    ]);
}
```

### Using Tokens

Configure API guard in `config/auth.php`:

```php
'guards' => [
    'api' => [
        'driver' => 'token',
        'provider' => 'users',
        'hash' => false,
    ],
],
```

Protect API routes:

```php
Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
```

Pass token in requests:

```bash
curl -H "Authorization: Bearer YOUR_API_TOKEN" \
     http://example.com/api/user
```

## Events

Authentication system fires several events:

```php
use NeoPhp\Auth\Events\Attempting;
use NeoPhp\Auth\Events\Authenticated;
use NeoPhp\Auth\Events\Login;
use NeoPhp\Auth\Events\Failed;
use NeoPhp\Auth\Events\Logout;

// Listen to events
Event::listen(Login::class, function ($event) {
    $user = $event->user;
    $remember = $event->remember;
    
    // Log login
    Log::info("User {$user->id} logged in");
});

Event::listen(Logout::class, function ($event) {
    $user = $event->user;
    
    // Log logout
    Log::info("User {$user->id} logged out");
});
```

## Practical Examples

### Example 1: Complete Authentication System

```php
<?php

namespace App\Controllers;

use App\Models\User;
use NeoPhp\Http\Request;
use NeoPhp\Support\Facades\Auth;
use NeoPhp\Support\Facades\Hash;
use NeoPhp\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');
        
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Update last login
            $request->user()->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
            
            return redirect()->intended('/dashboard');
        }
        
        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->onlyInput('email');
    }
    
    public function showRegisterForm()
    {
        return view('auth.register');
    }
    
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verification_token' => Str::random(60),
        ]);
        
        // Send verification email
        Mail::to($user)->send(new VerifyEmail($user));
        
        Auth::login($user);
        
        return redirect('/dashboard')->with('info', 'Please verify your email.');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}
```

### Example 2: API Authentication

```php
<?php

namespace App\Controllers\Api;

use App\Models\User;
use NeoPhp\Http\Request;
use NeoPhp\Support\Facades\Hash;
use NeoPhp\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'api_token' => Str::random(80),
        ]);
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $user->api_token,
        ], 201);
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }
        
        // Generate new token
        $user->api_token = Str::random(80);
        $user->save();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $user->api_token,
        ]);
    }
    
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->api_token = null;
        $user->save();
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
    
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }
}
```

## Best Practices

### 1. Always Hash Passwords

```php
$user->password = Hash::make($request->password);
```

### 2. Regenerate Session on Login

```php
$request->session()->regenerate();
```

### 3. Validate Credentials Properly

```php
$request->validate([
    'email' => 'required|email',
    'password' => 'required',
]);
```

### 4. Use Middleware to Protect Routes

```php
Route::middleware('auth')->group(function () {
    // Protected routes
});
```

### 5. Implement Rate Limiting

```php
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1'); // 5 attempts per minute
```

## Testing Authentication

```php
class AuthTest extends TestCase
{
    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password')
        ]);
        
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
    
    public function test_user_cannot_login_with_wrong_password()
    {
        $user = User::factory()->create();
        
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
        
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }
}
```

## Next Steps

- [Authorization](authorization.md) - Policies and gates
- [Password Reset](password-reset.md) - Reset forgotten passwords
- [Email Verification](email-verification.md) - Verify email addresses
- [Multi-Guard Authentication](multi-guard.md) - Multiple authentication systems
