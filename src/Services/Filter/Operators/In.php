<?php

namespace Taha\Crudify\Services\Filter\Operators;

use Spatie\QueryBuilder\QueryBuilder;
use Taha\Crudify\Services\Filter\Contracts\FilterContract;

class In implements FilterContract
{
    public function apply(QueryBuilder $query, string $field, mixed $value): QueryBuilder
    {
        $values = is_array($value) ? $value : explode(',', $value);

        return $query->whereIn($field, $values);
    }
}
