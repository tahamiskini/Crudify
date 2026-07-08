<?php

namespace Taha\Crudify\Actions\Crud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\ActionResponse;
use Taha\Crudify\Actions\ExecutableActionResponseContract;
use Taha\Crudify\CrudifyRequest;

class MassCreate extends TransactionableAction
{
    public function __construct(
        protected CrudifyRequest $request,
    ) {
    }

    protected function doRun(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        /** @var Create $createAction */
        $createAction = resolve(Create::class);

        foreach ($actionPayload->getData() as $item) {
            Validator::make($item, $this->request->rules())->validate();

            $model = $actionPayload->getModel()->newInstance();

            $crudActionPayload = $this->createPayload($item, $model, $actionPayload);
            $createAction->run($crudActionPayload);
        }

        return new ActionResponse();
    }

    protected function createPayload(array $item, Model $model, ActionPayloadInterface $originalActionPayload): ActionPayloadInterface
    {
        $payload = new CrudActionPayload($item, $model);
        $payload->setAllowModelTimestampsOverride($originalActionPayload->getAllowModelTimestampsOverride());

        return $payload;
    }
}
