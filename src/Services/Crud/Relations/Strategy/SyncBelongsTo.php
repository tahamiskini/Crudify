<?php

namespace Taha\Crudify\Services\Crud\Relations\Strategy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Taha\Crudify\Services\Crud\Relations\Contract\SyncStrategyContract;

class SyncBelongsTo implements SyncStrategyContract
{
    public function __invoke(Model $model, string $relationName, array $data): void
    {
        $id = $data['id'] ?? $data;

        $model->{$relationName}()->associate($id);
        $model->save();
    }
}
