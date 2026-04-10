# Laravel Model Filter

[![Latest Stable Version](https://poser.pugx.org/laraditz/model-filter/v/stable?format=flat-square)](https://packagist.org/packages/laraditz/model-filter)
[![Total Downloads](https://img.shields.io/packagist/dt/laraditz/model-filter?style=flat-square)](https://packagist.org/packages/laraditz/model-filter)
[![License](https://poser.pugx.org/laraditz/model-filter/license?format=flat-square)](https://packagist.org/packages/laraditz/model-filter)

A flexible Eloquent model filter for Laravel with operator support, OR grouping, and relationship filtering.

> **v2 breaking change:** Filter params are now namespaced under `filters[]`. See the breaking changes section below.

## Requirements

- PHP 8.1+
- Laravel 9–13

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

Optionally create `App/Filters/UserFilter` to override specific fields:

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

# Multiple filters — all AND'd together
?filters[status]=active&filters[age][gte]=18

# Multiple operators on same field
?filters[age][gte]=18&filters[age][lte]=65

# OR group — produces: AND (name LIKE '%far%' OR email LIKE '%far%')
?filters[or][name][like]=far&filters[or][email][like]=far

# Relationship filtering
?filters[role.name][eq]=admin

# Sort — top-level, not inside filters[]
?sort=name,-created_at
```

### Supported operators

| Operator  | SQL equivalent      | Notes                                 |
|-----------|---------------------|---------------------------------------|
| `eq`      | `= ?`               | Default when no operator bracket used |
| `neq`     | `!= ?`              |                                       |
| `like`    | `LIKE ?`            | Value wrapped in `%…%` automatically  |
| `gt`      | `> ?`               |                                       |
| `gte`     | `>= ?`              |                                       |
| `lt`      | `< ?`               |                                       |
| `lte`     | `<= ?`              |                                       |
| `in`      | `IN (?)`            | Comma-separated string → array        |
| `between` | `BETWEEN ? AND ?`   | Comma-separated, exactly 2 values     |

### Security

Only fields listed in `$filterable` can be filtered. Unlisted fields are silently ignored. Values are bound via PDO prepared statements — no SQL injection risk.

## Breaking Changes from v1

| Area | v1 | v2 |
|------|----|----|
| Query params | `?name=farhan` | `?filters[name]=farhan` |
| Laravel support | 7–12 | 9–13 |
| PHP minimum | 7.4 | 8.1 |
| Lumen | Supported | Dropped |
| `$filterable` empty | All params passed through | All filtering disabled |
| `Filter::__call__` | Auto-proxied to Builder | Removed — use `$this->query` directly |
| `Filter::sort()` | On Filter base class | Moved to Filterable trait |

## Credits

- [Raditz Farhan](https://github.com/raditzfarhan)

## License

MIT. Please see the [license file](LICENSE) for more information.
