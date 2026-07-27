<?php

namespace Taha\Crudify\Services\Crud\Relations;

use Illuminate\Database\Eloquent\Model;

class EntityRelationsService
{
    public function __construct(
        protected SyncRelationService $syncRelationService,
    ) {
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
