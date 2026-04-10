<?php

declare(strict_types=1);

use Laraditz\ModelFilter\FilterInput;

// ── Baseline ──────────────────────────────────────────────────────────────────

it('returns empty array when filterable is empty', function (): void {
    expect(FilterInput::parse(['name' => 'farhan'], []))->toBeEmpty();
});

it('returns empty array when raw is empty', function (): void {
    expect(FilterInput::parse([], ['name']))->toBeEmpty();
});

it('drops field not in filterable', function (): void {
    $result = FilterInput::parse(['secret' => 'value', 'name' => 'farhan'], ['name']);

    expect($result)->toHaveCount(1)
        ->and($result[0]->field)->toBe('name');
});

// ── Shorthand and eq ──────────────────────────────────────────────────────────

it('parses shorthand value as eq operator', function (): void {
    $result = FilterInput::parse(['name' => 'farhan'], ['name']);

    expect($result)->toHaveCount(1)
        ->and($result[0]->field)->toBe('name')
        ->and($result[0]->operator)->toBe('eq')
        ->and($result[0]->value)->toBe('farhan')
        ->and($result[0]->isOr)->toBeFalse();
});

it('parses explicit eq operator', function (): void {
    $result = FilterInput::parse(['name' => ['eq' => 'farhan']], ['name']);

    expect($result[0]->operator)->toBe('eq');
});

// ── String comparison operators ───────────────────────────────────────────────

it('parses neq operator', function (): void {
    $result = FilterInput::parse(['status' => ['neq' => 'banned']], ['status']);

    expect($result[0]->operator)->toBe('neq')
        ->and($result[0]->value)->toBe('banned');
});

it('parses like operator', function (): void {
    $result = FilterInput::parse(['name' => ['like' => 'far']], ['name']);

    expect($result[0]->operator)->toBe('like')
        ->and($result[0]->value)->toBe('far');
});

// ── Numeric comparison operators ──────────────────────────────────────────────

it('parses gt operator', function (): void {
    expect(FilterInput::parse(['age' => ['gt' => '18']], ['age'])[0]->operator)->toBe('gt');
});

it('parses gte operator', function (): void {
    expect(FilterInput::parse(['age' => ['gte' => '18']], ['age'])[0]->operator)->toBe('gte');
});

it('parses lt operator', function (): void {
    expect(FilterInput::parse(['age' => ['lt' => '65']], ['age'])[0]->operator)->toBe('lt');
});

it('parses lte operator', function (): void {
    expect(FilterInput::parse(['age' => ['lte' => '65']], ['age'])[0]->operator)->toBe('lte');
});

// ── Multiple operators on one field ──────────────────────────────────────────

it('emits one condition per operator when multiple operators given for same field', function (): void {
    $result = FilterInput::parse(['age' => ['gte' => '18', 'lte' => '65']], ['age']);

    expect($result)->toHaveCount(2)
        ->and($result[0]->operator)->toBe('gte')
        ->and($result[0]->value)->toBe('18')
        ->and($result[1]->operator)->toBe('lte')
        ->and($result[1]->value)->toBe('65');
});

// ── in operator ───────────────────────────────────────────────────────────────

it('parses in operator by splitting comma-separated values into an array', function (): void {
    $result = FilterInput::parse(['status' => ['in' => 'active,inactive,pending']], ['status']);

    expect($result[0]->operator)->toBe('in')
        ->and($result[0]->value)->toBe(['active', 'inactive', 'pending']);
});

it('parses in operator with single value as a single-element array', function (): void {
    $result = FilterInput::parse(['status' => ['in' => 'active']], ['status']);

    expect($result[0]->value)->toBe(['active']);
});

it('trims whitespace from in values', function (): void {
    $result = FilterInput::parse(['status' => ['in' => 'active, inactive']], ['status']);

    expect($result[0]->value)->toBe(['active', 'inactive']);
});

// ── between operator ──────────────────────────────────────────────────────────

it('parses between operator with exactly 2 values into an array', function (): void {
    $result = FilterInput::parse(['age' => ['between' => '18,65']], ['age']);

    expect($result[0]->operator)->toBe('between')
        ->and($result[0]->value)->toBe(['18', '65']);
});

it('drops between condition when fewer than 2 values given', function (): void {
    expect(FilterInput::parse(['age' => ['between' => '18']], ['age']))->toBeEmpty();
});

it('drops between condition when more than 2 values given', function (): void {
    expect(FilterInput::parse(['age' => ['between' => '10,20,30']], ['age']))->toBeEmpty();
});

// ── Unknown operators ─────────────────────────────────────────────────────────

it('drops condition with unrecognised operator', function (): void {
    expect(FilterInput::parse(['name' => ['inject' => "'; DROP TABLE users"]], ['name']))->toBeEmpty();
});

// ── OR conditions ─────────────────────────────────────────────────────────────

it('parses or conditions with isOr set to true', function (): void {
    $result = FilterInput::parse(
        ['or' => ['name' => ['like' => 'far'], 'email' => ['like' => 'far']]],
        ['name', 'email']
    );

    expect($result)->toHaveCount(2)
        ->and($result[0]->isOr)->toBeTrue()
        ->and($result[0]->field)->toBe('name')
        ->and($result[1]->isOr)->toBeTrue()
        ->and($result[1]->field)->toBe('email');
});

it('parses or condition with shorthand value as eq', function (): void {
    $result = FilterInput::parse(['or' => ['status' => 'active']], ['status']);

    expect($result[0]->operator)->toBe('eq')
        ->and($result[0]->isOr)->toBeTrue();
});

it('drops or condition field not in filterable', function (): void {
    $result = FilterInput::parse(
        ['or' => ['secret' => 'value', 'name' => 'farhan']],
        ['name']
    );

    expect($result)->toHaveCount(1)
        ->and($result[0]->field)->toBe('name');
});

it('ignores or key when its value is not an array', function (): void {
    expect(FilterInput::parse(['or' => 'invalid'], ['name']))->toBeEmpty();
});

// ── Multiple conditions ───────────────────────────────────────────────────────

it('parses multiple conditions in one call', function (): void {
    $result = FilterInput::parse(
        ['name' => ['like' => 'far'], 'age' => ['gte' => '18']],
        ['name', 'age']
    );

    expect($result)->toHaveCount(2);
});
