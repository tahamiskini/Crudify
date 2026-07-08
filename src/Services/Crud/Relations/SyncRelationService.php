<?php

namespace Taha\Crudify\Services\Crud\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Taha\Crudify\Services\Crud\Relations\Strategy\AttachHasMany;
use Taha\Crudify\Services\Crud\Relations\Strategy\CreateHasMany;
use Taha\Crudify\Services\Crud\Relations\Strategy\DeleteHasMany;
use Taha\Crudify\Services\Crud\Relations\Strategy\DetachBelongsToMany;
use Taha\Crudify\Services\Crud\Relations\Strategy\DetachHasMany;
use Taha\Crudify\Services\Crud\Relations\Strategy\SyncBelongsTo;
use Taha\Crudify\Services\Crud\Relations\Strategy\SyncBelongsToMany;
use Taha\Crudify\Services\Crud\Relations\Strategy\SyncHasMany;
use Taha\Crudify\Services\Crud\Relations\Strategy\SyncHasOne;
use Taha\Crudify\Services\Crud\Relations\Strategy\SyncMorphTo;
use Taha\Crudify\Services\Crud\Relations\Strategy\SyncWithoutDetachBelongsToMany;
use Taha\Crudify\Services\RelationFieldCheckerService;

class SyncRelationService
{
    public function __construct(
        protected RelationFieldCheckerService $relationFieldCheckerService,
    ) {
    }

    public function applySync(Model $model, string $field, array|null $value): void
    {
        $syncStrategyClass = $this->resolveSyncStrategy($model, $field);

        if ($syncStrategyClass === null) {
            return;
        }

        resolve($syncStrategyClass)(
            $model,
            $field,
            $value,
        );
    }

    public function createRelation(Model $model, string $field, array $value): void
    {
        $syncStrategyClass = $this->resolveCreateStrategy($model, $field);

        if ($syncStrategyClass === null) {
            return;
        }

        resolve($syncStrategyClass)(
            $model,
            $field,
            $value,
        );
    }

    public function deleteRelation(Model $model, string $field, array $value): void
    {
        $syncStrategyClass = $this->resolveDeleteStrategy($model, $field);

        if ($syncStrategyClass === null) {
            return;
        }

        resolve($syncStrategyClass)(
            $model,
            $field,
            $value,
        );
    }

    public function attachRelation(Model $model, string $field, array $value): void
    {
        $syncStrategyClass = $this->resolveAttachStrategy($model, $field);

        if ($syncStrategyClass === null) {
            return;
        }

        resolve($syncStrategyClass)(
            $model,
            $field,
            $value,
        );
    }

    public function detachRelation(Model $model, string $field, array $value): void
    {
        $syncStrategyClass = $this->resolveDetachStrategy($model, $field);

        if ($syncStrategyClass === null) {
            return;
        }

        resolve($syncStrategyClass)(
            $model,
            $field,
            $value,
        );
    }

    protected function resolveSyncStrategy(Model $model, string $field): ?string
    {
        if (!$this->relationFieldCheckerService->isRelationField($model, $field)) {
            return null;
        }

        $relationClass = $this->relationFieldCheckerService->getRelationClassByField($model, $field);

        return match ($relationClass) {
            BelongsToMany::class, MorphToMany::class => SyncBelongsToMany::class,
            HasMany::class, MorphMany::class => SyncHasMany::class,
            BelongsTo::class => SyncBelongsTo::class,
            HasOne::class => SyncHasOne::class,
            MorphTo::class => SyncMorphTo::class,
            default => null,
        };
    }

    protected function resolveCreateStrategy(Model $model, string $field): ?string
    {
        if (!$this->relationFieldCheckerService->isRelationField($model, $field)) {
            return null;
        }

        $relationClass = $this->relationFieldCheckerService->getRelationClassByField($model, $field);

        return match ($relationClass) {
            BelongsToMany::class, MorphToMany::class => SyncWithoutDetachBelongsToMany::class,
            HasMany::class, MorphMany::class => CreateHasMany::class,
            default => null,
        };
    }

    protected function resolveDeleteStrategy(Model $model, string $field): ?string
    {
        if (!$this->relationFieldCheckerService->isRelationField($model, $field)) {
            return null;
        }

        $relationClass = $this->relationFieldCheckerService->getRelationClassByField($model, $field);

        return match ($relationClass) {
            BelongsToMany::class, MorphToMany::class => DetachBelongsToMany::class,
            HasMany::class, MorphMany::class => DeleteHasMany::class,
            default => null,
        };
    }

    protected function resolveAttachStrategy(Model $model, string $field): ?string
    {
        if (!$this->relationFieldCheckerService->isRelationField($model, $field)) {
            return null;
        }

        $relationClass = $this->relationFieldCheckerService->getRelationClassByField($model, $field);

        return match ($relationClass) {
            BelongsToMany::class, MorphToMany::class => SyncWithoutDetachBelongsToMany::class,
            HasMany::class, MorphMany::class => AttachHasMany::class,
            default => null,
        };
    }

    protected function resolveDetachStrategy(Model $model, string $field): ?string
    {
        if (!$this->relationFieldCheckerService->isRelationField($model, $field)) {
            return null;
        }

        $relationClass = $this->relationFieldCheckerService->getRelationClassByField($model, $field);

        return match ($relationClass) {
            BelongsToMany::class, MorphToMany::class => DetachBelongsToMany::class,
            HasMany::class, MorphMany::class => DetachHasMany::class,
            default => null,
        };
    }
}
