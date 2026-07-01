<?php

namespace Taha\Crudify;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class ModelHelper
{
    public static function getModelFqn(string $model, string $namespace): string
    {
        return $namespace . '\\Models\\' . static::studly($model);
    }

    public static function getControllerFqn(string $model, string $namespace): string
    {
        return $namespace . '\\Http\\Controllers\\' . static::studly($model) . 'Controller';
    }

    public static function getRequestFqn(string $model, string $namespace): string
    {
        return $namespace . '\\Http\\Requests\\' . static::studly($model) . 'Request';
    }

    protected static function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }
}