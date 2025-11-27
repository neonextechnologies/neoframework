<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

/**
 * Make Job Command
 * 
 * Generate a new job class
 */
class MakeJobCommand extends Command
{
    protected string $signature = 'make:job {name} {--sync}';
    protected string $description = 'Create a new job class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $isSync = $this->option('sync');

        // Ensure name ends with Job
        if (!str_ends_with($name, 'Job')) {
            $name .= 'Job';
        }

        $path = app()->basePath("app/Jobs/{$name}.php");

        if (file_exists($path)) {
            $this->error("Job already exists: {$path}");
            return 1;
        }

        $stub = $isSync ? $this->getSyncStub() : $this->getStub();
        $content = str_replace('{{name}}', $name, $stub);

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, $content);

        $this->info("Job created successfully: {$path}");

        return 0;
    }

    protected function getStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Jobs;

use NeoPhp\Queue\Job;

class {{name}} extends Job
{
    /**
     * Create a new job instance
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job
     */
    public function handle(): void
    {
        //
    }

    /**
     * Handle a job failure
     */
    public function failed(\Throwable $exception): void
    {
        //
    }
}
STUB;
    }

    protected function getSyncStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Jobs;

class {{name}}
{
    /**
     * Create a new job instance
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job
     */
    public function handle(): void
    {
        //
    }
}
STUB;
    }
}
