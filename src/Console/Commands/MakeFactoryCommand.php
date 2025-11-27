<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

class MakeFactoryCommand extends Command
{
    protected string $signature = 'make:factory {name : The name of the factory class} {--model= : The model class name}';
    protected string $description = 'Create a new model factory class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $modelName = $this->option('model');
        
        // Remove "Factory" suffix if provided
        $name = preg_replace('/Factory$/', '', $name);
        
        // If no model specified, use factory name
        if (!$modelName) {
            $modelName = $name;
        }
        
        $className = $name . 'Factory';
        $filePath = base_path("database/factories/{$className}.php");
        
        if (file_exists($filePath)) {
            $this->error("Factory {$className} already exists!");
            return 1;
        }
        
        $stub = $this->getStub($className, $modelName);
        
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        file_put_contents($filePath, $stub);
        
        $this->info("Factory created successfully: database/factories/{$className}.php");
        
        return 0;
    }

    protected function getStub(string $className, string $modelName): string
    {
        return <<<PHP
<?php

namespace Database\Factories;

use App\Models\\{$modelName};
use NeoPhp\Testing\Factory;

class {$className} extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected string \$model = {$modelName}::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => \$this->randomString(10),
            'email' => \$this->randomEmail(),
            'created_at' => \$this->randomDate(),
        ];
    }
}

PHP;
    }
}
