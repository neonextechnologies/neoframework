<?php

namespace NeoPhp\Translation;

/**
 * File Loader
 * 
 * Loads translation files from the file system
 */
class FileLoader
{
    protected string $path;
    protected array $hints = [];

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Load the messages for the given locale
     */
    public function load(string $locale, ?string $group, string $namespace = '*'): array
    {
        if ($namespace !== '*') {
            return $this->loadNamespaced($locale, $group, $namespace);
        }

        return $this->loadPath($this->path, $locale, $group);
    }

    /**
     * Load a namespaced translation group
     */
    protected function loadNamespaced(string $locale, ?string $group, string $namespace): array
    {
        if (isset($this->hints[$namespace])) {
            return $this->loadPath($this->hints[$namespace], $locale, $group);
        }

        return [];
    }

    /**
     * Load a locale from a given path
     */
    protected function loadPath(string $path, string $locale, ?string $group): array
    {
        if ($group === null) {
            return [];
        }

        $full = "{$path}/{$locale}/{$group}.php";

        if (!file_exists($full)) {
            return [];
        }

        return require $full;
    }

    /**
     * Add a new namespace to the loader
     */
    public function addNamespace(string $namespace, string $hint): void
    {
        $this->hints[$namespace] = $hint;
    }

    /**
     * Get the array of translation namespaces
     */
    public function namespaces(): array
    {
        return $this->hints;
    }
}
