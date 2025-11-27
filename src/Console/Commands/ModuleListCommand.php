<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

/**
 * Module List Command
 * 
 * List all registered modules
 */
class ModuleListCommand extends Command
{
    protected string $signature = 'module:list';
    protected string $description = 'List all registered modules';

    public function handle(): int
    {
        $registry = app('modules');
        $modules = $registry->all();

        if (empty($modules)) {
            $this->warn('No modules found.');
            return 0;
        }

        $this->line('');
        $this->info('Registered Modules:');
        $this->line('');

        // Table header
        $this->table(
            ['Name', 'Version', 'Status', 'Description'],
            $this->formatModules($modules)
        );

        $this->line('');
        $this->line('Total: ' . count($modules) . ' module(s)');
        $this->line('Enabled: ' . count($registry->enabled()) . ' module(s)');
        $this->line('Disabled: ' . count($registry->disabled()) . ' module(s)');

        return 0;
    }

    protected function formatModules(array $modules): array
    {
        $formatted = [];

        foreach ($modules as $module) {
            $formatted[] = [
                $module->getName(),
                $module->getVersion(),
                $module->isEnabled() ? '<green>Enabled</green>' : '<red>Disabled</red>',
                $module->getDescription() ?: '-'
            ];
        }

        return $formatted;
    }
}
