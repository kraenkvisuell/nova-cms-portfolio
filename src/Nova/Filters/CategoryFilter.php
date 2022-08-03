<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Filters;

use Illuminate\Http\Request;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;
use Laravel\Nova\Filters\Filter;

class CategoryFilter extends Filter
{
    public function name()
    {
        return __('nova-cms-portfolio::categories.category');
    }

    public $component = 'select-filter';

    public function apply(Request $request, $query, $value)
    {
        $query->whereHas('categories', function ($b) use ($value) {
            $b->where('id', $value);
        });

        return $query;
    }

    public function options(Request $request)
    {
        $options = [];

        foreach (Artist::find($request->viaResourceId)->categories as $category) {
            $options[$category->title] = $category->id;
        }

        return $options;
    }

    public function default()
    {
        return Artist::find(request()->viaResourceId)?->categories?->first()?->id;
    }
}
