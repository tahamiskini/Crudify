<?php

namespace Taha\Crudify\Actions\Crud;

use Illuminate\Support\Facades\DB;
use Throwable;
use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\ExecutableAction;
use Taha\Crudify\Actions\ExecutableActionResponseContract;

abstract class TransactionableAction implements ExecutableAction
{
    public function run(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        DB::beginTransaction();
        try {
            $actionResponse = $this->doRun($actionPayload);
            DB::commit();
        } catch (Throwable $t) {
            DB::rollBack();
            throw $t;
        }

        return $actionResponse;
    }

    abstract protected function doRun(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract;
}
