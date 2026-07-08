<?php

namespace Taha\Crudify\Services\Crud\Relations\Strategy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Taha\Crudify\Services\Crud\Relations\Contract\SyncStrategyContract;

class CreateHasMany implements SyncStrategyContract
{
    public function __invoke(Model $model, string $relationName, array $data): void
    {
        if (!isset($data['id'])) {
            return;
        }

        $relation = $this->getRelation($model, $relationName);
        $subModelClass = $relation->getRelated();

        $id = $data['id'];

        $subModel = $subModelClass->newModelQuery()->find($id);

        if (!$subModel instanceof Model) {
            $relation->create($data);
            return;
        }

        $subModel->fill($data);
        $relation->save($subModel);
    }

    protected function getRelation(Model $model, string $relationName): HasMany
    {
        return $model->$relationName();
    }
}
