<?php

namespace Taha\Crudify\Actions\Crud;

use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\ActionResponse;
use Taha\Crudify\Actions\ExecutableActionResponseContract;
use Taha\Crudify\Events\CrudModelAfterDelete;
use Taha\Crudify\Events\CrudModelBeforeDelete;

class Delete extends CrudAction
{
    public function run(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        CrudModelBeforeDelete::dispatch($actionPayload);

        $actionResponse = new ActionResponse([], (bool)$actionPayload->getModel()->delete());

        CrudModelAfterDelete::dispatch($actionPayload);

        return $actionResponse;
    }
}
