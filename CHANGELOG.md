# Release Notes

All notable changes to `Laravel Model Filter` will be documented in this file.

## Version 2.0.0 (2026-04-10)

### Added

- Operator-based filtering: `eq`, `neq`, `like`, `gt`, `gte`, `lt`, `lte`, `in`, `between`
- Multiple operators per field (e.g. `filters[age][gte]=18&filters[age][lte]=65`)
- OR grouping via `filters[or][field][op]=value` — produces `AND (...OR...)`
- Relationship filtering via dot-notation in `$filterable` (e.g. `role.name`)
- `FilterCondition` value object
- `FilterInput` static parser with field and operator allowlists
- `php artisan make:filter {name}` command to scaffold filter classes (`--force` to overwrite)
- `ModelFilterServiceProvider` — auto-discovered via Composer; no manual registration needed
- Full test suite (Pest)

### Changed

- Filter params namespaced under `filters[]` (breaking)
- `$filterable` required to enable any filtering (breaking)
- Sort now applied independently of `$filterable`
- `sort()` moved from `Filter` base class to `Filterable` trait (breaking)
- PHP minimum raised to 8.1
- Laravel minimum raised to 9.x; Laravel 7 and 8 dropped
- Lumen support dropped
- `illuminate/http` dependency removed

### Removed

- `Filter::__call__` magic proxy (breaking) — use `$this->query` directly
- `Filter::sort()` method (breaking)

## Version 1.0.4 (2025-11-10)

### Added

- Add support for Laravel 12.

## Version 1.0.4 (2024-05-15)

### Added

- Add support for Laravel 11.

## Version 1.0.3 (2023-04-14)

### Added

- Add support for Laravel 10.

### Changed

- Remove support for Laravel 6.

## Version 1.0.2 (2022-08-20)

### Added

- Add support for PHP8 and Laravel 8 nad Laravel 9.

## Version 1.0.1 (2021-04-01)

### Added

- Add `sort` function.

## Version 1.0.0 (2021-04-01)

### Added

- Initial commit.

### Changed

- Copied from Saring.
