<?php

namespace Taha\Crudify\Services\Crud\Relations\Strategy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Taha\Crudify\Services\Crud\Relations\Contract\SyncStrategyContract;

class SyncBelongsToMany implements SyncStrategyContract
{
    public function __invoke(Model $model, string $relationName, array $data): void
    {
        $relation = $this->getRelation($model, $relationName);

        $syncData = [];
        foreach ($data as $item) {
            $id = $item['id'] ?? $item;
            $pivot = $item['pivot'] ?? [];
            $syncData[$id] = $pivot;
        }

        $relation->sync($syncData);
    }

    protected function getRelation(Model $model, string $relationName): BelongsToMany
    {
        return $model->$relationName();
    }
}
