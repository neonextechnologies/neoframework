<?php

namespace NeoPhp\Foundation;

use NeoPhp\Foundation\Contracts\ModuleInterface;

/**
 * Module Registry
 * 
 * Manages module discovery, registration, and lifecycle.
 */
class ModuleRegistry
{
    /**
     * Registered modules
     */
    protected array $modules = [];

    /**
     * Booted modules
     */
    protected array $booted = [];

    /**
     * Module paths to scan
     */
    protected array $paths = [];

    /**
     * Cache file path
     */
    protected string $cacheFile;

    public function __construct()
    {
        $this->cacheFile = storage_path('cache/modules.php');
    }

    /**
     * Add path to scan for modules
     */
    public function addPath(string $path): void
    {
        $this->paths[] = $path;
    }

    /**
     * Discover modules in registered paths
     */
    public function discover(): array
    {
        // Try to load from cache first
        if ($this->canUseCache()) {
            return $this->loadFromCache();
        }

        $discovered = [];

        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            // Scan for *Module.php files
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $fileName = $file->getFilename();
                    
                    // Look for files ending with Module.php
                    if (str_ends_with($fileName, 'Module.php')) {
                        $modulePath = $file->getPath();
                        $discovered[] = [
                            'file' => $file->getPathname(),
                            'path' => $modulePath,
                            'name' => basename($modulePath)
                        ];
                    }
                }
            }
        }

        // Cache the discovery
        $this->saveToCache($discovered);

        return $discovered;
    }

    /**
     * Register a module
     */
    public function register(ModuleInterface $module): void
    {
        $name = $module->getName();
        
        if (isset($this->modules[$name])) {
            throw new \RuntimeException("Module '{$name}' is already registered.");
        }

        $this->modules[$name] = $module;

        // Register module services
        if ($module->isEnabled()) {
            $module->register();
        }
    }

    /**
     * Boot all registered modules
     */
    public function boot(): void
    {
        foreach ($this->modules as $name => $module) {
            if ($module->isEnabled() && !isset($this->booted[$name])) {
                $this->bootModule($module);
            }
        }
    }

    /**
     * Boot a specific module
     */
    protected function bootModule(ModuleInterface $module): void
    {
        $name = $module->getName();

        // Check dependencies
        $this->checkDependencies($module);

        // Boot the module
        $module->boot();

        $this->booted[$name] = true;
    }

    /**
     * Check module dependencies
     */
    protected function checkDependencies(ModuleInterface $module): void
    {
        foreach ($module->getDependencies() as $dependency) {
            if (!$this->has($dependency)) {
                throw new \RuntimeException(
                    "Module '{$module->getName()}' requires '{$dependency}' module."
                );
            }

            if (!$this->get($dependency)->isEnabled()) {
                throw new \RuntimeException(
                    "Module '{$module->getName()}' requires '{$dependency}' module to be enabled."
                );
            }
        }
    }

    /**
     * Get a module by name
     */
    public function get(string $name): ?ModuleInterface
    {
        return $this->modules[$name] ?? null;
    }

    /**
     * Check if a module exists
     */
    public function has(string $name): bool
    {
        return isset($this->modules[$name]);
    }

    /**
     * Get all registered modules
     */
    public function all(): array
    {
        return $this->modules;
    }

    /**
     * Get all enabled modules
     */
    public function enabled(): array
    {
        return array_filter($this->modules, fn($module) => $module->isEnabled());
    }

    /**
     * Get all disabled modules
     */
    public function disabled(): array
    {
        return array_filter($this->modules, fn($module) => !$module->isEnabled());
    }

    /**
     * Enable a module
     */
    public function enable(string $name): void
    {
        if (!$this->has($name)) {
            throw new \RuntimeException("Module '{$name}' not found.");
        }

        $module = $this->get($name);
        $module->enable();

        // Register and boot if not already
        if (!isset($this->booted[$name])) {
            $module->register();
            $this->bootModule($module);
        }

        $this->clearCache();
    }

    /**
     * Disable a module
     */
    public function disable(string $name): void
    {
        if (!$this->has($name)) {
            throw new \RuntimeException("Module '{$name}' not found.");
        }

        $module = $this->get($name);
        $module->disable();

        unset($this->booted[$name]);

        $this->clearCache();
    }

    /**
     * Check if cache can be used
     */
    protected function canUseCache(): bool
    {
        if (!file_exists($this->cacheFile)) {
            return false;
        }

        // Check if cache is fresh (less than 1 hour old)
        $cacheTime = filemtime($this->cacheFile);
        return (time() - $cacheTime) < 3600;
    }

    /**
     * Load modules from cache
     */
    protected function loadFromCache(): array
    {
        return require $this->cacheFile;
    }

    /**
     * Save modules to cache
     */
    protected function saveToCache(array $modules): void
    {
        $cacheDir = dirname($this->cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $export = var_export($modules, true);
        file_put_contents($this->cacheFile, "<?php\n\nreturn {$export};\n");
    }

    /**
     * Clear module cache
     */
    public function clearCache(): void
    {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    /**
     * Auto-load and register modules from paths
     */
    public function autoload(): void
    {
        $discovered = $this->discover();

        foreach ($discovered as $info) {
            // Require the module file
            require_once $info['file'];

            // Try to instantiate the module
            $className = $this->guessClassName($info);
            
            if (class_exists($className)) {
                $module = new $className();
                
                if ($module instanceof ModuleInterface) {
                    $this->register($module);
                }
            }
        }
    }

    /**
     * Guess module class name from file info
     */
    protected function guessClassName(array $info): string
    {
        // Assuming namespace like Modules\Blog\BlogModule
        $moduleName = $info['name'];
        $className = ucfirst($moduleName) . 'Module';
        
        return "Modules\\{$moduleName}\\{$className}";
    }
}
