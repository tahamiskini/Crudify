<?php

namespace Taha\Crudify\Actions\Crud;

use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\ExecutableActionResponseContract;
use Taha\Crudify\Events\CrudModelAfterUpdate;
use Taha\Crudify\Events\CrudModelBeforeUpdate;

class Update extends Create
{
    public function run(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        CrudModelBeforeUpdate::dispatch($actionPayload);

        $actionResponse = $this->doRun($actionPayload);

        CrudModelAfterUpdate::dispatch($actionPayload);

        return $actionResponse;
    }
}
