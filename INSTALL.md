# 🚀 NeoFramework Installation Guide

## Quick Installation

### Via Composer (Recommended)

Install NeoFramework using Composer's `create-project` command:

```bash
composer create-project neonex/neoframework my-project
cd my-project
php neo serve
```

That's it! Your NeoFramework application is ready at `http://localhost:8000`

---

## Installation Methods

### Method 1: Composer Create-Project (Easiest)

```bash
# Create new project
composer create-project neonex/neoframework blog

# Navigate to project
cd blog

# Configure environment (optional - already done automatically)
cp .env.example .env

# Generate application key (optional - already done automatically)
php neo app:key

# Run migrations
php neo migrate

# Start development server
php neo serve
```

### Method 2: Clone from GitHub

```bash
# Clone repository
git clone https://github.com/neonextechnologies/neoframework.git my-project

# Navigate to project
cd my-project

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php neo app:key

# Run migrations
php neo migrate

# Start development server
php neo serve
```

### Method 3: Download ZIP

1. Download latest release from [GitHub Releases](https://github.com/neonextechnologies/neoframework/releases)
2. Extract to your project directory
3. Run installation commands:

```bash
composer install
cp .env.example .env
php neo app:key
php neo migrate
php neo serve
```

---

## System Requirements

- PHP 8.0 to 8.4
- Composer 2.0+
- PHP Extensions:
  - PDO (MySQL/PostgreSQL/SQLite)
  - mbstring
  - OpenSSL
  - JSON
  - cURL

### Database Support

- MySQL 5.7+
- PostgreSQL 10+
- SQLite 3.8+
- SQL Server 2017+

---

## Post-Installation Setup

### 1. Configure Database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=neoframework
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Run Migrations

```bash
php neo migrate
```

### 3. Seed Database (Optional)

```bash
php neo db:seed
```

### 4. Start Development Server

```bash
php neo serve

# Or specify port
php neo serve --port=8080

# Or specify host
php neo serve --host=0.0.0.0 --port=8000
```

---

## Quick Start Examples

### Create a New Controller

```bash
php neo make:controller PostController --resource
```

### Create a New Model

```bash
php neo make:model Post --migration --factory
```

### Generate Complete CRUD

```bash
php neo make:crud Post --api --test
```

### Run Tests

```bash
php neo test
# or
vendor/bin/phpunit
```

---

## Web Server Configuration

### Apache

Create `.htaccess` in `public/` directory (already included):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

### Nginx

Add to your Nginx configuration:

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/neoframework/public;

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
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Docker Installation

### Using Docker Compose

Create `docker-compose.yml`:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8000:8000"
    volumes:
      - .:/var/www/html
    environment:
      - DB_CONNECTION=mysql
      - DB_HOST=db
      - DB_DATABASE=neoframework
      - DB_USERNAME=root
      - DB_PASSWORD=secret
    depends_on:
      - db

  db:
    image: mysql:8.0
    ports:
      - "3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: neoframework
    volumes:
      - dbdata:/var/lib/mysql

volumes:
  dbdata:
```

Run with Docker:

```bash
docker-compose up -d
docker-compose exec app php neo migrate
```

---

## Troubleshooting

### Permission Issues

```bash
# Linux/Mac
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Set ownership
chown -R www-data:www-data storage bootstrap/cache
```

### Composer Memory Limit

```bash
php -d memory_limit=-1 /usr/local/bin/composer install
```

### Clear Cache

```bash
php neo cache:clear
php neo config:clear
php neo route:clear
php neo view:clear
```

### Regenerate Autoload

```bash
composer dump-autoload
```

---

## Environment-Specific Setup

### Development

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

---

## Next Steps

1. ✅ Read the [Quick Start Guide](docs/getting-started/quick-start.md)
2. ✅ Explore [Documentation](https://docs.neoframework.io)
3. ✅ Join our [Community](https://discord.gg/neoframework)
4. ✅ Check out [Example Projects](https://github.com/neonextechnologies/neoframework-examples)

---

## Getting Help

- 📚 [Documentation](https://docs.neoframework.io)
- 💬 [Discord Community](https://discord.gg/neoframework)
- 🐛 [Issue Tracker](https://github.com/neonextechnologies/neoframework/issues)
- 📧 [Email Support](mailto:support@neonex.dev)

---

## License

NeoFramework is open-source software licensed under the [MIT license](LICENSE).
