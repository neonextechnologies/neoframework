<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

/**
 * Make Mail Command
 * 
 * Generate a new mailable class
 */
class MakeMailCommand extends Command
{
    protected string $signature = 'make:mail {name} {--markdown=}';
    protected string $description = 'Create a new mailable class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $markdown = $this->option('markdown');

        $path = app()->basePath("app/Mail/{$name}.php");

        if (file_exists($path)) {
            $this->error("Mail already exists: {$path}");
            return 1;
        }

        $stub = $markdown ? $this->getMarkdownStub() : $this->getStub();
        $replacements = [
            '{{name}}' => $name,
            '{{markdown}}' => $markdown ?? 'emails.default'
        ];

        $content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $stub
        );

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, $content);

        $this->info("Mail created successfully: {$path}");

        // Create view file if markdown option is provided
        if ($markdown) {
            $this->createMarkdownView($markdown);
        }

        return 0;
    }

    protected function getStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Mail;

use NeoPhp\Mail\Mailable;
use NeoPhp\Queue\ShouldQueue;

class {{name}} extends Mailable implements ShouldQueue
{
    /**
     * Create a new message instance
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message
     */
    public function build(): self
    {
        return $this->view('emails.name')
                    ->subject('Subject');
    }
}
STUB;
    }

    protected function getMarkdownStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Mail;

use NeoPhp\Mail\Mailable;
use NeoPhp\Queue\ShouldQueue;

class {{name}} extends Mailable implements ShouldQueue
{
    /**
     * Create a new message instance
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message
     */
    public function build(): self
    {
        return $this->markdown('{{markdown}}')
                    ->subject('Subject');
    }
}
STUB;
    }

    protected function createMarkdownView(string $view): void
    {
        $path = app()->basePath('resources/views/' . str_replace('.', '/', $view) . '.blade.php');
        
        if (file_exists($path)) {
            return;
        }

        $stub = <<<'STUB'
# Hello!

The body of your message.

Thanks,<br>
{{ config('app.name') }}
STUB;

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, $stub);
        
        $this->info("Markdown view created: {$path}");
    }
}
