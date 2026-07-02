<?php

namespace Taha\Crudify\Actions\Crud;

use Taha\Crudify\Actions\ExecutableAction;

abstract class CrudAction implements ExecutableAction
{
    protected function saveModel(CrudActionPayload $payload, array $data): void
    {
        $model = $payload->getModel();
        $model->fill($data);
        $model->save();
    }
}
