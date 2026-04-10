<?php

declare(strict_types=1);

use Tests\Fixtures\Models\Guest;
use Tests\Fixtures\Models\Role;
use Tests\Fixtures\Models\User;

// ── Helpers ───────────────────────────────────────────────────────────────────

function createGuests(): void
{
    Guest::create(['name' => 'Alice',  'email' => 'alice@example.com',  'age' => 25, 'status' => 'active']);
    Guest::create(['name' => 'Bob',    'email' => 'bob@example.com',    'age' => 17, 'status' => 'inactive']);
    Guest::create(['name' => 'Carlos', 'email' => 'carlos@example.com', 'age' => 40, 'status' => 'active']);
}

// ── Empty $filterable guard ───────────────────────────────────────────────────

it('returns all records when model has no $filterable property', function (): void {
    createGuests();

    $model = new class extends \Illuminate\Database\Eloquent\Model {
        use \Laraditz\ModelFilter\Filterable;
        protected $table = 'users';
    };

    expect($model::filter(['filters' => ['name' => 'Alice']])->get())->toHaveCount(3);
});

it('returns all records when $filterable is empty array', function (): void {
    createGuests();

    $model = new class extends \Illuminate\Database\Eloquent\Model {
        use \Laraditz\ModelFilter\Filterable;
        protected $table = 'users';
        protected array $filterable = [];
    };

    expect($model::filter(['filters' => ['name' => 'Alice']])->get())->toHaveCount(3);
});

// ── eq operator ───────────────────────────────────────────────────────────────

it('filters by eq shorthand', function (): void {
    createGuests();

    $results = Guest::filter(['filters' => ['status' => 'active']])->get();

    expect($results)->toHaveCount(2)
        ->and($results->pluck('name')->toArray())->toContain('Alice', 'Carlos');
});

it('filters by explicit eq operator', function (): void {
    createGuests();

    $results = Guest::filter(['filters' => ['name' => ['eq' => 'Alice']]])->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Alice');
});

// ── neq operator ──────────────────────────────────────────────────────────────

it('filters by neq operator', function (): void {
    createGuests();

    $results = Guest::filter(['filters' => ['status' => ['neq' => 'active']]])->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Bob');
});

// ── like operator ─────────────────────────────────────────────────────────────

it('filters by like operator wrapping value in %', function (): void {
    createGuests();

    $results = Guest::filter(['filters' => ['name' => ['like' => 'li']]])->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Alice');
});

// ── gt / gte / lt / lte ───────────────────────────────────────────────────────

it('filters by gt operator', function (): void {
    createGuests();
    expect(Guest::filter(['filters' => ['age' => ['gt' => '17']]])->get())->toHaveCount(2);
});

it('filters by gte operator', function (): void {
    createGuests();
    expect(Guest::filter(['filters' => ['age' => ['gte' => '25']]])->get())->toHaveCount(2);
});

it('filters by lt operator', function (): void {
    createGuests();
    $results = Guest::filter(['filters' => ['age' => ['lt' => '25']]])->get();
    expect($results)->toHaveCount(1)->and($results->first()->name)->toBe('Bob');
});

it('filters by lte operator', function (): void {
    createGuests();
    expect(Guest::filter(['filters' => ['age' => ['lte' => '25']]])->get())->toHaveCount(2);
});

// ── in operator ───────────────────────────────────────────────────────────────

it('filters by in operator', function (): void {
    createGuests();
    $results = Guest::filter(['filters' => ['name' => ['in' => 'Alice,Carlos']]])->get();
    expect($results)->toHaveCount(2);
});

// ── between operator ──────────────────────────────────────────────────────────

it('filters by between operator', function (): void {
    createGuests();
    $results = Guest::filter(['filters' => ['age' => ['between' => '20,35']]])->get();
    expect($results)->toHaveCount(1)->and($results->first()->name)->toBe('Alice');
});

// ── Multiple operators on one field ──────────────────────────────────────────

