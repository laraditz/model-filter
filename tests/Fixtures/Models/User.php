<?php

declare(strict_types=1);

namespace Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laraditz\ModelFilter\Filterable;

class User extends Model
{
    use Filterable;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'age', 'status', 'role_id'];

    protected array $filterable = ['name', 'email', 'age', 'status', 'role.name'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
