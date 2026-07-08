<?php

namespace Taha\Crudify;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;
use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\Crud\AddRelation;
use Taha\Crudify\Actions\Crud\AttachRelation;
use Taha\Crudify\Actions\Crud\Create;
use Taha\Crudify\Actions\Crud\CrudActionPayload;
use Taha\Crudify\Actions\Crud\Delete;
use Taha\Crudify\Actions\Crud\DetachRelation;
use Taha\Crudify\Actions\Crud\MassCreate;
use Taha\Crudify\Actions\Crud\MassCreateOrUpdate;
use Taha\Crudify\Actions\Crud\MassDelete;
use Taha\Crudify\Actions\Crud\MassUpdate;
use Taha\Crudify\Actions\Crud\RemoveRelation;
use Taha\Crudify\Actions\Crud\Update;
use Taha\Crudify\Actions\ExecutableAction;
use Taha\Crudify\Actions\ExecutableActionResponseContract;
use Taha\Crudify\Services\QueryParserService;

class CrudifyController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public const PER_PAGE = 20;

    public function __construct(
        protected QueryParserService $queryParser,
    ) {
    }

    public function readOne(Request $request, string $id): CrudifyResource
    {
        $modelClass = $this->resolveModel();
        $modelInstance = $this->queryParser
            ->parse($request, $modelClass)
            ->findOrFail($id);

        $this->authorize('readOne', [$modelClass, $modelInstance]);

        return $this->createResource($modelInstance);
    }

    public function readMore(Request $request): AnonymousResourceCollection
    {
        $modelClass = $this->resolveModel();

        $this->authorize('readMore', $modelClass);

        return $this->createResourceCollection(
            $this->queryParser
                ->parse($request, $modelClass)
                ->paginate($this->perPage())
        );
    }

    public function create(Request $request): CrudifyResource
    {
        $modelClass = $this->resolveModel();
        $this->authorize('create', $modelClass);

        $this->resolveRequest();

        $model = new $modelClass;
        $data = $this->requestData();

        $modelId = $data[$model->getKeyName()] ?? null;
        if ($modelId !== null) {
            $model->{$model->getKeyName()} = $modelId;
        }

        $this->onCreate($this->createActionPayload($request, $model, $data));

        $fresh = $model->fresh();

        return $this->createResource($fresh ?? $model);
    }

    public function update(Request $request, string $id): CrudifyResource
    {
        $modelClass = $this->resolveModel();
        $instance = $modelClass::query()->findOrFail($id);

        $this->authorize('update', [$modelClass, $instance]);

        $this->resolveRequest();

        $this->onUpdate($this->createActionPayload($request, $instance, $this->requestData(), $instance->getOriginal()));

        $fresh = $instance->fresh();

        return $this->createResource($fresh ?? $instance);
    }

    public function delete(Request $request, string $id): JsonResponse
    {
        $modelClass = $this->resolveModel();
        $instance = $modelClass::query()->findOrFail($id);

        $this->authorize('delete', [$modelClass, $instance]);

        $this->onDelete($this->createActionPayload($request, $instance));

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function massCreate(Request $request): JsonResponse
    {
        $modelClass = $this->resolveModel();

        $this->authorize('massCreate', $modelClass);

        $this->resolveRequest();

        $model = new $modelClass;
        $payload = $this->createActionPayload($request, $model, $request->all());

        $this->onMassCreate($payload);

        return response()->json(['message' => 'Mass created successfully'], Response::HTTP_CREATED);
    }

    public function massUpdate(Request $request): JsonResponse
    {
        $modelClass = $this->resolveModel();

        $this->authorize('massUpdate', $modelClass);

        $this->resolveRequest();

        $model = new $modelClass;
        $payload = $this->createActionPayload($request, $model, $request->all());

        $this->onMassUpdate($payload);

        return response()->json(['message' => 'Mass updated successfully']);
    }

    public function massDelete(Request $request): JsonResponse
    {
        $modelClass = $this->resolveModel();

        $this->authorize('massDelete', $modelClass);

        $model = new $modelClass;
        $payload = $this->createActionPayload($request, $model, $request->all());

        $this->onMassDelete($payload);

        return response()->json(['message' => 'Mass deleted successfully']);
    }

    public function massCreateOrUpdate(Request $request): JsonResponse
    {
        $modelClass = $this->resolveModel();

        $this->authorize('massCreateOrUpdate', $modelClass);

        $this->resolveRequest();

        $model = new $modelClass;
        $payload = $this->createActionPayload($request, $model, $request->all());

        $this->onMassCreateOrUpdate($payload);

        return response()->json(['message' => 'Mass create or update completed successfully']);
    }

    public function addRelation(Request $request, string $id, string $relationField): CrudifyResource
    {
        $modelClass = $this->resolveModel();
        $instance = $modelClass::query()->findOrFail($id);

        $this->authorize('update', [$modelClass, $instance]);

        $actionPayload = $this->createActionPayload($request, $instance, $request->all());
        $actionPayload->setAdditionalData(['relationField' => $relationField]);

        $this->onAddRelation($actionPayload);

        return $this->createResource($instance->fresh());
    }

    public function removeRelation(Request $request, string $id, string $relationField, ?string $relationId = null): CrudifyResource
    {
        $modelClass = $this->resolveModel();
        $instance = $modelClass::query()->findOrFail($id);

        $this->authorize('update', [$modelClass, $instance]);

        $data = $relationId ? ['id' => $relationId] : $request->all();
        $actionPayload = $this->createActionPayload($request, $instance, $data);
        $actionPayload->setAdditionalData(['relationField' => $relationField]);

        $this->onRemoveRelation($actionPayload);

        return $this->createResource($instance->fresh());
    }

    public function attachRelation(string $id, string $relationField, string $relationId): CrudifyResource
    {
        $modelClass = $this->resolveModel();
        $instance = $modelClass::query()->findOrFail($id);

        $this->authorize('update', [$modelClass, $instance]);

        $actionPayload = $this->createActionPayload(request(), $instance, ['id' => $relationId]);
        $actionPayload->setAdditionalData(['relationField' => $relationField]);

        $this->onAttachRelation($actionPayload);

        return $this->createResource($instance->fresh());
    }

    public function detachRelation(string $id, string $relationField, string $relationId): CrudifyResource
    {
        $modelClass = $this->resolveModel();
        $instance = $modelClass::query()->findOrFail($id);

        $this->authorize('update', [$modelClass, $instance]);

        $actionPayload = $this->createActionPayload(request(), $instance, ['id' => $relationId]);
        $actionPayload->setAdditionalData(['relationField' => $relationField]);

        $this->onDetachRelation($actionPayload);

        return $this->createResource($instance->fresh());
    }

    protected function onCreate(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        return $this->getCreateAction()->run($actionPayload);
    }

    protected function onUpdate(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        return $this->getUpdateAction()->run($actionPayload);
    }

    protected function onDelete(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        return $this->getDeleteAction()->run($actionPayload);
    }

    protected function onMassCreate(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        return $this->getMassCreateAction()->run($actionPayload);
    }

    protected function onMassUpdate(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        return $this->getMassUpdateAction()->run($actionPayload);
    }

    protected function onMassDelete(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        return $this->getMassDeleteAction()->run($actionPayload);
    }

    protected function onMassCreateOrUpdate(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        return $this->getMassCreateOrUpdateAction()->run($actionPayload);
    }

    protected function onAddRelation(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        return $this->getAddRelationAction()->run($actionPayload);
    }

    protected function onRemoveRelation(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        return $this->getRemoveRelationAction()->run($actionPayload);
    }

    protected function onAttachRelation(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        return $this->getAttachRelationAction()->run($actionPayload);
    }

    protected function onDetachRelation(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        return $this->getDetachRelationAction()->run($actionPayload);
    }

    protected function getCreateAction(): ExecutableAction
    {
        return resolve(Create::class);
    }

    protected function getUpdateAction(): ExecutableAction
    {
        return resolve(Update::class);
    }

    protected function getDeleteAction(): ExecutableAction
    {
        return resolve(Delete::class);
    }

    protected function getMassCreateAction(): ExecutableAction
    {
        return resolve(MassCreate::class);
    }

    protected function getMassUpdateAction(): ExecutableAction
    {
        return resolve(MassUpdate::class);
    }

    protected function getMassDeleteAction(): ExecutableAction
    {
        return resolve(MassDelete::class);
    }

    protected function getMassCreateOrUpdateAction(): ExecutableAction
    {
        return resolve(MassCreateOrUpdate::class);
    }

    protected function getAddRelationAction(): ExecutableAction
    {
        return resolve(AddRelation::class);
    }

    protected function getRemoveRelationAction(): ExecutableAction
    {
        return resolve(RemoveRelation::class);
    }

    protected function getAttachRelationAction(): ExecutableAction
    {
        return resolve(AttachRelation::class);
    }

    protected function getDetachRelationAction(): ExecutableAction
    {
        return resolve(DetachRelation::class);
    }

    protected function getModelFqn(): string
    {
        $route = $this->getRoute();

        return ModelHelper::getModelFqn(
            $route->getAction('model'),
            $route->getAction('namespace')
        );
    }

    protected function getRequestFqn(): string
    {
        $route = $this->getRoute();
        $requestClass = ModelHelper::getRequestFqn(
            $route->getAction('model'),
            $route->getAction('namespace')
        );

        if (!class_exists($requestClass)) {
            $requestClass = CrudifyRequest::class;
        }

        return $requestClass;
    }

    protected function resolveModel(): string
    {
        return $this->getModelFqn();
    }

    protected function resolveRequest(): CrudifyRequest
    {
        return resolve($this->getRequestFqn());
    }

    protected function perPage(): int
    {
        return request()->query->has('per_page')
            ? request()->query->getInt('per_page')
            : self::PER_PAGE;
    }

    protected function createResource(Model $resource): CrudifyResource
    {
        return new CrudifyResource($resource);
    }

    protected function createResourceCollection($resource): AnonymousResourceCollection
    {
        return CrudifyResource::collection($resource);
    }

    protected function createActionPayload(
        Request $request,
        Model $model,
        array $data = [],
        array $originalData = [],
    ): ActionPayloadInterface {
        return new CrudActionPayload($data, $model, $originalData);
    }

    protected function requestData(): array
    {
        return request()->all();
    }

    private function getRoute(): Route
    {
        return request()->route();
    }
}
