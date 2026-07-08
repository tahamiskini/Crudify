<?php

namespace Taha\Crudify\Services\Crud\Relations\Strategy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Taha\Crudify\Services\Crud\Relations\Contract\SyncStrategyContract;

class SyncMorphTo implements SyncStrategyContract
{
    public function __invoke(Model $model, string $relationName, array $data): void
    {
        $relation = $this->getRelation($model, $relationName);

        $model->{$relation->getForeignKeyName()} = $data['id'] ?? $data;
        $model->{$relation->getMorphType()} = $data['type'] ?? null;
        $model->save();
    }

    protected function getRelation(Model $model, string $relationName): MorphTo
    {
        return $model->$relationName();
    }
}
