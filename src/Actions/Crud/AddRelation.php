<?php

namespace Taha\Crudify\Actions\Crud;

use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\ActionResponse;
use Taha\Crudify\Actions\ExecutableActionResponseContract;
use Taha\Crudify\Events\CrudModelAfterAddRelation;
use Taha\Crudify\Events\CrudModelBeforeAddRelation;
use Taha\Crudify\Services\Crud\Relations\EntityRelationsService;
use Taha\Crudify\Services\Crud\Relations\RelationDataPayloadService;

class AddRelation extends CrudAction
{
    public function __construct(
        protected EntityRelationsService $entityRelationService,
        protected RelationDataPayloadService $relationDataPayloadService,
    ) {
    }

    public function run(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        $newActionPayload = $this->relationDataPayloadService->preparePayload($actionPayload);

        CrudModelBeforeAddRelation::dispatch($newActionPayload);
        $actionResponse = $this->doRun($newActionPayload);
        CrudModelAfterAddRelation::dispatch($newActionPayload);

        return $actionResponse;
    }

    protected function doRun(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        $data = $actionPayload->getData();
        $model = $actionPayload->getModel();
        $additionalData = $actionPayload->getAdditionalData();

        $this->entityRelationService->addRelation($model, $data, $additionalData);

        return new ActionResponse();
    }
}
