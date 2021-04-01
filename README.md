# Laravel Model Filter

[![Latest Stable Version](https://poser.pugx.org/laraditz/model-filter/v/stable?format=flat-square)](https://packagist.org/packages/laraditz/model-filter)
[![Total Downloads](https://img.shields.io/packagist/dt/laraditz/model-filter?style=flat-square)](https://packagist.org/packages/laraditz/model-filter)
[![License](https://poser.pugx.org/laraditz/model-filter/license?format=flat-square)](https://packagist.org/packages/laraditz/model-filter)
[![StyleCI](https://github.styleci.io/repos/7548986/shield?style=square)](https://github.com/laraditz/model-filter)

A simple eloquent model filter for Laravel and Lumen.

## Installation

Via Composer

``` bash
$ composer require laraditz/model-filter
```

## Configuration

Add filterable trait to your model as below snippet:
```php
use Laraditz\ModelFilter\Filterable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Filterable;
    ...
}
```

Create filter class under the `App/Filters` folder with `<model_name>Filter` format. For example for `User` model, you will need to create `UserFilter` class. 

Below snippet shows how the `UserFilter` could look like:
```php
namespace App\Filters;

use Laraditz\ModelFilter\Filter;
use Illuminate\Database\Eloquent\Builder;

class UserFilter extends Filter
{
    public function name(string $value)
    {
        $this->where('name', 'LIKE', $value);
    }

    public function email(string $value)
    {
        $this->where('email', 'LIKE', "%$value%");
    }

    // Filter relationship
    public function rank($value)
    {
        $this->whereHas('rank', function (Builder $query) use ($value) {
            $query->where('level', 'like', $value);
        });
    }
}

```

If you want to have more control on which attributes can be filtered, you can add `filterable` array to you model:
```php

protected $filterable = [
    'name', 'email'
];
```

## Usage

In your controller, call `filter` method and pass the input data to use the filter that you have created.
```php
$users = User::filter($request->all())->get();
```

That's it!

## Credits

- [Raditz Farhan](https://github.com/raditzfarhan)

## License

MIT. Please see the [license file](LICENSE) for more information.