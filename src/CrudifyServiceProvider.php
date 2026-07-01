<?php

namespace Taha\Crudify;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Taha\Crudify\Policies\CrudifyPolicy;

class CrudifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/crudify.php' => config_path('crudify.php'),
        ], 'crudify-config');

        Gate::guessPolicyNamesUsing(function (string $modelClass) {
            $policyClass = str_replace('Models', 'Policies', $modelClass) . 'Policy';

            if (class_exists($policyClass)) {
                return $policyClass;
            }

            return CrudifyPolicy::class;
        });
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/crudify.php',
            'crudify'
        );
    }
}
