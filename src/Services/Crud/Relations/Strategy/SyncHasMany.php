<?php

namespace Taha\Crudify\Services\Crud\Relations\Strategy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Taha\Crudify\Services\Crud\Relations\Contract\SyncStrategyContract;

class SyncHasMany implements SyncStrategyContract
{
    public function __invoke(Model $model, string $relationName, array $data): void
    {
        $relation = $this->getRelation($model, $relationName);

        $relatedIds = [];
        foreach ($data as $item) {
            if (isset($item['id'])) {
                $subModel = $relation->find($item['id']);
                if ($subModel) {
                    $subModel->fill($item)->save();
                    $relatedIds[] = $item['id'];
                    continue;
                }
            }

            $relation->create($item);
        }

        if (count($relatedIds) > 0) {
            $relation->whereNotIn($relation->getRelated()->getKeyName(), $relatedIds)->delete();
        }
    }

    protected function getRelation(Model $model, string $relationName): HasMany
    {
        return $model->$relationName();
    }
}
