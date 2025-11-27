<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

/**
 * Module Disable Command
 * 
 * Disable an enabled module
 */
class ModuleDisableCommand extends Command
{
    protected string $signature = 'module:disable {name : The name of the module}';
    protected string $description = 'Disable a module';

    public function handle(): int
    {
        $name = $this->argument(0);
        
        if (empty($name)) {
            $this->error('Module name is required!');
            return 1;
        }

        $registry = app('modules');

        if (!$registry->has($name)) {
            $this->error("Module '{$name}' not found!");
            return 1;
        }

        $module = $registry->get($name);

        if (!$module->isEnabled()) {
            $this->warn("Module '{$name}' is already disabled.");
            return 0;
        }

        try {
            $registry->disable($name);
            $this->success("Module '{$name}' has been disabled!");
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to disable module '{$name}': " . $e->getMessage());
            return 1;
        }
    }
}
