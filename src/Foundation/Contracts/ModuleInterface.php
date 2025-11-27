<?php

namespace NeoPhp\Foundation\Contracts;

/**
 * Module Interface
 * 
 * All modules must implement this interface to be recognized by the framework.
 */
interface ModuleInterface
{
    /**
     * Get the module name
     */
    public function getName(): string;

    /**
     * Get the module version
     */
    public function getVersion(): string;

    /**
     * Get the module description
     */
    public function getDescription(): string;

    /**
     * Get module dependencies (other module names)
     */
    public function getDependencies(): array;

    /**
     * Register module services
     */
    public function register(): void;

    /**
     * Bootstrap the module
     */
    public function boot(): void;

    /**
     * Check if the module is enabled
     */
    public function isEnabled(): bool;

    /**
     * Enable the module
     */
    public function enable(): void;

    /**
     * Disable the module
     */
    public function disable(): void;
}
