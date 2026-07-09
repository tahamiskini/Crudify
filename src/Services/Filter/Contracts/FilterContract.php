<?php

namespace Taha\Crudify\Services\Filter\Contracts;

use Spatie\QueryBuilder\QueryBuilder;

interface FilterContract
{
    public function apply(QueryBuilder $query, string $field, mixed $value): QueryBuilder;
}
