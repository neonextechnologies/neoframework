# 🛠️ Development Tools

This guide covers essential development tools, IDEs, extensions, and utilities that enhance your NeoFramework development workflow.

## Table of Contents

- [IDEs and Editors](#ides-and-editors)
- [Editor Extensions](#editor-extensions)
- [Command Line Tools](#command-line-tools)
- [Database Tools](#database-tools)
- [API Testing Tools](#api-testing-tools)
- [Docker and Containers](#docker-and-containers)
- [Version Control](#version-control)
- [Debugging Tools](#debugging-tools)
- [Performance Tools](#performance-tools)
- [Deployment Tools](#deployment-tools)

## 💻 IDEs and Editors

### Visual Studio Code

**Best for**: Modern, lightweight, highly customizable

**Download**: [code.visualstudio.com](https://code.visualstudio.com)

**Recommended Settings**:

```json
{
    "editor.formatOnSave": true,
    "editor.codeActionsOnSave": {
        "source.fixAll": true
    },
    "files.associations": {
        "*.php": "php"
    },
    "php.validate.enable": true,
    "php.suggest.basic": true,
    "[php]": {
        "editor.defaultFormatter": "bmewburn.vscode-intelephense-client"
    }
}
```

**Essential Extensions**:
- PHP Intelephense
- PHP Debug
- PHP CS Fixer
- GitLens
- DotENV
- Better Comments

### PHPStorm

**Best for**: Professional PHP development, advanced features

**Download**: [jetbrains.com/phpstorm](https://jetbrains.com/phpstorm)

**Price**: $199/year (Free for students)

**Features**:
- Intelligent code completion
- Advanced refactoring
- Built-in debugger
- Database tools
- Version control integration
- Code quality analysis

**NeoFramework Configuration**:

1. **Enable Composer autoload**:
   - Settings → PHP → Composer
   - Enable "Synchronize IDE settings with composer.json"

2. **Configure PHP version**:
   - Settings → PHP
   - Add CLI Interpreter: PHP 8.1+

3. **Set up PHPUnit**:
   - Settings → PHP → Test Frameworks
   - Add PHPUnit by Remote Interpreter

4. **Code Style**:
   - Settings → Editor → Code Style → PHP
   - Set to PSR-12

### Sublime Text

**Best for**: Lightweight, fast, minimal setup

**Download**: [sublimetext.com](https://sublimetext.com)

**Essential Packages** (via Package Control):
```
- SublimeLinter
- SublimeLinter-php
- PHPCompanion
- DocBlockr
- Git
- Emmet
```

### Vim/Neovim

**Best for**: Terminal-based development, customization

**PHP Plugins**:
```vim
" .vimrc / init.vim
Plug 'phpactor/phpactor'
Plug 'vim-php/vim-php'
Plug 'stephpy/vim-php-cs-fixer'
Plug 'phpstan/phpstan'
```

## 🔌 Editor Extensions

### Visual Studio Code Extensions

#### PHP Intelephense

**Install**: `bmewburn.vscode-intelephense-client`

Features:
- Intelligent code completion
- Go to definition
- Find references
- Rename symbol
- Format document

**Configuration**:
```json
{
    "intelephense.files.maxSize": 5000000,
    "intelephense.environment.phpVersion": "8.2.0"
}
```

#### PHP Debug (Xdebug)

**Install**: `xdebug.php-debug`

**Setup**:

1. Install Xdebug:
```bash
pecl install xdebug
```

2. Configure php.ini:
```ini
zend_extension=xdebug.so
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_port=9003
```

3. VS Code launch.json:
```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}"
            }
        }
    ]
}
```

#### PHP CS Fixer

**Install**: `junstyle.php-cs-fixer`

Auto-format code on save.

**Configuration**:
```json
{
    "php-cs-fixer.executablePath": "${workspaceFolder}/vendor/bin/php-cs-fixer",
    "php-cs-fixer.onsave": true,
    "php-cs-fixer.config": ".php-cs-fixer.php"
}
```

#### GitLens

**Install**: `eamodio.gitlens`

Supercharge Git integration:
- Blame annotations
- File history
- Branch comparison
- Repository insights

#### DotENV

**Install**: `mikestead.dotenv`

Syntax highlighting for .env files.

#### Better Comments

**Install**: `aaron-bond.better-comments`

Highlight different comment types:
```php
// ! Important
// ? Question
// TODO: Task
// * Highlight
```

#### PHP Namespace Resolver

**Install**: `mehedidracula.php-namespace-resolver`

Automatically import classes and namespaces.

**Usage**: `Ctrl+Alt+I` (Windows/Linux) or `Cmd+Alt+I` (Mac)

### PHPStorm Plugins

#### Laravel Idea

**Install**: Settings → Plugins → Marketplace

Features:
- Route navigation
- Blade support
- Eloquent model insights
- Validation rules

(Works well with NeoFramework due to similarities)

#### PHP Inspections (EA Extended)

**Install**: Settings → Plugins → Marketplace

Static code analysis with 160+ inspections.

#### .env files support

**Install**: Settings → Plugins → Marketplace

Syntax highlighting and validation for .env files.

## ⌨️ Command Line Tools

### Composer

**Install**:
```bash
# Windows (via installer)
# Download from getcomposer.org

# macOS/Linux
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

**Essential Commands**:
```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Add package
composer require vendor/package

# Add dev package
composer require --dev vendor/package

# Remove package
composer remove vendor/package

# Autoload optimization
composer dump-autoload -o
```

**Global Tools**:
```bash
# PHP CS Fixer
composer global require friendsofphp/php-cs-fixer

# PHPStan
composer global require phpstan/phpstan

# Psalm
composer global require vimeo/psalm
```

### Neo CLI

**Built-in Commands**:
```bash
# List all commands
neo list

# Make controller
neo make:controller UserController

# Make model with migration
neo make:model Post --migration

# Make middleware
neo make:middleware AuthMiddleware

# Make service provider
neo make:provider CustomServiceProvider

# Run migrations
neo migrate

# Create migration
neo make:migration create_users_table

# Run seeds
neo db:seed

# Clear cache
neo cache:clear

# Run tests
neo test
```

**Custom Commands**:
```php
// app/Console/Commands/CustomCommand.php
namespace App\Console\Commands;

use Neo\Console\Command;

class CustomCommand extends Command
{
    protected $signature = 'app:custom {argument} {--option=}';
    protected $description = 'Custom command description';
    
    public function handle()
    {
        $arg = $this->argument('argument');
        $opt = $this->option('option');
        
        $this->info('Command executed!');
    }
}
```

### PHP Built-in Server

```bash
# Start development server
php -S localhost:8000 -t public

# With custom router
php -S localhost:8000 -t public public/index.php

# Specify host
php -S 0.0.0.0:8000 -t public
```

### Git

**Essential Commands**:
```bash
# Clone repository
git clone https://github.com/user/repo.git

# Create branch
git checkout -b feature/new-feature

# Stage changes
git add .

# Commit
git commit -m "feat: add new feature"

# Push
git push origin feature/new-feature

# Pull latest changes
git pull origin main

# Rebase
git rebase main
```

**Git Aliases** (~/.gitconfig):
```ini
[alias]
    co = checkout
    br = branch
    ci = commit
    st = status
    lg = log --oneline --graph --decorate
    undo = reset HEAD~1 --soft
```

## 🗄️ Database Tools

### TablePlus

**Best for**: Modern, native database client

**Download**: [tableplus.com](https://tableplus.com)

**Supported Databases**:
- MySQL/MariaDB
- PostgreSQL
- SQLite
- MongoDB
- Redis

**Features**:
- Multiple connections
- Query editor with autocomplete
- Data editing
- Import/export
- SSH tunneling

### MySQL Workbench

**Best for**: MySQL administration and design

**Download**: [mysql.com/products/workbench](https://www.mysql.com/products/workbench/)

**Features**:
- Visual database design
- SQL development
- Database administration
- Performance monitoring

### phpMyAdmin

**Best for**: Web-based MySQL management

**Install**:
```bash
composer require phpmyadmin/phpmyadmin
```

Access via browser at `http://localhost/phpmyadmin`

### DBeaver

**Best for**: Universal database tool

**Download**: [dbeaver.io](https://dbeaver.io)

**Features**:
- Support for 80+ databases
- ER diagrams
- Data transfer
- SQL editor
- Free and open source

### Redis Desktop Manager

**Best for**: Redis GUI client

**Download**: [resp.app](https://resp.app)

Features:
- Key browser
- CLI console
- Data import/export
- Pub/Sub monitoring

### MongoDB Compass

**Best for**: MongoDB GUI

**Download**: [mongodb.com/products/compass](https://www.mongodb.com/products/compass)

Features:
- Visual query builder
- Schema analysis
- Performance monitoring
- Index management

## 🌐 API Testing Tools

### Postman

**Download**: [postman.com](https://www.postman.com)

**Features**:
- Request builder
- Collections
- Environment variables
- Tests and automation
- Mock servers
- API documentation

**NeoFramework Collection Example**:
```json
{
    "info": {
        "name": "NeoFramework API",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/"
    },
    "item": [
        {
            "name": "Get Users",
            "request": {
                "method": "GET",
                "url": "{{base_url}}/api/users",
                "header": [
                    {
                        "key": "Authorization",
                        "value": "Bearer {{token}}"
                    }
                ]
            }
        }
    ]
}
```

### Insomnia

**Download**: [insomnia.rest](https://insomnia.rest)

**Features**:
- Clean interface
- GraphQL support
- Code generation
- Plugin system

### HTTPie

**Install**:
```bash
# macOS
brew install httpie

# Linux
sudo apt install httpie

# Windows
pip install httpie
```

**Usage**:
```bash
# GET request
http GET http://localhost:8000/api/users

# POST request
http POST http://localhost:8000/api/users name="John" email="john@example.com"

# With authentication
http GET http://localhost:8000/api/users Authorization:"Bearer token"

# Form data
http -f POST http://localhost:8000/api/upload file@/path/to/file.jpg
```

### cURL

**Basic Commands**:
```bash
# GET request
curl http://localhost:8000/api/users

# POST with JSON
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com"}'

# With authentication
curl http://localhost:8000/api/users \
  -H "Authorization: Bearer token"

# Upload file
curl -X POST http://localhost:8000/api/upload \
  -F "file=@/path/to/file.jpg"

# Verbose output
curl -v http://localhost:8000/api/users
```

## 🐳 Docker and Containers

### Docker Desktop

**Download**: [docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop)

**NeoFramework Dockerfile**:
```dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000
CMD ["php-fpm"]
```

**docker-compose.yml**:
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    volumes:
      - .:/var/www
    networks:
      - neoframework
    depends_on:
      - mysql
      - redis

  nginx:
    image: nginx:alpine
    ports:
      - "8000:80"
    volumes:
      - .:/var/www
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    networks:
      - neoframework
    depends_on:
      - app

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: neoframework
      MYSQL_ROOT_PASSWORD: secret
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - neoframework

  redis:
    image: redis:alpine
    ports:
      - "6379:6379"
    networks:
      - neoframework

networks:
  neoframework:
    driver: bridge

volumes:
  mysql_data:
```

**Usage**:
```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f app

# Execute command in container
docker-compose exec app php neo migrate
```

### LazyDocker

**Install**:
```bash
# macOS
brew install lazydocker

# Linux
curl https://raw.githubusercontent.com/jesseduffield/lazydocker/master/scripts/install_update_linux.sh | bash
```

Terminal UI for Docker management.

## 🔍 Debugging Tools

### Xdebug

**Install**:
```bash
pecl install xdebug
```

**php.ini Configuration**:
```ini
[xdebug]
zend_extension=xdebug.so
xdebug.mode=debug,coverage
xdebug.start_with_request=yes
xdebug.client_host=localhost
xdebug.client_port=9003
xdebug.log=/tmp/xdebug.log
```

### Ray

**Desktop Debugging App**

**Install**:
```bash
composer require spatie/ray
```

**Download App**: [myray.app](https://myray.app)

**Usage**:
```php
ray('Debug message');
ray($variable)->blue();
ray()->table($array);
ray()->measure(fn() => slow_operation());
```

### Clockwork

**Browser DevTools Extension**

**Install**:
```bash
composer require itsgoingd/clockwork
```

**Browser Extension**:
- Chrome: [Clockwork Chrome Extension](https://chrome.google.com/webstore)
- Firefox: [Clockwork Firefox Add-on](https://addons.mozilla.org)

## ⚡ Performance Tools

### Blackfire

**Download**: [blackfire.io](https://blackfire.io)

PHP profiling tool.

**Install Agent**:
```bash
# Ubuntu/Debian
wget -q -O - https://packages.blackfire.io/gpg.key | sudo apt-key add -
echo "deb http://packages.blackfire.io/debian any main" | sudo tee /etc/apt/sources.list.d/blackfire.list
sudo apt-get update
sudo apt-get install blackfire-agent blackfire-php
```

**Profile Application**:
```bash
blackfire curl http://localhost:8000
```

### Apache Bench (ab)

**Install**: Usually pre-installed on macOS/Linux

**Usage**:
```bash
# 1000 requests, 10 concurrent
ab -n 1000 -c 10 http://localhost:8000/

# With authentication
ab -n 1000 -c 10 -H "Authorization: Bearer token" http://localhost:8000/api/users
```

### wrk

**Install**:
```bash
# macOS
brew install wrk

# Linux
git clone https://github.com/wg/wrk.git
cd wrk
make
sudo cp wrk /usr/local/bin
```

**Usage**:
```bash
# 12 threads, 400 connections, 30s duration
wrk -t12 -c400 -d30s http://localhost:8000/
```

## 🚀 Deployment Tools

### Deployer

**Install**:
```bash
composer require deployer/deployer --dev
```

**deploy.php**:
```php
<?php
namespace Deployer;

require 'recipe/common.php';

set('application', 'neoframework-app');
set('repository', 'git@github.com:user/repo.git');

host('production')
    ->set('remote_user', 'deploy')
    ->set('deploy_path', '/var/www/html');

task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:publish',
]);
```

**Deploy**:
```bash
vendor/bin/dep deploy production
```

### Envoy

**Install**:
```bash
composer global require laravel/envoy
```

**Envoy.blade.php**:
```php
@servers(['web' => 'user@server.com'])

@task('deploy', ['on' => 'web'])
    cd /var/www/html
    git pull origin main
    composer install --no-dev
    php neo migrate
    php neo cache:clear
@endtask
```

**Run**:
```bash
envoy run deploy
```

### GitHub Actions

**.github/workflows/deploy.yml**:
```yaml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          
      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader
        
      - name: Run tests
        run: vendor/bin/phpunit
        
      - name: Deploy
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.SSH_KEY }}
          script: |
            cd /var/www/html
            git pull origin main
            composer install --no-dev
            php neo migrate
```

---

These tools will significantly improve your NeoFramework development workflow and productivity! 🚀
