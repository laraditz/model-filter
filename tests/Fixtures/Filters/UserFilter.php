<?php

declare(strict_types=1);

namespace App\Filters;

use Laraditz\ModelFilter\Filter;

class UserFilter extends Filter
{
    /**
     * Override status to use a 'custom_' prefix.
     * When caller passes status='active', this queries for status='custom_active'.
     * Seeding a record with status='custom_active' proves this method ran.
     */
    public function status(mixed $value): void
    {
        $this->query->where('status', 'custom_' . $value);
    }
}
