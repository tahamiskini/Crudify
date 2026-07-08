<?php

namespace Taha\Crudify\Services\Crud\Relations\Strategy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Taha\Crudify\Services\Crud\Relations\Contract\SyncStrategyContract;

class DetachBelongsToMany implements SyncStrategyContract
{
    public function __invoke(Model $model, string $relationName, array $data): void
    {
        if (!isset($data['id'])) {
            return;
        }

        $relation = $this->getRelation($model, $relationName);
        $relation->detach($data['id']);
    }

    protected function getRelation(Model $model, string $relationName): BelongsToMany
    {
        return $model->$relationName();
    }
}
