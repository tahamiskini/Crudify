<?php

namespace Taha\Crudify\Services\Crud\Relations;

use Illuminate\Database\Eloquent\Model;
use Taha\Crudify\Services\RelationFieldCheckerService;

class EntityRelationsService
{
    public function __construct(
        protected RelationFieldCheckerService $relationFieldCheckerService,
        protected SyncRelationService $syncRelationService,
    ) {
    }

    public function resolveRelationFields(Model $model, array $data): array
    {
        $parsedData = [];
        $parsedRelationData = [];

        foreach ($data as $key => $item) {
            if (!$model->isRelation($key)) {
                $parsedData[$key] = $item;
                continue;
            }

            $foreignKey = $key . '_id';

            $isForeignKeyFillable = $model->isFillable($foreignKey);

            if ($isForeignKeyFillable && $model->isFillable($key . '_type')) {
                $parsedRelationData[$key] = $item;
                continue;
            }

            if ($isForeignKeyFillable) {
                $parsedData[$foreignKey] = (is_array($item) && array_key_exists('id', $item)) ? $item['id'] : $item;
                continue;
            }

            if (!$model->isFillable($key) && $model->isGuarded($foreignKey)) {
                $parsedRelationData[$key] = $item;
            }
        }

        return [$parsedData, $parsedRelationData];
    }

    public function fillRelationships(Model $model, array $data): void
    {
        foreach ($data as $field => $value) {
            $this->syncRelationService->applySync($model, $field, $value);
        }
    }

    public function addRelation(Model $model, array $data, array $params): void
    {
        $this->syncRelationService->createRelation($model, $params['relationField'], $data);
    }

    public function removeRelation(Model $model, array $data, array $params): void
    {
        $this->syncRelationService->deleteRelation($model, $params['relationField'], $data);
    }

    public function attachRelation(Model $model, array $data, array $params): void
    {
        $this->syncRelationService->attachRelation($model, $params['relationField'], $data);
    }

    public function detachRelation(Model $model, array $data, array $params): void
    {
        $this->syncRelationService->detachRelation($model, $params['relationField'], $data);
    }
}
