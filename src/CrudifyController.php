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
        $model = $this->resolveModel();
        $modelInstance = $this->queryParser
            ->parse($request, $model)
            ->findOrFail($id);

        $this->authorize('readOne', [$model, $modelInstance]);

        return $this->createResource($modelInstance);
    }

    public function readMore(Request $request): AnonymousResourceCollection
    {
        $model = $this->resolveModel();

        $this->authorize('readMore', $model);

        return $this->createResourceCollection(
            $this->queryParser
                ->parse($request, $model)
                ->paginate($this->perPage())
        );
    }

    public function create(Request $request): CrudifyResource
    {
        $model = $this->resolveModel();
        $this->authorize('create', $model);

        $this->resolveRequest();

        $instance = $model::create($request->all());
        $instance = $instance->fresh();

        return $this->createResource($instance);
    }

    public function update(Request $request, string $id): CrudifyResource
    {
        $modelClass = $this->resolveModel();
        $instance = $modelClass::query()->findOrFail($id);

        $this->authorize('update', [$modelClass, $instance]);

        $this->resolveRequest();

        $instance->update($request->all());
        $instance = $instance->fresh();

        return $this->createResource($instance);
    }

    public function delete(Request $request, string $id): JsonResponse
    {
        $modelClass = $this->resolveModel();
        $instance = $modelClass::query()->findOrFail($id);

        $this->authorize('delete', [$modelClass, $instance]);

        $instance->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
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

    private function getRoute(): Route
    {
        return request()->route();
    }
}
