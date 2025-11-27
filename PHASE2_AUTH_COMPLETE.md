# NeoFramework - Advanced Authentication & Authorization

## Phase 2 Complete: Auth & Authorization System ✅

เสร็จสมบูรณ์! Phase 2 ได้เพิ่มระบบ authentication และ authorization ขั้นสูงเข้ามาใน NeoFramework

---

## 🔐 Features Implemented

### 1. Password Reset System
- Token-based password reset
- Email notifications
- Token expiration (1 hour default)
- Secure token hashing

### 2. Email Verification
- Email verification tokens
- `MustVerifyEmail` interface
- `VerifiesEmails` trait
- Verification email templates

### 3. Remember Me
- Persistent login tokens
- 30-day expiration
- Cookie-based storage
- Auto-login on return

### 4. Multi-Auth Guards
- **Session Guard** - Web-based authentication
- **Token Guard** - API token authentication  
- Guard interface for custom guards
- Multiple guard support (web, api, admin)

### 5. Authorization System
- **Gates** - Closure-based authorization
- **Policies** - Model-specific authorization
- `AuthorizesRequests` trait for controllers
- Helper functions

---

## 📚 Usage Examples

### Password Reset

```php
use NeoPhp\Auth\Passwords\PasswordFacade as Password;

// Send reset link
$status = Password::sendResetLink('user@example.com');

if ($status === Password::PASSWORD_RESET) {
    echo "Password reset link sent!";
}

// Reset password
$status = Password::reset([
    'email' => 'user@example.com',
    'password' => 'newpassword',
    'token' => $token
]);
```

### Email Verification

```php
use App\Models\User;
use NeoPhp\Auth\EmailVerification\MustVerifyEmail;
use NeoPhp\Auth\EmailVerification\VerifiesEmails;

class User extends Model implements MustVerifyEmail
{
    use VerifiesEmails;
    
    // Add email_verified_at column
    protected array $casts = [
        'email_verified_at' => 'datetime'
    ];
}

// Send verification email
$user->sendEmailVerificationNotification();

// Check if verified
if ($user->hasVerifiedEmail()) {
    echo "Email verified!";
}

// Mark as verified
$user->markEmailAsVerified();
```

### Remember Me

```php
// Login with remember me
auth()->attempt([
    'email' => 'user@example.com',
    'password' => 'password'
], $remember = true);

// Auto-login on next visit
$user = auth()->user(); // Will use remember token if session expired
```

### Multi-Auth Guards

```php
// Configure in config/auth.php
return [
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users'
        ],
        'api' => [
            'driver' => 'token',
            'provider' => 'users'
        ],
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins'
        ]
    ],
    'providers' => [
        'users' => [
            'model' => \App\Models\User::class
        ],
        'admins' => [
            'model' => \App\Models\Admin::class
        ]
    ]
];

// Use different guards
auth()->user(); // Default guard (web)
auth('api')->user(); // API guard
auth('admin')->check(); // Admin guard

// Login with specific guard
auth('admin')->login($admin);
```

### Authorization - Gates

```php
use NeoPhp\Auth\Access\Gate;

// Define gates (in service provider)
Gate::define('update-post', function ($user, $post) {
    return $user->id === $post->user_id;
});

Gate::define('delete-post', function ($user, $post) {
    return $user->id === $post->user_id || $user->isAdmin();
});

// Check authorization
if (Gate::allows('update-post', $post)) {
    // User can update post
}

if (Gate::denies('delete-post', $post)) {
    // User cannot delete post
}

// Authorize or throw exception
Gate::authorize('update-post', $post);

// Helper functions
if (can('update-post', $post)) {
    // User can update
}

if (cannot('delete-post', $post)) {
    // User cannot delete
}
```

### Authorization - Policies

```php
// Generate policy
php neo make:policy PostPolicy --model=Post

// Register policy (in service provider)
use NeoPhp\Auth\Access\Gate;
use App\Models\Post;
use App\Policies\PostPolicy;

Gate::policy(Post::class, PostPolicy::class);

// PostPolicy.php
namespace App\Policies;

use NeoPhp\Auth\Access\Policy;
use App\Models\User;
use App\Models\Post;

class PostPolicy extends Policy
{
    public function update(User $user, Post $post): bool
    {
        return $this->owns($user, $post);
    }
    
    public function delete(User $user, Post $post): bool
    {
        return $this->owns($user, $post) || $this->hasRole($user, 'admin');
    }
}

// Check policy
if (Gate::check($post, 'update')) {
    // User can update post
}
```

### In Controllers

```php
use NeoPhp\Auth\Access\AuthorizesRequests;

class PostController extends Controller
{
    use AuthorizesRequests;
    
    public function update(int $id)
    {
        $post = Post::find($id);
        
        // Authorize or throw 403
        $this->authorize('update-post', $post);
        
        // Alternative: using model policy
        $this->authorizeResource('update', $post);
        
        // Or check without exception
        if ($this->cannot('delete-post', $post)) {
            return json(['error' => 'Unauthorized'], 403);
        }
        
        // Update post...
    }
}
```

---

## 🗄️ Database Migrations

```sql
-- Password reset tokens
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL
);

-- Email verification tokens
CREATE TABLE email_verification_tokens (
    user_id INT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Remember me tokens
CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (token)
);

-- Add to users table
ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN api_token VARCHAR(80) NULL UNIQUE;
```

---

## 📦 Files Created

```
src/Auth/
├── Passwords/
│   ├── PasswordResetToken.php
│   ├── PasswordBroker.php
│   └── PasswordFacade.php
├── EmailVerification/
│   ├── EmailVerificationToken.php
│   ├── MustVerifyEmail.php (interface)
│   └── VerifiesEmails.php (trait)
├── Guards/
│   ├── GuardInterface.php
│   ├── SessionGuard.php
│   └── TokenGuard.php
├── Access/
│   ├── Gate.php
│   ├── Policy.php
│   ├── AuthorizesRequests.php (trait)
│   └── AuthorizationException.php
├── AuthManager.php
├── DatabaseUserProvider.php
└── RememberToken.php

src/Console/Commands/
└── MakePolicyCommand.php

src/helpers.php (updated)
├── auth() - with guard support
├── gate()
├── can()
└── cannot()
```

---

## ✅ Phase 2 Complete

**Progress**: 80% ของ Development Roadmap

**Next Phase**: Infrastructure Enhancements
- Form Request Validation
- API Resources
- Queue Enhancement
- Storage Enhancement
- Mail Enhancement

---

**Completion Date**: November 27, 2025
