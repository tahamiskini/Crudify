<?php

namespace Taha\Crudify\Services;

use Illuminate\Database\Eloquent\Model;
use Taha\Crudify\CrudifyRequest;

class LoadModelDataMissingFromRequest
{
    public function load(Model $model, string $requestClassFqn): array
    {
        /** @var CrudifyRequest $requestClass */
        $requestClass = new $requestClassFqn;

        $relations = [];
        foreach (array_keys($requestClass->rules()) as $fieldName) {
            if ($model->isRelation($fieldName)) {
                $relations[] = $fieldName;
            }
        }

        if (count($relations) > 0) {
            $model->load($relations);
        }

        return $model->toArray();
    }
}
