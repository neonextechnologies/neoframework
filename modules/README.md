# Business Modules

This directory contains self-contained business modules following the Modular Monolith architecture.

## Module Structure

Each module should have:

```
modules/blog/
├── BlogModule.php          # Module definition
├── Controllers/            # HTTP Controllers
├── Models/                 # Eloquent Models
├── Services/               # Business logic
├── Repositories/           # Data access layer (optional)
├── Views/                  # Blade templates
├── Migrations/             # Database migrations
├── Tests/                  # Module tests
├── routes.php              # Module routes
├── config.php              # Module configuration
└── module.json             # Module metadata
```

## Creating a Module

```bash
php neo make:module Blog
```

## Module Lifecycle

1. **Register** - Module is discovered and registered
2. **Boot** - Module services are initialized
3. **Active** - Module is running
4. **Disabled** - Module is inactive but installed

## Example Modules

- `blog/` - Blog functionality (posts, comments)
- `shop/` - E-commerce features (products, orders)
- `user/` - User management (profiles, settings)
