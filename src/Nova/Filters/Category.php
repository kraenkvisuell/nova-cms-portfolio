<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class Category extends Filter
{
    public function name()
    {
        return __('nova-cms-portfolio::categories.category');
    }

    public $component = 'select-filter';

    public function apply(Request $request, $query, $value)
    {
        return $query;
    }

    public function options(Request $request)
    {
        ray($request);

        return [];
    }

    public function default()
    {
        return '';
    }
}
