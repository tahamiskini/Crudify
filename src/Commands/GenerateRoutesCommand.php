<?php

namespace Taha\Crudify\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateRoutesCommand extends Command
{
    protected $signature = 'crudify:generate-routes
        {--dir= : Custom directory to scan for models}
        {--output= : Custom output path for the generated routes file}';

    protected $description = 'Scan Models directory and generate CRUD route definitions';

    public function handle(): int
    {
        $modelsDir = $this->option('dir') ?? app_path('Models');

        if (!is_dir($modelsDir)) {
            $this->error("Models directory not found: [{$modelsDir}].");

            return self::FAILURE;
        }

        $files = glob($modelsDir . '/*.php');

        if (empty($files)) {
            $this->warn('No model files found in: ' . $modelsDir);

            return self::SUCCESS;
        }

        $namespace = config('crudify.namespace', 'App');
        $modelsNamespace = $namespace . '\\Models';

        $models = collect($files)
            ->map(fn(string $file) => pathinfo($file, PATHINFO_FILENAME))
            ->sort()
            ->values();

        $routes = $models
            ->map(fn(string $model) => sprintf(
                "        Route::crud('%s', \\%s\\%s::class);",
                Str::kebab(Str::plural($model)),
                $modelsNamespace,
                $model
            ))
            ->implode("\n");

        $outputPath = $this->option('output') ?? base_path('routes/crudify.php');

        $stub = $this->getStub();
        $prefix = config('crudify.routes_prefix', 'api');
        $middlewares = config('crudify.middlewares', []);

        $content = str_replace(
            ['{{ routesPrefix }}', '{{ middlewares }}', '{{ routes }}'],
            [$prefix, $this->formatMiddlewares($middlewares), $routes],
            $stub
        );

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $content);

        $this->info("Crudify routes generated: [{$outputPath}]");
        $this->newLine();
        $this->line('Include it in your route service provider:');
        $this->line("    require __DIR__ . '/crudify.php';");

        return self::SUCCESS;
    }

    protected function getStub(): string
    {
        $stubPath = __DIR__ . '/stubs/crudify-routes.stub';

        if (File::exists($stubPath)) {
            return File::get($stubPath);
        }

        return $this->getDefaultStub();
    }

    protected function formatMiddlewares(array $middlewares): string
    {
        if (empty($middlewares)) {
            return '[]';
        }

        $formatted = collect($middlewares)
            ->map(fn(string $m) => "            '{$m}'")
            ->implode(",\n");

        return "[\n{$formatted}\n        ]";
    }

    protected function getDefaultStub(): string
    {
        return <<<'STUB'
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Crudify Generated Routes
|--------------------------------------------------------------------------
|
| Auto-generated CRUD routes. Regenerate with:
|
|   php artisan crudify:generate-routes
|
*/

Route::prefix('{{ routesPrefix }}')
    ->middleware({{ middlewares }})
    ->group(function () {
{{ routes }}
    });
STUB;
    }
}
