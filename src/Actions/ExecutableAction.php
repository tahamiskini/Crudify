<?php

namespace Taha\Crudify\Actions;

interface ExecutableAction
{
    public function run(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract;
}
