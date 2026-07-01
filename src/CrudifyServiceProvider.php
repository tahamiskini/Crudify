<?php

namespace Taha\Crudify;

use Illuminate\Support\ServiceProvider;

class CrudifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/crudify.php' => config_path('crudify.php'),
        ], 'crudify-config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/crudify.php',
            'crudify'
        );
    }
}