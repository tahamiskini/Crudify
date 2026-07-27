<?php

namespace Taha\Crudify;

use Illuminate\Support\Str;

class ModelHelper
{
    public static function getModelFqn(string $model, string $namespace): string
    {
        return $namespace . '\\Models\\' . static::studly($model);
    }

    public static function getRequestFqn(string $model, string $namespace): string
    {
        return $namespace . '\\Http\\Requests\\' . static::studly($model) . 'Request';
    }

    public static function isNested(string $resource): bool
    {
        return str_contains($resource, '.');
    }

    public static function getParentResource(string $resource): ?string
    {
        if (!static::isNested($resource)) {
            return null;
        }

        return explode('.', $resource)[0];
    }

    public static function getChildResource(string $resource): string
    {
        if (!static::isNested($resource)) {
            return $resource;
        }

        return explode('.', $resource)[1];
    }

    public static function getParentModelFqn(string $resource, string $namespace): string
    {
        return static::getModelFqn(Str::singular(static::getParentResource($resource)), $namespace);
    }

    public static function getParentRouteParam(string $resource): string
    {
        return Str::singular(static::getParentResource($resource));
    }

    public static function getForeignKey(string $parentResource): string
    {
        return Str::singular($parentResource) . '_id';
    }

    protected static function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }
}