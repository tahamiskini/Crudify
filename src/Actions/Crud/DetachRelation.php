<?php

namespace Taha\Crudify\Actions\Crud;

use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\ActionResponse;
use Taha\Crudify\Actions\ExecutableActionResponseContract;
use Taha\Crudify\Events\CrudModelAfterDetachRelation;
use Taha\Crudify\Events\CrudModelBeforeDetachRelation;
use Taha\Crudify\Services\Crud\Relations\EntityRelationsService;
use Taha\Crudify\Services\Crud\Relations\RelationDataPayloadService;

class DetachRelation extends CrudAction
{
    public function __construct(
        protected EntityRelationsService $entityRelationService,
        protected RelationDataPayloadService $relationDataPayloadService,
    ) {
    }

    public function run(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        $newActionPayload = $this->relationDataPayloadService->preparePayload($actionPayload);

        CrudModelBeforeDetachRelation::dispatch($newActionPayload);
        $actionResponse = $this->doRun($newActionPayload);
        CrudModelAfterDetachRelation::dispatch($newActionPayload);

        return $actionResponse;
    }

    protected function doRun(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        $data = $actionPayload->getData();
        $model = $actionPayload->getModel();
        $additionalData = $actionPayload->getAdditionalData();

        $this->entityRelationService->detachRelation($model, $data, $additionalData);

        return new ActionResponse();
    }
}
