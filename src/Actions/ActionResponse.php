<?php

namespace Taha\Crudify\Actions;

class ActionResponse implements ExecutableActionResponseContract
{
    public function __construct(
        protected array $data = [],
        protected bool  $success = true,
    )
    {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function success(): bool
    {
        return $this->success;
    }
}
