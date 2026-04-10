<?php

declare(strict_types=1);

namespace Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Laraditz\ModelFilter\Filterable;

class Guest extends Model
{
    use Filterable;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'age', 'status', 'role_id'];

    protected array $filterable = ['name', 'email', 'age', 'status'];
}
