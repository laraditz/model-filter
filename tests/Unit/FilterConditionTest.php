<?php

declare(strict_types=1);

use Laraditz\ModelFilter\FilterCondition;

it('stores field, operator, value with isOr defaulting to false', function (): void {
    $condition = new FilterCondition('name', 'eq', 'farhan');

    expect($condition->field)->toBe('name')
        ->and($condition->operator)->toBe('eq')
        ->and($condition->value)->toBe('farhan')
        ->and($condition->isOr)->toBeFalse();
});

it('stores isOr as true when specified', function (): void {
    $condition = new FilterCondition('email', 'like', 'far', true);

    expect($condition->isOr)->toBeTrue();
});

it('accepts mixed value types', function (): void {
    $arrayCondition = new FilterCondition('age', 'between', ['18', '65']);
    $intCondition   = new FilterCondition('age', 'gte', 18);

    expect($arrayCondition->value)->toBe(['18', '65'])
        ->and($intCondition->value)->toBe(18);
});

it('properties are readonly', function (): void {
    $condition = new FilterCondition('name', 'eq', 'farhan');

    expect(fn () => $condition->field = 'other')->toThrow(Error::class);
});
