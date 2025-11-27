<?php

namespace NeoPhp\Foundation;

use NeoPhp\Foundation\Contracts\ModuleInterface;

/**
 * Base Module Class
 * 
 * All modules should extend this class for common functionality.
 */
abstract class Module implements ModuleInterface
{
    /**
     * Module name
     */
    protected string $name;

    /**
     * Module version
     */
    protected string $version = '1.0.0';

    /**
     * Module description
     */
    protected string $description = '';

    /**
     * Module dependencies
     */
    protected array $dependencies = [];

    /**
     * Module enabled status
     */
    protected bool $enabled = true;

    /**
     * Service providers to register
     */
    protected array $providers = [];

    /**
     * Module path
     */
    protected string $path;

    public function __construct()
    {
        // Auto-detect module path
        $reflection = new \ReflectionClass($this);
        $this->path = dirname($reflection->getFileName());
    }

    /**
     * Get the module name
     */
    public function getName(): string
    {
        return $this->name ?? $this->getDefaultName();
    }

    /**
     * Get default name from class name
     */
    protected function getDefaultName(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();
        return strtolower(str_replace('Module', '', $className));
    }

    /**
     * Get the module version
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get the module description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get module dependencies
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Check if the module is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable the module
     */
    public function enable(): void
    {
        $this->enabled = true;
        $this->onEnable();
    }

    /**
     * Disable the module
     */
    public function disable(): void
    {
        $this->enabled = false;
        $this->onDisable();
    }

    /**
     * Get module path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Register module services
     */
    public function register(): void
    {
        // Register service providers
        foreach ($this->providers as $provider) {
            app()->register($provider);
        }
    }

    /**
     * Bootstrap the module
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutes();
        
        // Load views
        $this->loadViews();
        
        // Load migrations
        $this->loadMigrations();
        
        // Load translations
        $this->loadTranslations();
    }

    /**
     * Load module routes
     */
    protected function loadRoutes(): void
    {
        $routesPath = $this->path . '/routes.php';
        if (file_exists($routesPath)) {
            require $routesPath;
        }
    }

    /**
     * Load module views
     */
    protected function loadViews(): void
    {
        $viewsPath = $this->path . '/Views';
        if (is_dir($viewsPath)) {
            view()->addNamespace($this->getName(), $viewsPath);
        }
    }

    /**
     * Load module migrations
     */
    protected function loadMigrations(): void
    {
        $migrationsPath = $this->path . '/Migrations';
        if (is_dir($migrationsPath)) {
            // Register migrations path
            // Will be implemented with migration system
        }
    }

    /**
     * Load module translations
     */
    protected function loadTranslations(): void
    {
        $langPath = $this->path . '/Lang';
        if (is_dir($langPath)) {
            // Register translations path
            // Will be implemented with translation system
        }
    }

    /**
     * Called when module is enabled
     */
    protected function onEnable(): void
    {
        // Override in child class
    }

    /**
     * Called when module is disabled
     */
    protected function onDisable(): void
    {
        // Override in child class
    }

    /**
     * Get module configuration
     */
    protected function getConfig(string $key, mixed $default = null): mixed
    {
        $configPath = $this->path . '/config.php';
        if (file_exists($configPath)) {
            $config = require $configPath;
            return $config[$key] ?? $default;
        }
        return $default;
    }

    /**
     * Get module metadata from module.json
     */
    protected function getMetadata(): array
    {
        $metadataPath = $this->path . '/module.json';
        if (file_exists($metadataPath)) {
            return json_decode(file_get_contents($metadataPath), true) ?? [];
        }
        return [];
    }
}
