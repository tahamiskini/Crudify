<?php

namespace Taha\Crudify\Actions\Crud;

use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\ActionResponse;
use Taha\Crudify\Actions\ExecutableActionResponseContract;
use Taha\Crudify\Events\CrudModelAfterRestore;
use Taha\Crudify\Events\CrudModelBeforeRestore;

class Restore extends CrudAction
{
    public function run(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        CrudModelBeforeRestore::dispatch($actionPayload);

        $actionResponse = new ActionResponse([], (bool)$actionPayload->getModel()->restore());

        CrudModelAfterRestore::dispatch($actionPayload);

        return $actionResponse;
    }
}
