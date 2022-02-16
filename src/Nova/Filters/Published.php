<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class Published extends Filter
{
    public function name()
    {
        return __('Veröffentlicht');
    }

    public $component = 'select-filter';

    public function apply(Request $request, $query, $value)
    {
        if ($value == 'published_only') {
            $query->where('is_published', true);
        } elseif ($value == 'unpublished_only') {
            $query->where('is_published', false);
        }

        return $query;
    }

    public function options(Request $request)
    {
        return [
            __('nur Veröffentlichte') => 'published_only',
            __('nur Nicht Veröffentlichte') => 'unpublished_only',
        ];
    }

    public function default()
    {
        return '';
    }
}
