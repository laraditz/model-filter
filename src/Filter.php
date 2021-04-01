<?php

namespace Laraditz\ModelFilter;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Builder;

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
        if (method_exists($this->query, $name)) {
            $this->query->$name(...$arguments);
        } else {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.',
                get_class(),
                $name
            ));
        }
    }
}
