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
use Taha\Crudify\Actions\Crud\Create;
use Taha\Crudify\Actions\Crud\CrudActionPayload;
use Taha\Crudify\Actions\Crud\Delete;
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
