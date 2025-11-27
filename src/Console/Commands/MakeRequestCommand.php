<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

/**
 * Make Request Command
 * 
 * Generate a new form request class
 */
class MakeRequestCommand extends Command
{
    protected string $signature = 'make:request {name}';
    protected string $description = 'Create a new form request class';

    public function handle(): int
    {
        $name = $this->argument('name');

        // Ensure name ends with Request
        if (!str_ends_with($name, 'Request')) {
            $name .= 'Request';
        }

        $path = app()->basePath("app/Http/Requests/{$name}.php");

        if (file_exists($path)) {
            $this->error("Request already exists: {$path}");
            return 1;
        }

        $stub = $this->getStub();
        $content = str_replace('{{name}}', $name, $stub);

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, $content);

        $this->info("Request created successfully: {$path}");

        return 0;
    }

    protected function getStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Http\Requests;

use NeoPhp\Http\FormRequest;

class {{name}} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            //
        ];
    }

    /**
     * Get custom messages for validator errors
     */
    public function messages(): array
    {
        return [
            //
        ];
    }
}
STUB;
    }
}
