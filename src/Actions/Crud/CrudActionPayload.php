<?php

namespace Taha\Crudify\Actions\Crud;

use Illuminate\Database\Eloquent\Model;
use Taha\Crudify\Actions\ActionPayloadInterface;

class CrudActionPayload implements ActionPayloadInterface
{
    public function __construct(
        protected array $data,
        protected Model $model,
        protected array $originalData = [],
        protected array $additionalData = [],
        protected bool $allowModelTimestampsOverride = false,
    ) {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getOriginalData(): array
    {
        return $this->originalData;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function setAdditionalData(array $additionalData): static
    {
        $this->additionalData = $additionalData;
        return $this;
    }

    public function getAllowModelTimestampsOverride(): bool
    {
        return $this->allowModelTimestampsOverride;
    }

    public function setAllowModelTimestampsOverride(bool $allow): static
    {
        $this->allowModelTimestampsOverride = $allow;
        return $this;
    }
}
