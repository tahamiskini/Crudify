<?php

namespace Taha\Crudify\Actions;

use Illuminate\Database\Eloquent\Model;

interface ActionPayloadInterface
{
    public function getData(): array;

    public function getOriginalData(): array;

    public function getModel(): Model;

    public function getAdditionalData(): array;

    public function setAdditionalData(array $additionalData): static;

    public function getAllowModelTimestampsOverride(): bool;

    public function setAllowModelTimestampsOverride(bool $allow): static;
}
