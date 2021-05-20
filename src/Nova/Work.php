<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Laravel\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Stack;
use Kraenkvisuell\NovaCmsMedia\API;
use Laravel\Nova\Http\Requests\NovaRequest;
use Kraenkvisuell\NovaCmsMedia\MediaLibrary;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BooleanGroup;
use OptimistDigital\NovaSortable\Traits\HasSortableRows;

class Work extends Resource
{
    use HasSortableRows;
    
    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Work::class;

    public static $title = 'title';

    public static $sortable = false;

    public static $searchable = false;

    public static $displayInNavigation = false;

    public static $perPageViaRelationship = 1000;

    public static function label()
    {
        return config('nova-cms-portfolio.custom_works_label') ?: __('nova-cms-portfolio::works.works');
    }

    public static function singularLabel()
    {
        return config('nova-cms-portfolio.custom_work_label') ?: __('nova-cms-portfolio::works.work');
    }

    public function fields(Request $request)
    {
        return [
            MediaLibrary::make(__('nova-cms::content_blocks.file'), 'file')
                ->uploadOnly(),
                
            Stack::make('Details', [
                Line::make('', function () {
                    return API::getOriginalName($this->file);
                })->asBase(),
            ]),
            
            Text::make(__('nova-cms::pages.title'), 'title'),

            Boolean::make(__('nova-cms-portfolio::works.is_artist_portfolio_image'), 'is_artist_portfolio_image'),

            BooleanGroup::make(
                __('nova-cms-portfolio::works.represents_artist_in_discipline'),
                'represents_artist_in_discipline'
            )
                ->options(function () {
                    return optional(optional(optional($this->slideshow)->artist)->disciplines)->pluck('title', 'id');
                })
                ->onlyOnForms(),

            BooleanGroup::make(
                __('nova-cms-portfolio::works.represents_artist_in_discipline_category'),
                'represents_artist_in_discipline_category'
            )
                ->options(function () {
                    $disciplineId = optional(optional($this->slideshow)->getDiscipline())->id;
                    $options = [];

                    foreach (optional($this->slideshow)->categories ?: [] as $category) {
                        $options[$disciplineId.'_'.$category->id] = $category->title;
                    }

                    return $options;
                })
                ->onlyOnForms(),
        ];
    }

    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return '/resources/slideshows/'.$resource->slideshow_id;
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/slideshows/'.$resource->slideshow_id;
    }
}
