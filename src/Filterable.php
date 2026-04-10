<?php

declare(strict_types=1);

namespace Laraditz\ModelFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait Filterable
{
    /**
     * Models using this trait must declare this property to enable filtering.
     * No trait-level default is provided — omitting it disables filtering.
     * Sort is applied regardless of whether $filterable is defined.
     *
     * protected array $filterable = ['field', 'relation.column'];
     */

    public function scopeFilter(Builder $query, array $data = []): void
    {
        // Filtering path: gated by $filterable
        if (!empty($this->filterable ?? [])) {
            $raw         = $data['filters'] ?? [];
            $conditions  = FilterInput::parse($raw, $this->filterable);
            $filterClass = 'App\\Filters\\' . class_basename($this) . 'Filter';
            $filter      = class_exists($filterClass) ? new $filterClass($query) : null;

            $andConditions = array_values(array_filter($conditions, fn (FilterCondition $c) => !$c->isOr));
            $orConditions  = array_values(array_filter($conditions, fn (FilterCondition $c) => $c->isOr));

            foreach ($andConditions as $condition) {
                $method = Str::camel($condition->field);
                if ($filter !== null && method_exists($filter, $method)) {
                    $filter->$method($condition->value);
                } else {
                    $this->applyCondition($query, $condition);
                }
            }

            if (!empty($orConditions)) {
                $query->where(function (Builder $q) use ($orConditions): void {
                    foreach ($orConditions as $i => $condition) {
                        $this->applyCondition($q, $condition, $i > 0);
                    }
                });
            }
        }

        // Sort: always applied, independent of $filterable
        if (!empty($data['sort'])) {
            $this->applySort($query, (string) $data['sort']);
        }
    }

    private function applyCondition(Builder $query, FilterCondition $condition, bool $or = false): void
    {
        if (str_contains($condition->field, '.')) {
            [$relation, $column] = explode('.', $condition->field, 2);
            $inner = new FilterCondition($column, $condition->operator, $condition->value);
            $query->{$or ? 'orWhereHas' : 'whereHas'}(
                $relation,
                fn (Builder $q) => $this->applyConditionToQuery($q, $inner)
            );
            return;
        }

        $this->applyConditionToQuery($query, $condition, $or);
    }

    private function applyConditionToQuery(Builder $query, FilterCondition $condition, bool $or = false): void
    {
        match ($condition->operator) {
            'in' => $or
                ? $query->orWhereIn($condition->field, (array) $condition->value)
                : $query->whereIn($condition->field, (array) $condition->value),
            'between' => $or
                ? $query->orWhereBetween($condition->field, (array) $condition->value)
                : $query->whereBetween($condition->field, (array) $condition->value),
            default => $query->{$or ? 'orWhere' : 'where'}(
                $condition->field,
                $this->operatorToSql($condition->operator),
                $condition->operator === 'like'
                    ? '%' . $condition->value . '%'
                    : $condition->value
            ),
        };
    }

    private function operatorToSql(string $operator): string
    {
        return match ($operator) {
            'eq'    => '=',
            'neq'   => '!=',
            'like'  => 'LIKE',
            'gt'    => '>',
            'gte'   => '>=',
            'lt'    => '<',
            'lte'   => '<=',
            default => '=',
        };
    }

    private function applySort(Builder $query, string $sort): void
    {
        foreach (explode(',', $sort) as $field) {
            $field = trim($field);
            if ($field === '') {
                continue;
            }
            if (str_starts_with($field, '-')) {
                $query->orderByDesc(ltrim($field, '-'));
            } else {
                $query->orderBy($field);
            }
        }
    }
}
