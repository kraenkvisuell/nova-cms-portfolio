<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Laravel\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Category extends Resource
{
    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Category::class;

    public static $sortable = false;

    public static $searchable = false;

    public function title()
    {
        return $this->resource->title;
    }

    public static function label()
    {
        return ucfirst(__('nova-cms-portfolio::categories.categories'));
    }

    public static function singularLabel()
    {
        return ucfirst(__('nova-cms-portfolio::categories.category'));
    }

    public function fields(Request $request)
    {
        return [

            Text::make(__('nova-cms-portfolio::portfolio.title'), 'title')
                ->rules('required')
                ->translatable(),

            Text::make(__('nova-cms::pages.slug'), 'slug')
                ->rules('required')
                ->translatable()
                ->help(__('nova-cms-portfolio::artists.slug_explanation')),
        ];
    }

    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return '/resources/categories';
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/categories';
    }
}
