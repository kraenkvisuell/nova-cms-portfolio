<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Illuminate\Http\Request;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Resource;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Manogi\Tiptap\Tiptap;

class Category extends Resource
{
    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Category::class;

    // public static $sortable = false;

    public static function orderBy()
    {
        return [
            'title->'.app()->getLocale() => 'asc',
        ];
    }

    //public static $searchable = false;

    public static $perPageOptions = [100, 200];

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
                ->translatable(),

            Text::make(__('nova-cms::pages.slug'), 'slug')
                ->translatable()
                ->help(__('nova-cms-portfolio::artists.slug_explanation')),

            TipTap::make(__('nova-cms-portfolio::categories.description'), 'description')
                ->translatable()
                ->onlyOnForms(),

            Boolean::make(__('nova-cms-portfolio::categories.show_in_home_navi'), 'show_in_home_navi')
                ->onlyOnForms(),

            Boolean::make(__('nova-cms-portfolio::categories.show_in_main_menu'), 'show_in_main_menu')
                ->onlyOnForms(),
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
