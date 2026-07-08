<?php

namespace Taha\Crudify\Services\Crud\Relations\Strategy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Taha\Crudify\Services\Crud\Relations\Contract\SyncStrategyContract;

class DetachHasMany implements SyncStrategyContract
{
    public function __invoke(Model $model, string $relationName, array $data): void
    {
        $relation = $this->getRelation($model, $relationName);

        $id = $data['id'] ?? null;

        /** @var Model $subModel */
        $subModel = $relation->findOrFail($id);
        $subModel->setAttribute($relation->getForeignKeyName(), null);
        $subModel->save();
    }

    protected function getRelation(Model $model, string $relationName): HasMany
    {
        return $model->$relationName();
    }
}
