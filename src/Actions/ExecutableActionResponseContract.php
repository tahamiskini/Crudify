<?php

namespace Taha\Crudify\Actions;

interface ExecutableActionResponseContract
{
    public function success(): bool;

    public function getData(): array;
}
