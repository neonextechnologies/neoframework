<?php

namespace NeoPhp\Foundation\Providers;

use NeoPhp\Container\ServiceProvider;
use NeoPhp\Foundation\ModuleRegistry;

/**
 * Module Service Provider
 * 
 * Bootstraps the module system.
 */
class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register module services
     */
    public function register(): void
    {
        // Register module registry as singleton
        $this->app->singleton('modules', function ($app) {
            $registry = new ModuleRegistry();
            
            // Add default module paths
            $registry->addPath(base_path('modules'));
            $registry->addPath(app_path('Modules'));
            
            return $registry;
        });

        // Create alias
        $this->app->alias('modules', ModuleRegistry::class);
    }

    /**
     * Bootstrap module services
     */
    public function boot(): void
    {
        $registry = $this->app->make('modules');
        
        // Auto-discover and register modules
        $registry->autoload();
        
        // Boot all enabled modules
        $registry->boot();
    }
}
