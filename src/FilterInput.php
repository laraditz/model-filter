<?php

declare(strict_types=1);

namespace Laraditz\ModelFilter;

final class FilterInput
{
    private const ALLOWED_OPERATORS = ['eq', 'neq', 'like', 'gt', 'gte', 'lt', 'lte', 'in', 'between'];

    /**
     * Parse a raw filters[] array into FilterCondition objects.
     * Fields not in $filterable are silently dropped.
     * Unrecognised operators are silently dropped.
     * Multiple operators on the same field each produce a separate FilterCondition.
     * Returns an empty array when $filterable is empty.
     *
     * @param  array<string, mixed>  $raw
     * @param  array<int, string>    $filterable
     * @return FilterCondition[]
     */
    public static function parse(array $raw, array $filterable): array
    {
        if (empty($filterable)) {
            return [];
        }

        $conditions = [];

        foreach ($raw as $key => $value) {
            if ($key === 'or') {
                if (!is_array($value)) {
                    continue;
                }
                foreach ($value as $field => $fieldValue) {
                    if (!in_array($field, $filterable, true)) {
                        continue;
                    }
                    array_push($conditions, ...self::buildConditions((string) $field, $fieldValue, isOr: true));
                }
                continue;
            }

            if (!in_array($key, $filterable, true)) {
                continue;
            }

            array_push($conditions, ...self::buildConditions($key, $value));
        }

        return $conditions;
    }

    /**
     * @return FilterCondition[]
     */
    private static function buildConditions(string $field, mixed $value, bool $isOr = false): array
    {
        if (!is_array($value)) {
            return [new FilterCondition($field, 'eq', $value, $isOr)];
        }

        $results = [];

        foreach ($value as $operator => $rawValue) {
            $operator = (string) $operator;

            if (!in_array($operator, self::ALLOWED_OPERATORS, true)) {
                continue;
            }

            $parsedValue = self::parseValue($operator, $rawValue);

            if ($parsedValue === null) {
                continue;
            }

            $results[] = new FilterCondition($field, $operator, $parsedValue, $isOr);
        }

        return $results;
    }

    private static function parseValue(string $operator, mixed $value): mixed
    {
        if ($operator === 'in') {
            return array_map('trim', explode(',', (string) $value));
        }

        if ($operator === 'between') {
            $parts = array_map('trim', explode(',', (string) $value));
            if (count($parts) !== 2) {
                return null;
            }
            return $parts;
        }

        return $value;
    }
}
