<?php

declare(strict_types=1);

namespace Laraditz\ModelFilter;

class FilterCondition
{
    public function __construct(
        public readonly string $field,
        public readonly string $operator,
        public readonly mixed  $value,
        public readonly bool   $isOr = false,
    ) {}
}
