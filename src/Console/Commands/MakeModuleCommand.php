<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

/**
 * Make Module Command
 * 
 * Generate a new module structure
 */
class MakeModuleCommand extends Command
{
    protected string $signature = 'make:module {name : The name of the module}';
    protected string $description = 'Create a new module';

    public function handle(): int
    {
        $name = $this->argument(0);
        
        if (empty($name)) {
            $this->error('Module name is required!');
            return 1;
        }

        // Normalize module name
        $moduleName = ucfirst($name);
        $modulePath = base_path("modules/{$name}");

        // Check if module already exists
        if (is_dir($modulePath)) {
            $this->error("Module '{$name}' already exists!");
            return 1;
        }

        $this->info("Creating module: {$moduleName}");

        // Create module structure
        $this->createModuleStructure($modulePath, $name, $moduleName);

        $this->success("Module '{$moduleName}' created successfully!");
        $this->line('');
        $this->line("Location: {$modulePath}");
        $this->line('');
        $this->line('Next steps:');
        $this->line("  1. Edit modules/{$name}/{$moduleName}Module.php");
        $this->line("  2. Add controllers in modules/{$name}/Controllers/");
        $this->line("  3. Add models in modules/{$name}/Models/");
        $this->line("  4. Define routes in modules/{$name}/routes.php");

        return 0;
    }

    protected function createModuleStructure(string $path, string $name, string $moduleName): void
    {
        // Create directories
        $directories = [
            $path,
            "{$path}/Controllers",
            "{$path}/Models",
            "{$path}/Services",
            "{$path}/Views",
            "{$path}/Migrations",
            "{$path}/Tests",
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        // Create module class
        $this->createModuleClass($path, $name, $moduleName);

        // Create module.json
        $this->createModuleMetadata($path, $name, $moduleName);

        // Create config.php
        $this->createModuleConfig($path, $name);

        // Create routes.php
        $this->createModuleRoutes($path, $name);

        // Create README.md
        $this->createModuleReadme($path, $name, $moduleName);

        // Create .gitkeep files
        $this->createGitkeepFiles($path);
    }

    protected function createModuleClass(string $path, string $name, string $moduleName): void
    {
        $namespace = "Modules\\" . $moduleName;
        $className = "{$moduleName}Module";

        $content = <<<PHP
<?php

namespace {$namespace};

use NeoPhp\Foundation\Module;

/**
 * {$moduleName} Module
 * 
 * @package {$namespace}
 */
class {$className} extends Module
{
    /**
     * Module name
     */
    protected string \$name = '{$name}';

    /**
     * Module version
     */
    protected string \$version = '1.0.0';

    /**
     * Module description
     */
    protected string \$description = '{$moduleName} module for NeoFramework';

    /**
     * Module dependencies
     */
    protected array \$dependencies = [];

    /**
     * Service providers to register
     */
    protected array \$providers = [];

    /**
     * Register module services
     */
    public function register(): void
    {
        parent::register();

        // Register your services here
    }

    /**
     * Bootstrap the module
     */
    public function boot(): void
    {
        parent::boot();

        // Bootstrap your module here
    }
}

PHP;

        file_put_contents("{$path}/{$className}.php", $content);
    }

    protected function createModuleMetadata(string $path, string $name, string $moduleName): void
    {
        $metadata = [
            'name' => $name,
            'display_name' => $moduleName,
            'version' => '1.0.0',
            'description' => "{$moduleName} module for NeoFramework",
            'author' => 'Your Name',
            'keywords' => [$name],
            'requires' => [],
            'autoload' => [
                'psr-4' => [
                    "Modules\\{$moduleName}\\" => '/'
                ]
            ]
        ];

        file_put_contents(
            "{$path}/module.json",
            json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    protected function createModuleConfig(string $path, string $name): void
    {
        $content = <<<PHP
<?php

/**
 * {$name} module configuration
 */
return [
    'enabled' => true,
    
    // Add your configuration here
];

PHP;

        file_put_contents("{$path}/config.php", $content);
    }

    protected function createModuleRoutes(string $path, string $name): void
    {
        $content = <<<'PHP'
<?php

/**
 * Module Routes
 * 
 * Define your module routes here
 */

use NeoPhp\Routing\Route;

// Example route
// Route::get('/example', [ExampleController::class, 'index']);

PHP;

        file_put_contents("{$path}/routes.php", $content);
    }

    protected function createModuleReadme(string $path, string $name, string $moduleName): void
    {
        $content = <<<MD
# {$moduleName} Module

{$moduleName} module for NeoFramework.

## Installation

This module is automatically discovered by NeoFramework.

## Usage

Add your usage instructions here.

## Configuration

Edit `config.php` to configure this module.

## Routes

Define your routes in `routes.php`.

## License

MIT

MD;

        file_put_contents("{$path}/README.md", $content);
    }

    protected function createGitkeepFiles(string $path): void
    {
        $dirs = ['Controllers', 'Models', 'Services', 'Views', 'Migrations', 'Tests'];
        
        foreach ($dirs as $dir) {
            file_put_contents("{$path}/{$dir}/.gitkeep", '');
        }
    }
}

PHP;
