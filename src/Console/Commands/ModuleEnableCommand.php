<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

/**
 * Module Enable Command
 * 
 * Enable a disabled module
 */
class ModuleEnableCommand extends Command
{
    protected string $signature = 'module:enable {name : The name of the module}';
    protected string $description = 'Enable a module';

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

        if ($module->isEnabled()) {
            $this->warn("Module '{$name}' is already enabled.");
            return 0;
        }

        try {
            $registry->enable($name);
            $this->success("Module '{$name}' has been enabled!");
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to enable module '{$name}': " . $e->getMessage());
            return 1;
        }
    }
}
