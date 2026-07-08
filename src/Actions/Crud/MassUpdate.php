<?php

namespace Taha\Crudify\Actions\Crud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\ActionResponse;
use Taha\Crudify\Actions\ExecutableActionResponseContract;
use Taha\Crudify\CrudifyRequest;
use Taha\Crudify\Services\LoadModelDataMissingFromRequest;

class MassUpdate extends TransactionableAction
{
    public function __construct(
        protected LoadModelDataMissingFromRequest $mergeModelDataToRequest,
        protected CrudifyRequest $request,
    ) {
    }

    protected function doRun(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        /** @var Update $updateAction */
        $updateAction = resolve(Update::class);

        foreach ($actionPayload->getData() as $item) {
            $id = $item['id'] ?? null;

            /** @var Model $model */
            $model = $actionPayload->getModel()->newQuery()->findOrFail($id);

            $data = $this->mergeModelData($model, $item);

            Validator::make($data, $this->request->rules())->validate();

            $crudActionPayload = $this->createPayload($item, $model, $actionPayload);
            $updateAction->run($crudActionPayload);
        }

        return new ActionResponse();
    }

    protected function mergeModelData(Model $model, array $item): array
    {
        if (config('crudify.merge_model_data_to_request') !== true) {
            return $item;
        }

        $modelData = $this->mergeModelDataToRequest->load($model, get_class($this->request));

        return array_merge($modelData, $item);
    }

    protected function createPayload(array $item, Model $model, ActionPayloadInterface $originalActionPayload): ActionPayloadInterface
    {
        $payload = new CrudActionPayload($item, $model);
        $payload->setAllowModelTimestampsOverride($originalActionPayload->getAllowModelTimestampsOverride());

        return $payload;
    }
}
