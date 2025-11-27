<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

/**
 * Make Resource Command
 * 
 * Generate a new API resource class
 */
class MakeResourceCommand extends Command
{
    protected string $signature = 'make:resource {name} {--collection}';
    protected string $description = 'Create a new API resource class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $isCollection = $this->option('collection');

        // Ensure name ends with Resource
        if (!str_ends_with($name, 'Resource')) {
            $name .= 'Resource';
        }

        $path = app()->basePath("app/Http/Resources/{$name}.php");

        if (file_exists($path)) {
            $this->error("Resource already exists: {$path}");
            return 1;
        }

        $stub = $isCollection ? $this->getCollectionStub() : $this->getStub();
        $content = str_replace('{{name}}', $name, $stub);

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, $content);

        $this->info("Resource created successfully: {$path}");

        return 0;
    }

    protected function getStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Http\Resources;

use NeoPhp\Http\Resources\JsonResource;

class {{name}} extends JsonResource
{
    /**
     * Transform the resource into an array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            // Add more fields here
        ];
    }
}
STUB;
    }

    protected function getCollectionStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Http\Resources;

use NeoPhp\Http\Resources\ResourceCollection;

class {{name}} extends ResourceCollection
{
    /**
     * Transform the collection into an array
     */
    public function toArray($request = null): array
    {
        return parent::toArray($request);
    }

    /**
     * Get additional data that should be returned with the collection
     */
    public function with($request): array
    {
        return [
            'meta' => [
                'key' => 'value',
            ],
        ];
    }
}
STUB;
    }
}