it('applies multiple operators on the same field as AND conditions', function (): void {
    createGuests();
    // age >= 17 AND age <= 25 → Bob (17) and Alice (25)
    $results = Guest::filter(['filters' => ['age' => ['gte' => '17', 'lte' => '25']]])->get();
    expect($results)->toHaveCount(2);
});

// ── Field not in $filterable ──────────────────────────────────────────────────

it('ignores filters for fields not in filterable', function (): void {
    createGuests();
    $results = Guest::filter(['filters' => ['id' => '1']])->get();
    expect($results)->toHaveCount(3);
});

// ── No filters ────────────────────────────────────────────────────────────────

it('returns all records when no filters key is present', function (): void {
    createGuests();
    expect(Guest::filter([])->get())->toHaveCount(3);
});

// ── Custom filter method override ─────────────────────────────────────────────

it('calls custom filter class method instead of auto-handling', function (): void {
    User::create(['name' => 'Alice', 'email' => 'a@test.com', 'age' => 25, 'status' => 'custom_active']);
    User::create(['name' => 'Bob',   'email' => 'b@test.com', 'age' => 30, 'status' => 'active']);

    $results = User::filter(['filters' => ['status' => 'active']])->get();

    expect($results)->toHaveCount(1)->and($results->first()->name)->toBe('Alice');
});

it('auto-handles fields that have no custom method in the filter class', function (): void {
    User::create(['name' => 'Alice', 'email' => 'a@test.com', 'age' => 25, 'status' => 'active']);
    User::create(['name' => 'Bob',   'email' => 'b@test.com', 'age' => 30, 'status' => 'active']);

    $results = User::filter(['filters' => ['name' => 'Alice']])->get();

    expect($results)->toHaveCount(1)->and($results->first()->name)->toBe('Alice');
});

// ── OR grouping ───────────────────────────────────────────────────────────────

it('applies OR group as AND (...OR...) so preceding AND conditions are not bypassed', function (): void {
    Guest::create(['name' => 'Alice', 'email' => 'alice@example.com', 'age' => 25, 'status' => 'active']);
    Guest::create(['name' => 'Bob',   'email' => 'bob@example.com',   'age' => 17, 'status' => 'active']);
    Guest::create(['name' => 'Carol', 'email' => 'carol@example.com', 'age' => 30, 'status' => 'inactive']);

    $results = Guest::filter([
        'filters' => [
            'status' => 'active',
            'or'     => ['name' => ['like' => 'li'], 'email' => ['like' => 'bob']],
        ],
    ])->get();

    expect($results)->toHaveCount(2)
        ->and($results->pluck('name')->toArray())->toContain('Alice', 'Bob');
});

it('applies OR group without preceding AND conditions', function (): void {
    Guest::create(['name' => 'Alice', 'email' => 'a@x.com', 'age' => 25, 'status' => 'active']);
    Guest::create(['name' => 'Bob',   'email' => 'b@x.com', 'age' => 17, 'status' => 'inactive']);
    Guest::create(['name' => 'Carol', 'email' => 'c@x.com', 'age' => 30, 'status' => 'pending']);

    $results = Guest::filter([
        'filters' => ['or' => ['name' => ['eq' => 'Alice'], 'email' => ['like' => 'b@']]],
    ])->get();

    expect($results)->toHaveCount(2)
        ->and($results->pluck('name')->toArray())->toContain('Alice', 'Bob');
});

// ── Relationship filtering ────────────────────────────────────────────────────

it('filters via dot-notation using whereHas', function (): void {
    $adminRole  = Role::create(['name' => 'admin']);
    $memberRole = Role::create(['name' => 'member']);

    User::create(['name' => 'Alice', 'email' => 'a@x.com', 'age' => 25, 'status' => 'active', 'role_id' => $adminRole->id]);
    User::create(['name' => 'Bob',   'email' => 'b@x.com', 'age' => 30, 'status' => 'active', 'role_id' => $memberRole->id]);

    $results = User::filter(['filters' => ['role.name' => ['eq' => 'admin']]])->get();

    expect($results)->toHaveCount(1)->and($results->first()->name)->toBe('Alice');
});

