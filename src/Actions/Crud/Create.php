<?php

namespace Taha\Crudify\Actions\Crud;

use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\ActionResponse;
use Taha\Crudify\Actions\ExecutableActionResponseContract;
use Taha\Crudify\Events\CrudModelAfterCreate;
use Taha\Crudify\Events\CrudModelBeforeCreate;

class Create extends CrudAction
{
    public function run(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        CrudModelBeforeCreate::dispatch($actionPayload);

        $actionResponse = $this->doRun($actionPayload);

        CrudModelAfterCreate::dispatch($actionPayload);

        return $actionResponse;
    }

    protected function doRun(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        $model = $actionPayload->getModel();

        $this->saveModel($actionPayload, $actionPayload->getData());

        return new ActionResponse([
            'id' => $model->getKey(),
        ]);
    }
}
