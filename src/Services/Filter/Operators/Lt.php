<?php

namespace Taha\Crudify\Services\Filter\Operators;

use Spatie\QueryBuilder\QueryBuilder;
use Taha\Crudify\Services\Filter\Contracts\FilterContract;

class Lt implements FilterContract
{
    public function apply(QueryBuilder $query, string $field, mixed $value): QueryBuilder
    {
        return $query->where($field, '<', $value);
    }
}
