<?php

namespace Taha\Crudify\Actions\Crud;

use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\ActionResponse;
use Taha\Crudify\Actions\ExecutableActionResponseContract;
use Taha\Crudify\Events\CrudModelAfterForceDelete;
use Taha\Crudify\Events\CrudModelBeforeForceDelete;

class ForceDelete extends CrudAction
{
    public function run(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        CrudModelBeforeForceDelete::dispatch($actionPayload);

        $actionResponse = new ActionResponse([], (bool)$actionPayload->getModel()->forceDelete());

        CrudModelAfterForceDelete::dispatch($actionPayload);

        return $actionResponse;
    }
}
