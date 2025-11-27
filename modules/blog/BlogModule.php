<?php

namespace Modules\Blog;

use NeoPhp\Foundation\Module;

/**
 * Blog Module
 * 
 * Example module demonstrating NeoFramework modular architecture
 * 
 * @package Modules\Blog
 */
class BlogModule extends Module
{
    /**
     * Module name
     */
    protected string $name = 'blog';

    /**
     * Module version
     */
    protected string $version = '1.0.0';

    /**
     * Module description
     */
    protected string $description = 'Blog module with posts and comments functionality';

    /**
     * Module dependencies
     */
    protected array $dependencies = [];

    /**
     * Service providers to register
     */
    protected array $providers = [];

    /**
     * Register module services
     */
    public function register(): void
    {
        parent::register();

        // Register blog services here
        // Example: app()->singleton(BlogService::class);
    }

    /**
     * Bootstrap the module
     */
    public function boot(): void
    {
        parent::boot();

        // Module is now booted and ready
        // Routes, views, and migrations are loaded automatically
    }
}
