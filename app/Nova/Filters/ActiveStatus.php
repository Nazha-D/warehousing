<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ActiveStatus extends Filter
{
    /**
     * The filter's display name.
     */
    public $name = 'Active Status';

    /**
     * Apply the filter to the given query.
     */
    public function apply(Request $request, $query, $value)
    {
        if ($value === 'active') {
            return $query->where('is_active', 1);
        }

        if ($value === 'inactive') {
            return $query->where('is_active', 0);
        }

        return $query; // All
    }

    /**
     * The options for the filter.
     */
    public function options(Request $request)
    {
        return [
            'Active' => 'active',
            'Inactive' => 'inactive',
            'All Users' => 'all',
        ];
    }
}
