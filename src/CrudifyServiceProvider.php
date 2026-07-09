<?php

namespace Taha\Crudify;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Taha\Crudify\Commands\GenerateRoutesCommand;
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

        $this->registerRouteMacro();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/crudify.php',
            'crudify'
        );

        $this->commands([
            GenerateRoutesCommand::class,
        ]);
    }

    protected function registerRouteMacro(): void
    {
        Route::macro('crud', function (string $resource, string $model, array $options = []) {
            $controller = CrudifyController::class;
            $namespace = $options['namespace'] ?? config('crudify.namespace', 'App');
            $modelName = class_basename($model);

            $def = function (array|string $method, string $action, string $suffix = '') use ($controller, $modelName, $namespace, $resource) {
                $uri = $resource . $suffix;

                $route = Route::match((array) $method, $uri, [$controller, $action]);

                $route->setAction(array_merge(
                    $route->getAction(),
                    ['model' => $modelName, 'namespace' => $namespace]
                ));
            };

            // Mass utility routes (before {id} routes to avoid capture)
            $def('POST', 'massCreate', '/mass-create');
            $def(['PUT', 'PATCH'], 'massUpdate', '/mass-update');
            $def('DELETE', 'massDelete', '/mass-delete');
            $def('POST', 'massCreateOrUpdate', '/mass-create-or-update');

            // Single-resource routes
            $def('GET', 'readMore');
            $def('GET', 'readOne', '/{id}');
            $def('POST', 'create');
            $def(['PUT', 'PATCH'], 'update', '/{id}');
            $def('DELETE', 'delete', '/{id}');

            // Relation routes (param names must match controller: $relationField)
            $def('POST', 'addRelation', '/{id}/add-relation/{relationField}');
            $def('DELETE', 'removeRelation', '/{id}/remove-relation/{relationField}/{relationId?}');
            $def('POST', 'attachRelation', '/{id}/attach-relation/{relationField}/{relationId}');
            $def('DELETE', 'detachRelation', '/{id}/detach-relation/{relationField}/{relationId}');
        });
    }
}
