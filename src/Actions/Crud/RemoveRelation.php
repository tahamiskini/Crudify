<?php

namespace Taha\Crudify\Actions\Crud;

use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\ActionResponse;
use Taha\Crudify\Actions\ExecutableActionResponseContract;
use Taha\Crudify\Events\CrudModelAfterRemoveRelation;
use Taha\Crudify\Events\CrudModelBeforeRemoveRelation;
use Taha\Crudify\Services\Crud\Relations\EntityRelationsService;
use Taha\Crudify\Services\Crud\Relations\RelationDataPayloadService;

class RemoveRelation extends CrudAction
{
    public function __construct(
        protected EntityRelationsService $entityRelationService,
        protected RelationDataPayloadService $relationDataPayloadService,
    ) {
    }

    public function run(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        $newActionPayload = $this->relationDataPayloadService->preparePayload($actionPayload);

        CrudModelBeforeRemoveRelation::dispatch($newActionPayload);
        $actionResponse = $this->doRun($newActionPayload);
        CrudModelAfterRemoveRelation::dispatch($newActionPayload);

        return $actionResponse;
    }

    protected function doRun(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        $data = $actionPayload->getData();
        $model = $actionPayload->getModel();
        $additionalData = $actionPayload->getAdditionalData();

        $this->entityRelationService->removeRelation($model, $data, $additionalData);

        return new ActionResponse();
    }
}
