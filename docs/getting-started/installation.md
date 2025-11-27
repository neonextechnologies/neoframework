# Installation

## Server Requirements

NeoFramework has a few system requirements. Make sure your server meets the following requirements:

- PHP >= 8.0
- PDO PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- JSON PHP Extension
- BCMath PHP Extension (for encryption)

# Installing NeoFramework

## Via Composer

The recommended way to install NeoFramework is through Composer:

```bash
composer create-project neonex/neoframework myapp
```

Or if you're starting with an existing project:

```bash
composer require neonex/neoframework
```

## Manual Installation

You can also manually clone the repository:

```bash
git clone https://github.com/neonextechnologies/neoframework.git myapp
cd myapp
composer install
```

## Configuration

### Environment Configuration

After installing NeoFramework, copy the example environment file:

```bash
cp .env.example .env
```

### Application Key

Generate a unique application key for encryption:

```bash
php neo key:generate
```

### Directory Permissions

Ensure the `storage` and `cache` directories are writable:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## Web Server Configuration

### Apache

NeoFramework includes a `public/.htaccess` file that works out of the box. If it doesn't work, try this configuration:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### Nginx

For Nginx, use this configuration:

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/myapp/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Development Server

For local development, use the built-in PHP server:

```bash
php neo serve
```

This will start a development server at `http://localhost:8000`.

You can customize the host and port:

```bash
php neo serve --host=0.0.0.0 --port=8080
```

## Database Setup

### Configure Database Connection

Edit your `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myapp
DB_USERNAME=root
DB_PASSWORD=secret
```

### Run Migrations

Create the database tables:

```bash
php neo migrate
```

### Seed Database (Optional)

Populate the database with sample data:

```bash
php neo db:seed
```

## Verify Installation

### Check Framework Version

```bash
php neo --version
```

### List Available Commands

```bash
php neo list
```

### Test Your Installation

Create a simple route in `routes/web.php`:

```php
Route::get('/', function() {
    return 'NeoFramework is working!';
});
```

Visit `http://localhost:8000` and you should see the message.

## Next Steps

Now that NeoFramework is installed, you're ready to start building:

- [Configuration](configuration.md) - Learn about configuration
- [Quick Start](quick-start.md) - Build your first app
- [Directory Structure](directory-structure.md) - Understand the structure
- [Routing](../basics/routing.md) - Define your routes

## Troubleshooting

### Common Issues

**Problem: 500 Internal Server Error**

Solution: Check file permissions on `storage/` and `bootstrap/cache/`

```bash
chmod -R 775 storage bootstrap/cache
```

**Problem: Class not found**

Solution: Regenerate the autoloader:

```bash
composer dump-autoload
```

**Problem: Database connection failed**

Solution: Verify your `.env` database credentials and ensure the database exists.

**Problem: Blank page with no errors**

Solution: Enable error display in `.env`:

```env
APP_DEBUG=true
```

### Getting Help

If you encounter any issues:

- Check the [Documentation](../README.md)
- Search [GitHub Issues](https://github.com/neonextechnologies/neoframework/issues)
- Ask in [GitHub Discussions](https://github.com/neonextechnologies/neoframework/discussions)
