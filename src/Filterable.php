<?php

namespace Laraditz\ModelFilter;

use Illuminate\Support\Str;

trait Filterable
{
    /**
     * Filter scope to receive request data.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $data
     * @return void
     */
    public function scopeFilter($query, array $data = [])
    {
        $class_name = class_basename($this);
        $filter_class = 'App\\Filters\\' . $class_name . 'Filter';

        if ($this->filterable && count($this->filterable) > 0) {
            $filterable = $this->filterable;

            $data = collect($data)->map(function ($name, $key) use ($filterable) {
                if (in_array($key, $filterable)) {
                    return $name;
                }
            })->reject(function ($name) {
                return empty($name);
            })->toArray();
        }

        if (class_exists($filter_class)) {
            $filter = new $filter_class($query, $data);

            foreach ($data as $key => $value) {
                $method_name = Str::camel($key);

                if (method_exists($filter, $method_name)) {
                    $filter->$method_name($value);
                }
            }
        }
    }
}
