<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

class MakeTestCommand extends Command
{
    protected string $signature = 'make:test {name : The name of the test class}';
    protected string $description = 'Create a new test class';

    public function handle(): int
    {
        $name = $this->argument('name');
        
        // Remove "Test" suffix if provided
        $name = preg_replace('/Test$/', '', $name);
        
        // Determine test type
        $isUnit = str_contains(strtolower($name), 'unit');
        $testDir = $isUnit ? 'Unit' : 'Feature';
        
        $className = $name . 'Test';
        $filePath = base_path("tests/{$testDir}/{$className}.php");
        
        if (file_exists($filePath)) {
            $this->error("Test {$className} already exists!");
            return 1;
        }
        
        $stub = $this->getStub($className);
        
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        file_put_contents($filePath, $stub);
        
        $this->info("Test created successfully: tests/{$testDir}/{$className}.php");
        
        return 0;
    }

    protected function getStub(string $className): string
    {
        return <<<PHP
<?php

use NeoPhp\Testing\TestCase;

class {$className} extends TestCase
{
    /**
     * Test basic functionality.
     */
    public function test_example(): void
    {
        \$this->assertTrue(true);
    }
}

PHP;
    }
}
