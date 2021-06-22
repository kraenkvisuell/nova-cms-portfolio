<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Laravel\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Boolean;
use Kraenkvisuell\NovaCmsMedia\API;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Http\Requests\NovaRequest;
use Kraenkvisuell\NovaCmsMedia\MediaLibrary;
use OptimistDigital\NovaSortable\Traits\HasSortableRows;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Actions\ToggleArtistPortfolioImage;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Actions\ToggleArtistDisciplineImage;

class Work extends Resource
{
    use HasSortableRows;
    
    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Work::class;

    public static $title = 'title';

    public static $sortable = false;

    public static $searchable = false;

    public static $displayInNavigation = false;

    public static $perPageViaRelationship = 1000;

    public static function indexQuery(NovaRequest $request, $query)
    {
        $query = parent::indexQuery($request, $query);

        return $query->with(['slideshow.categories', 'slideshow.artist']);
    }

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

            Stack::make('Settings', [
                Line::make('', function () {
                    if ($this->is_artist_portfolio_image) {
                        return '<span class="text-sm font-bold uppercase">'
                        . __('nova-cms-portfolio::works.is_artist_portfolio_image')
                        . '</span>';
                    }
                    return '';
                })->asHtml(),

                Line::make('', function () {
                    if ($this->is_artist_discipline_image) {
                        return '<span class="text-sm font-bold uppercase">'
                        . __('nova-cms-portfolio::works.is_artist_discipline_image')
                        . '</span>';
                    }
                    return '';
                })->asHtml(),
            ]),
            
            Text::make(__('nova-cms::pages.title'), 'title')
                ->onlyOnForms(),

            Boolean::make(__('nova-cms-portfolio::works.is_artist_portfolio_image'), 'is_artist_portfolio_image')
                ->onlyOnForms(),

            Boolean::make(__('nova-cms-portfolio::works.is_artist_discipline_image'), 'is_artist_discipline_image')
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

    public function actions(Request $request)
    {
        return [
            ToggleArtistPortfolioImage::make()
                ->onlyOnTableRow()
                ->withoutConfirmation(),

            ToggleArtistDisciplineImage::make()
                ->onlyOnTableRow()
                ->withoutConfirmation(),
        ];
    }
}
