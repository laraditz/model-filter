<?php

declare(strict_types=1);

namespace Laraditz\ModelFilter\Console;

use Illuminate\Console\Command;

class MakeFilterCommand extends Command
{
    protected $signature = 'make:filter {name : The name of the filter class} {--force : Overwrite the filter if it already exists}';

    protected $description = 'Create a new filter class';

    public function handle(): int
    {
        /** @var string $name */
        $name = $this->argument('name');

        // Strip .php suffix
        if (str_ends_with($name, '.php')) {
            $name = substr($name, 0, -4);
        }

        // Reject path separators
        if (str_contains($name, '/') || str_contains($name, '\\')) {
            $this->components->error('Filter name must not contain path separators.');
            return self::FAILURE;
        }

        $path = app_path('Filters/' . $name . '.php');

        // Guard against overwrite
        if (file_exists($path) && ! $this->option('force')) {
            $this->components->error('Filter [' . $this->relativePath($path) . '] already exists.');
            return self::FAILURE;
        }

        // Ensure directory exists
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0755, true)) {
            $this->components->error('Could not create filter file.');
            return self::FAILURE;
        }

        // Read stub and replace placeholders
        $stub    = (string) file_get_contents(__DIR__ . '/../stubs/filter.stub');
        $content = str_replace(['{{ namespace }}', '{{ class }}'], ['App\\Filters', $name], $stub);

        if (file_put_contents($path, $content) === false) {
            $this->components->error('Could not create filter file.');
            return self::FAILURE;
        }

        $this->components->info('Filter [' . $this->relativePath($path) . '] created successfully.');
        return self::SUCCESS;
    }

    private function relativePath(string $path): string
    {
        return ltrim(str_replace(base_path(), '', $path), '/\\');
    }
}
