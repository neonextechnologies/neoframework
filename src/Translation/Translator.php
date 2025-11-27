<?php

namespace NeoPhp\Translation;

/**
 * Translator
 * 
 * Handles translations for multi-language support
 */
class Translator
{
    protected string $locale = 'en';
    protected string $fallbackLocale = 'en';
    protected array $loaded = [];
    protected FileLoader $loader;

    public function __construct(FileLoader $loader, string $locale = 'en')
    {
        $this->loader = $loader;
        $this->locale = $locale;
    }

    /**
     * Get the translation for the given key
     */
    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? $this->locale;

        [$namespace, $group, $item] = $this->parseKey($key);

        // Load the translation group
        $this->load($namespace, $group, $locale);

        // Get the line
        $line = $this->getLine($namespace, $group, $item, $locale);

        // If not found, try fallback locale
        if ($line === null && $locale !== $this->fallbackLocale) {
            return $this->get($key, $replace, $this->fallbackLocale);
        }

        // Return key if not found
        if ($line === null) {
            return $key;
        }

        // Replace placeholders
        return $this->makeReplacements($line, $replace);
    }

    /**
     * Get a translation with pluralization
     */
    public function choice(string $key, int $number, array $replace = [], ?string $locale = null): string
    {
        $line = $this->get($key, $replace, $locale);

        // Simple pluralization
        $replace['count'] = $number;

        return $this->makeReplacements($line, $replace);
    }

    /**
     * Parse a translation key
     */
    protected function parseKey(string $key): array
    {
        $namespace = '*';
        $group = null;
        $item = null;

        // Check for namespace
        if (strpos($key, '::') !== false) {
            [$namespace, $key] = explode('::', $key, 2);
        }

        // Parse group and item
        $segments = explode('.', $key);

        if (count($segments) >= 2) {
            $group = $segments[0];
            $item = implode('.', array_slice($segments, 1));
        } else {
            $item = $segments[0];
        }

        return [$namespace, $group, $item];
    }

    /**
     * Load a translation group
     */
    protected function load(string $namespace, ?string $group, string $locale): void
    {
        if ($this->isLoaded($namespace, $group, $locale)) {
            return;
        }

        $lines = $this->loader->load($locale, $group, $namespace);

        $this->loaded[$namespace][$group][$locale] = $lines;
    }

    /**
     * Check if a group is loaded
     */
    protected function isLoaded(string $namespace, ?string $group, string $locale): bool
    {
        return isset($this->loaded[$namespace][$group][$locale]);
    }

    /**
     * Get a translation line
     */
    protected function getLine(string $namespace, ?string $group, string $item, string $locale): ?string
    {
        $line = $this->loaded[$namespace][$group][$locale] ?? [];

        foreach (explode('.', $item) as $segment) {
            if (!is_array($line) || !isset($line[$segment])) {
                return null;
            }

            $line = $line[$segment];
        }

        return is_string($line) ? $line : null;
    }

    /**
     * Make the place-holder replacements
     */
    protected function makeReplacements(string $line, array $replace): string
    {
        foreach ($replace as $key => $value) {
            $line = str_replace(
                [':' . $key, ':' . strtoupper($key), ':' . ucfirst($key)],
                [$value, strtoupper($value), ucfirst($value)],
                $line
            );
        }

        return $line;
    }

    /**
     * Get the current locale
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Set the current locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Get the fallback locale
     */
    public function getFallbackLocale(): string
    {
        return $this->fallbackLocale;
    }

    /**
     * Set the fallback locale
     */
    public function setFallbackLocale(string $locale): void
    {
        $this->fallbackLocale = $locale;
    }

    /**
     * Determine if a translation exists
     */
    public function has(string $key, ?string $locale = null): bool
    {
        $locale = $locale ?? $this->locale;

        [$namespace, $group, $item] = $this->parseKey($key);

        $this->load($namespace, $group, $locale);

        $line = $this->getLine($namespace, $group, $item, $locale);

        return $line !== null;
    }
}