it('filters relationship via like operator', function (): void {
    $adminRole = Role::create(['name' => 'admin']);
    $superRole = Role::create(['name' => 'superadmin']);
    $userRole  = Role::create(['name' => 'user']);

    User::create(['name' => 'Alice', 'email' => 'a@x.com', 'age' => 25, 'status' => 'active', 'role_id' => $adminRole->id]);
    User::create(['name' => 'Bob',   'email' => 'b@x.com', 'age' => 30, 'status' => 'active', 'role_id' => $superRole->id]);
    User::create(['name' => 'Carol', 'email' => 'c@x.com', 'age' => 35, 'status' => 'active', 'role_id' => $userRole->id]);

    $results = User::filter(['filters' => ['role.name' => ['like' => 'admin']]])->get();

    expect($results)->toHaveCount(2)
        ->and($results->pluck('name')->toArray())->toContain('Alice', 'Bob');
});

// ── Sort ──────────────────────────────────────────────────────────────────────

it('sorts ascending', function (): void {
    Guest::create(['name' => 'Charlie', 'email' => 'c@x.com', 'age' => 30, 'status' => 'active']);
    Guest::create(['name' => 'Alice',   'email' => 'a@x.com', 'age' => 25, 'status' => 'active']);
    Guest::create(['name' => 'Bob',     'email' => 'b@x.com', 'age' => 17, 'status' => 'active']);

    expect(Guest::filter(['sort' => 'name'])->get()->pluck('name')->toArray())
        ->toBe(['Alice', 'Bob', 'Charlie']);
});

it('sorts descending when prefixed with -', function (): void {
    Guest::create(['name' => 'Charlie', 'email' => 'c@x.com', 'age' => 30, 'status' => 'active']);
    Guest::create(['name' => 'Alice',   'email' => 'a@x.com', 'age' => 25, 'status' => 'active']);
    Guest::create(['name' => 'Bob',     'email' => 'b@x.com', 'age' => 17, 'status' => 'active']);

    expect(Guest::filter(['sort' => '-name'])->get()->pluck('name')->toArray())
        ->toBe(['Charlie', 'Bob', 'Alice']);
});

it('sorts by multiple fields', function (): void {
    Guest::create(['name' => 'Alice', 'email' => 'a@x.com', 'age' => 25, 'status' => 'inactive']);
    Guest::create(['name' => 'Bob',   'email' => 'b@x.com', 'age' => 17, 'status' => 'active']);
    Guest::create(['name' => 'Carol', 'email' => 'c@x.com', 'age' => 25, 'status' => 'active']);

    expect(Guest::filter(['sort' => '-age,name'])->get()->pluck('name')->toArray())
        ->toBe(['Alice', 'Carol', 'Bob']);
});

it('combines filters and sort', function (): void {
    Guest::create(['name' => 'Alice', 'email' => 'a@x.com', 'age' => 25, 'status' => 'active']);
    Guest::create(['name' => 'Bob',   'email' => 'b@x.com', 'age' => 30, 'status' => 'active']);
    Guest::create(['name' => 'Carol', 'email' => 'c@x.com', 'age' => 20, 'status' => 'inactive']);

    $results = Guest::filter(['filters' => ['status' => 'active'], 'sort' => '-age'])->get();

    expect($results)->toHaveCount(2)
        ->and($results->pluck('name')->toArray())->toBe(['Bob', 'Alice']);
});

it('applies sort even when $filterable is empty', function (): void {
    Guest::create(['name' => 'Charlie', 'email' => 'c@x.com', 'age' => 30, 'status' => 'active']);
    Guest::create(['name' => 'Alice',   'email' => 'a@x.com', 'age' => 25, 'status' => 'active']);

    $model = new class extends \Illuminate\Database\Eloquent\Model {
        use \Laraditz\ModelFilter\Filterable;
        protected $table = 'users';
        protected array $filterable = [];
    };

    expect($model::filter(['sort' => 'name'])->get()->pluck('name')->first())->toBe('Alice');
});
