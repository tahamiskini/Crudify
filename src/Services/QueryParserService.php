<?php

namespace Taha\Crudify\Services;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Taha\Crudify\Services\Filter\FilterParser;

class QueryParserService
{
    public function __construct(
        protected ?FilterParser $filterParser = null,
    ) {
        $this->filterParser ??= new FilterParser();
    }

    public function parse(Request $request, string $modelClass): QueryBuilder
    {
        $this->normalizeRequest($request);

        $query = QueryBuilder::for($modelClass, $request);

        $this->parseIncludes($request, $query)
            ->parseSorts($request, $query);

        $this->filterParser->parse($request, $query);

        return $query;
    }

    public function getFilterParser(): FilterParser
    {
        return $this->filterParser;
    }

    protected function normalizeRequest(Request $request): void
    {
        $include = $request->input('include', '');

        if (is_string($include)) {
            $cleaned = collect(explode(',', $include))
                ->map(fn (string $item) => explode('|', $item, 2)[0])
                ->filter()
                ->implode(',');

            $request->merge(['include' => $cleaned]);
        }
    }

    protected function parseIncludes(Request $request, QueryBuilder $query): self
    {
        $includes = array_filter(explode(',', $request->input('include', '')));

        if (!empty($includes)) {
            $query->allowedIncludes(...$includes);
        }

        return $this;
    }

    protected function parseSorts(Request $request, QueryBuilder $query): self
    {
        $sorts = array_filter(explode(',', $request->input('sort', '')));

        if (!empty($sorts)) {
            $resolved = [];

            foreach ($sorts as $sort) {
                $resolved[] = $this->resolveRelationSort($sort);
            }

            $query->allowedSorts(...$resolved);
        }

        return $this;
    }

    protected function resolveRelationSort(string $sort): AllowedSort|string
    {
        $descending = str_starts_with($sort, '-');
        $field = ltrim($sort, '-');

        if (str_contains($field, '.')) {
            return AllowedSort::field(
                $field,
                ($descending ? '-' : '') . $field
            );
        }

        return $sort;
    }
}
