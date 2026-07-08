<?php

namespace Taha\Crudify\Services\Crud\Relations;

use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\Crud\CrudActionPayload;
use Taha\Crudify\Services\RelationFieldCheckerService;

class RelationDataPayloadService
{
    public function __construct(
        protected RelationFieldCheckerService $relationFieldCheckerService,
    ) {
    }

    public function preparePayload(ActionPayloadInterface $actionPayload): ActionPayloadInterface
    {
        $data = $actionPayload->getData();
        $model = $actionPayload->getModel();
        $additionalData = $actionPayload->getAdditionalData();

        $relatedModel = $this->relationFieldCheckerService->getRelatedModelByField(
            $model,
            $additionalData['relationField']
        );

        $newData = [
            'id' => $data['id'],
            'relation' => $additionalData['relationField'],
            'childFqn' => get_class($relatedModel),
            'parentFqn' => get_class($model),
            'parentId' => $model->getKey(),
        ];

        $newActionPayload = new CrudActionPayload($newData, $model);
        $newActionPayload->setAdditionalData($additionalData);

        return $newActionPayload;
    }
}
