<?php

namespace Taha\Crudify\Actions\Crud;

use Illuminate\Support\Facades\Validator;
use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\ActionResponse;
use Taha\Crudify\Actions\ExecutableActionResponseContract;

class MassCreateOrUpdate extends MassUpdate
{
    protected function doRun(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        /** @var Create $createAction */
        $createAction = resolve(Create::class);

        /** @var Update $updateAction */
        $updateAction = resolve(Update::class);

        foreach ($actionPayload->getData() as $item) {
            $id = $item['id'] ?? null;

            Validator::make($item, $this->request->rules())->validate();

            $model = $actionPayload->getModel()->newQuery()->findOrNew($id);

            if ($model->exists) {
                $data = $this->mergeModelData($model, $item);

                $crudActionPayload = $this->createPayload($data, $model, $actionPayload);
                $updateAction->run($crudActionPayload);
                continue;
            }

            $crudActionPayload = $this->createPayload($item, $model, $actionPayload);
            $createAction->run($crudActionPayload);
        }

        return new ActionResponse();
    }
}
