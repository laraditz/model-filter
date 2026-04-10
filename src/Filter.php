<?php

declare(strict_types=1);

namespace Laraditz\ModelFilter;

use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    public function __construct(
        protected Builder $query,
    ) {}
}
