<?php

namespace Taha\Crudify\Services\Filter;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Taha\Crudify\Services\Filter\Contracts\FilterContract;
use Taha\Crudify\Services\Filter\Exceptions\InvalidFilterOperatorException;

class FilterParser
{
    protected array $operators = [];

    public function __construct(?array $operators = null)
    {
        $this->registerDefaultOperators();

        if ($operators !== null) {
            foreach ($operators as $name => $filter) {
                $this->registerFilter($name, $filter);
            }
        }
    }

    public function parse(Request $request, QueryBuilder $query): QueryBuilder
    {
        $filters = $request->input('filter', []);

        if (!is_array($filters)) {
            return $query;
        }

        foreach ($filters as $key => $value) {
            [$operator, $field] = $this->parseKey($key);
            $strategy = $this->resolveOperator($operator);
            $query = $strategy->apply($query, $field, $value);
        }

        return $query;
    }

    public function registerFilter(string $operator, FilterContract $filter): self
    {
        $this->operators[$operator] = $filter;

        return $this;
    }

    public function getOperators(): array
    {
        return array_keys($this->operators);
    }

    protected function parseKey(string $key): array
    {
        if (!str_contains($key, ':')) {
            return ['eq', $key];
        }

        $parts = explode(':', $key, 2);

        return [$parts[0], $parts[1]];
    }

    protected function resolveOperator(string $operator): FilterContract
    {
        if (!isset($this->operators[$operator])) {
            throw new InvalidFilterOperatorException("Unknown filter operator: '{$operator}'.");
        }

        return $this->operators[$operator];
    }

    protected function registerDefaultOperators(): void
    {
        $this->operators = [
            'eq' => new Operators\Eq(),
            'neq' => new Operators\NotEq(),
            'noteq' => new Operators\NotEq(),
            'notEq' => new Operators\NotEq(),
            'contains' => new Operators\Contains(),
            'startsWith' => new Operators\StartsWith(),
            'startswith' => new Operators\StartsWith(),
            'endsWith' => new Operators\EndsWith(),
            'endswith' => new Operators\EndsWith(),
            'gt' => new Operators\Gt(),
            'gte' => new Operators\Gte(),
            'lt' => new Operators\Lt(),
            'lte' => new Operators\Lte(),
            'in' => new Operators\In(),
            'notIn' => new Operators\NotIn(),
            'notin' => new Operators\NotIn(),
            'isNull' => new Operators\IsNull(),
            'isnull' => new Operators\IsNull(),
            'notNull' => new Operators\NotNull(),
            'notnull' => new Operators\NotNull(),
            'between' => new Operators\Between(),
        ];
    }
}
