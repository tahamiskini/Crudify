<?php

namespace Taha\Crudify\Services\Crud\Relations\Strategy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Taha\Crudify\Services\Crud\Relations\Contract\SyncStrategyContract;

class SyncHasOne implements SyncStrategyContract
{
    public function __invoke(Model $model, string $relationName, array $data): void
    {
        $relation = $this->getRelation($model, $relationName);

        if (isset($data['id'])) {
            $subModel = $relation->find($data['id']);
            if ($subModel) {
                $subModel->fill($data)->save();
                return;
            }
        }

        $relation->updateOrCreate([], $data);
    }

    protected function getRelation(Model $model, string $relationName): HasOne
    {
        return $model->$relationName();
    }
}
