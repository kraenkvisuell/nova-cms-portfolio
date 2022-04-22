<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Eminiarts\Tabs\Tabs;
use Eminiarts\Tabs\TabsOnEdit;
use Illuminate\Http\Request;
use Kraenkvisuell\NovaCms\Tabs\Seo;
use KraenkVisuell\NovaSortable\Traits\HasSortableRows;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Manogi\Tiptap\Tiptap;
use Timothyasp\Color\Color;

class Discipline extends Resource
{
    use TabsOnEdit;
    use HasSortableRows;

    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Discipline::class;

    public static $sortable = false;

    public static $searchable = false;

    public function title()
    {
        return $this->resource->title;
    }

    public static function label()
    {
        return ucfirst(__('nova-cms-portfolio::disciplines.disciplines'));
    }

    public static function singularLabel()
    {
        return ucfirst(__('nova-cms-portfolio::disciplines.discipline'));
    }

    public function fields(Request $request)
    {
        $tabs = [];

        $tabs[__('nova-cms::settings.settings')] = [
            Text::make(__('nova-cms-portfolio::portfolio.title'), 'title')
                ->rules('required')
                ->translatable()
                ->onlyOnForms(),

            Text::make(__('nova-cms::pages.slug'), 'slug')
                ->rules('required')
                ->translatable()
                ->help(__('nova-cms-portfolio::artists.slug_explanation'))
                ->onlyOnForms(),

            Color::make(__('nova-cms-portfolio::portfolio.background_color'), 'bgcolor')
                ->sketch()
                ->hideFromDetail(),
        ];

        $tabs[__('nova-cms::pages.content')] = [
            TipTap::make(__('nova-cms-portfolio::portfolio.description'), 'description')
            ->onlyOnForms(),
        ];

        $tabs[__('nova-cms::seo.seo')] = Seo::make();

        return [
            Stack::make('Details', [
                Line::make('', 'title')->asBase(),
                Line::make('', function () {
                    return '/'.$this->slug;
                })->asSmall(),
            ]),

            (new Tabs(static::singularLabel(), $tabs))->withToolbar(),
        ];
    }

    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return '/resources/disciplines';
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/disciplines';
    }
}
