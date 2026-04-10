# Laravel Model Filter

[![Latest Stable Version](https://poser.pugx.org/laraditz/model-filter/v/stable?format=flat-square)](https://packagist.org/packages/laraditz/model-filter)
[![Total Downloads](https://img.shields.io/packagist/dt/laraditz/model-filter?style=flat-square)](https://packagist.org/packages/laraditz/model-filter)
[![License](https://poser.pugx.org/laraditz/model-filter/license?format=flat-square)](https://packagist.org/packages/laraditz/model-filter)

A flexible Eloquent model filter for Laravel with operator support, OR grouping, and relationship filtering.

> **v2 breaking change:** Filter params are now namespaced under `filters[]`. See the breaking changes section below.

## Requirements

- PHP 8.1+
- Laravel 9 - 13

## Installation

```bash
composer require laraditz/model-filter
```

## Setup

Add the `Filterable` trait to your model and declare a `$filterable` allowlist:

```php
use Laraditz\ModelFilter\Filterable;

class User extends Model
{
    use Filterable;

    protected array $filterable = [
        'name',
        'email',
        'age',
        'role.name',  // dot-notation opts in to relationship filtering
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
```

Optionally scaffold a filter class with the Artisan command:

```bash
php artisan make:filter UserFilter
```

This creates `app/Filters/UserFilter.php`. Use `--force` to overwrite an existing file.

Then add your custom methods:

```php
namespace App\Filters;

use Laraditz\ModelFilter\Filter;

class UserFilter extends Filter
{
    public function name(mixed $value): void
    {
        $this->query->where('name', 'LIKE', $value . '%');
    }
}
```

Fields without a custom method are handled automatically.

> **Custom method precedence:** When a custom method exists for a field, it always takes precedence over auto-handling — and it receives only the **value**, not the operator. This means `?filters[age][gte]=18` will call `age('18')` and silently drop the `gte` operator. If you need operator-based filtering for a field, do not define a custom method for it and let auto-handling do the work.

## Usage

Pass `$request->all()` to `filter()`:

```php
$users = User::filter($request->all())->get();
```

### Query param format

```
# Shorthand (defaults to eq)
?filters[name]=farhan

# Explicit operator
?filters[age][gte]=18

# Multiple filters - all AND'd together
?filters[status]=active&filters[age][gte]=18

# Multiple operators on same field
?filters[age][gte]=18&filters[age][lte]=65

# OR group - produces: AND (name LIKE '%far%' OR email LIKE '%far%')
?filters[or][name][like]=far&filters[or][email][like]=far

# Relationship filtering
?filters[role.name][eq]=admin

# Sort - top-level, not inside filters[]
?sort=name,-created_at
```

### Supported operators

| Operator  | SQL equivalent    | Notes                                 |
| --------- | ----------------- | ------------------------------------- |
| `eq`      | `= ?`             | Default when no operator bracket used |
| `neq`     | `!= ?`            |                                       |
| `like`    | `LIKE ?`          | Value wrapped in `%…%` automatically  |
| `gt`      | `> ?`             |                                       |
| `gte`     | `>= ?`            |                                       |
| `lt`      | `< ?`             |                                       |
| `lte`     | `<= ?`            |                                       |
| `in`      | `IN (?)`          | Comma-separated string → array        |
| `between` | `BETWEEN ? AND ?` | Comma-separated, exactly 2 values     |

### Relationship filtering

Add the relationship column in dot-notation to `$filterable`:

```php
protected array $filterable = [
    'name',
    'role.name',   // enables ?filters[role.name][...]=...
];
```

The package translates `role.name` into a `whereHas('role', ...)` clause automatically. All operators work:

```
# Users whose role name equals "admin"
?filters[role.name][eq]=admin

# Users whose role name contains "mod"
?filters[role.name][like]=mod

# Combined with a direct field filter
?filters[name][like]=john&filters[role.name][eq]=admin
```

Only one level of nesting is supported (`relation.column`). Deeper nesting (e.g. `post.comments.body`) is not supported.

#### Custom relationship query

If you need more control — for example filtering on a condition that spans multiple columns of the related model — define a custom method in your filter class:

```php
class UserFilter extends Filter
{
    /**
     * ?filters[active_admin]=1
     * Finds users with an active role named "admin".
     */
    public function activeAdmin(mixed $value): void
    {
        if (! $value) {
            return;
        }

        $this->query->whereHas('role', function ($q): void {
            $q->where('name', 'admin')
              ->where('active', true);
        });
    }
}
```

Remember to add the key to `$filterable`:

```php
protected array $filterable = ['active_admin'];
```

### Security

Only fields listed in `$filterable` can be filtered. Unlisted fields are silently ignored. Values are bound via PDO prepared statements - no SQL injection risk.

## Breaking Changes from v1

| Area                | v1                        | v2                                    |
| ------------------- | ------------------------- | ------------------------------------- |
| Query params        | `?name=farhan`            | `?filters[name]=farhan`               |
| Laravel support     | 7–12                      | 9–13                                  |
| PHP minimum         | 7.4                       | 8.1                                   |
| Lumen               | Supported                 | Dropped                               |
| `$filterable` empty | All params passed through | All filtering disabled                |
| `Filter::__call__`  | Auto-proxied to Builder   | Removed — use `$this->query` directly |
| `Filter::sort()`    | On Filter base class      | Moved to Filterable trait             |

## Credits

- [Raditz Farhan](https://github.com/raditzfarhan)

## License

MIT. Please see the [license file](LICENSE) for more information.
