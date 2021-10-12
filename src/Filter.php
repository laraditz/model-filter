<?php

namespace Laraditz\ModelFilter;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Filter
{
    /**
     * The query builder.
     *
     * @var object
     */
    public $query;

    /**
     * The request data.
     *
     * @var array
     */
    public $data;

    /**
     * Receive query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $data
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function __construct(Builder $query, $data = [])
    {
        $this->query = $query;
        $this->data = $data;
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  string  $name
     * @param  array  $arguments
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function __call($name, $arguments)
    {
        $this->query->$name(...$arguments);
    }

    /**
     * Add sort function through query string.
     *
     * @param  string  $value
     * 
     */
    public function sort(string $value)
    {
        return collect(explode(',', $value))->each(function ($item, $key) {
            if (Str::startsWith($item, '-')) {
                return $this->query->orderByDesc(Str::of($item)->replace('-', ''));
            } else {
                return $this->query->orderBy($item);
            }
        });
    }
}
