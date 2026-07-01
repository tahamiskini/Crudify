<?php

namespace Taha\Crudify\Services;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class QueryParserService
{
    public function parse(Request $request, string $modelClass): QueryBuilder
    {
        $query = QueryBuilder::for($modelClass, $request);

        $this->parseFilters($request, $query)
            ->parseIncludes($request, $query)
            ->parseSorts($request, $query);

        return $query;
    }

    protected function parseFilters(Request $request, QueryBuilder $query): self
    {
        $filters = $request->input('filter', []);

        if (!empty($filters)) {
            $query->allowedFilters(...array_keys($filters));
        }

        return $this;
    }

    protected function parseIncludes(Request $request, QueryBuilder $query): self
    {
        $includes = array_filter(explode(',', $request->input('include', '')));

        if (!empty($includes)) {
            $query->allowedIncludes($includes);
        }

        return $this;
    }

    protected function parseSorts(Request $request, QueryBuilder $query): self
    {
        $sorts = array_filter(explode(',', $request->input('sort', '')));

        if (!empty($sorts)) {
            $query->allowedSorts($sorts);
        }

        return $this;
    }
}
