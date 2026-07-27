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
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/crudify.php',
            'crudify'
        );

        $this->registerRouteMacro();

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

            $isNested = ModelHelper::isNested($resource);

            $parentModelClass = null;
            $parentParam = null;
            $foreignKey = null;
            $prefix = '';

            if ($isNested) {
                $parentResource = ModelHelper::getParentResource($resource);
                $parentModelClass = $options['parent_model'] ?? ModelHelper::getParentModelFqn($resource, $namespace);
                $parentParam = ModelHelper::getParentRouteParam($resource);
                $foreignKey = $options['foreign_key'] ?? ModelHelper::getForeignKey($parentResource);
                $childResource = ModelHelper::getChildResource($resource);
                $prefix = '{' . $parentParam . '}/';
            } else {
                $parentResource = null;
                $childResource = $resource;
            }

            $def = function (array|string $method, string $action, string $suffix = '') use ($controller, $modelName, $namespace, $childResource, $isNested, $parentModelClass, $parentParam, $foreignKey, $prefix, $parentResource) {
                $uri = $prefix . $childResource . $suffix;

                $route = Route::match((array) $method, $uri, [$controller, $action]);

                $actionData = [
                    'model' => $modelName,
                    'namespace' => $namespace,
                ];

                if ($isNested) {
                    $actionData['parentModel'] = $parentModelClass;
                    $actionData['parentParam'] = $parentParam;
                    $actionData['foreignKey'] = $foreignKey;
                    $actionData['parentResource'] = $parentResource;
                }

                $route->setAction(array_merge(
                    $route->getAction(),
                    $actionData
                ));
            };

            // Mass utility routes (before {id} routes to avoid capture)
            $def('POST', 'massCreate', '/mass-create');
            $def(['PUT', 'PATCH'], 'massUpdate', '/mass-update');
            $def('DELETE', 'massDelete', '/mass-delete');
            $def('POST', 'massCreateOrUpdate', '/mass-create-or-update');

            // Soft-delete routes (before {id} routes to avoid capture)
            $def('POST', 'restore', '/restore/{id}');
            $def('DELETE', 'forceDelete', '/force-delete/{id}');

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
