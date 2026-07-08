<?php

namespace Taha\Crudify\Actions\Crud;

use Illuminate\Database\Eloquent\Model;
use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\ActionResponse;
use Taha\Crudify\Actions\ExecutableActionResponseContract;

class MassDelete extends TransactionableAction
{
    protected function doRun(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        /** @var Delete $deleteAction */
        $deleteAction = resolve(Delete::class);

        foreach ($actionPayload->getData() as $id) {
            /** @var Model $model */
            $model = $actionPayload->getModel()->newQuery()->findOrFail($id);

            $crudActionPayload = new CrudActionPayload([], $model);
            $deleteAction->run($crudActionPayload);
        }

        return new ActionResponse();
    }
}
