<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

/**
 * Make Policy Command
 * 
 * Generate a new policy class
 */
class MakePolicyCommand extends Command
{
    protected string $signature = 'make:policy {name} {--model=}';
    protected string $description = 'Create a new policy class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $model = $this->option('model');

        // Ensure name ends with Policy
        if (!str_ends_with($name, 'Policy')) {
            $name .= 'Policy';
        }

        $path = app()->basePath("app/Policies/{$name}.php");

        if (file_exists($path)) {
            $this->error("Policy already exists: {$path}");
            return 1;
        }

        $stub = $this->getStub($model);
        $content = $this->populateStub($stub, $name, $model);

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, $content);

        $this->info("Policy created successfully: {$path}");

        return 0;
    }

    protected function getStub(?string $model): string
    {
        if ($model) {
            return <<<'STUB'
<?php

namespace App\Policies;

use NeoPhp\Auth\Access\Policy;
use App\Models\User;
use App\Models\{{model}};

class {{name}} extends Policy
{
    /**
     * Determine if the user can view any {{modelLower}}
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the {{modelLower}}
     */
    public function view(User $user, {{model}} ${{modelVar}}): bool
    {
        return true;
    }

    /**
     * Determine if the user can create {{modelLower}}
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the {{modelLower}}
     */
    public function update(User $user, {{model}} ${{modelVar}}): bool
    {
        return $this->owns($user, ${{modelVar}});
    }

    /**
     * Determine if the user can delete the {{modelLower}}
     */
    public function delete(User $user, {{model}} ${{modelVar}}): bool
    {
        return $this->owns($user, ${{modelVar}});
    }

    /**
     * Determine if the user can restore the {{modelLower}}
     */
    public function restore(User $user, {{model}} ${{modelVar}}): bool
    {
        return $this->owns($user, ${{modelVar}});
    }

    /**
     * Determine if the user can permanently delete the {{modelLower}}
     */
    public function forceDelete(User $user, {{model}} ${{modelVar}}): bool
    {
        return $this->owns($user, ${{modelVar}});
    }
}
STUB;
        }

        return <<<'STUB'
<?php

namespace App\Policies;

use NeoPhp\Auth\Access\Policy;
use App\Models\User;

class {{name}} extends Policy
{
    /**
     * Determine if the user can perform the action
     */
    public function handle(User $user): bool
    {
        return true;
    }
}
STUB;
    }

    protected function populateStub(string $stub, string $name, ?string $model): string
    {
        $replacements = [
            '{{name}}' => $name,
        ];

        if ($model) {
            $modelVar = lcfirst($model);
            $modelLower = strtolower(preg_replace('/(?<!^)[A-Z]/', ' $0', $model));

            $replacements['{{model}}'] = $model;
            $replacements['{{modelVar}}'] = $modelVar;
            $replacements['{{modelLower}}'] = $modelLower;
        }

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $stub
        );
    }
}
