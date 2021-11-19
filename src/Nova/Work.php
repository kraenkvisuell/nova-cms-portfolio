<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\NovaCmsMedia\API;
use Kraenkvisuell\NovaCmsMedia\MediaLibrary;
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Actions\ToggleArtistDisciplineImage;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Actions\ToggleArtistPortfolioImage;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Actions\ToggleRepresentsArtistInCategory;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Actions\ToggleShowInOverview;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Actions\ToggleShowInOverviewCategory;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
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

    public static $orderBy = [
        'sort_order' => 'asc',
    ];

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
                    if ($this->show_in_overview) {
                        return '<span class="text-xs font-bold uppercase">'
                        .__('nova-cms-portfolio::works.show_in_overview')
                        .'</span>';
                    }

                    return '';
                })->asHtml(),

                Line::make('', function () {
                    if (is_array($this->overviewCategorySlugs()) && $this->overviewCategorySlugs()) {
                        return '<span class="text-xs uppercase">'
                        .__('nova-cms-portfolio::works.overview_categories')
                        .': <span class="font-bold">'
                        .implode(', ', $this->overviewCategorySlugs())
                        .'</span></span>';
                    }

                    return '';
                })->asHtml(),

                Line::make('', function () {
                    if (is_array($this->representationCategorySlugs()) && $this->representationCategorySlugs()) {
                        return '<span class="text-xs uppercase">'
                        .__('nova-cms-portfolio::works.representation_categories')
                        .': <span class="font-bold">'
                        .implode(', ', $this->representationCategorySlugs())
                        .'</span></span>';
                    }

                    return '';
                })->asHtml(),

                Line::make('', function () {
                    if ($this->is_artist_portfolio_image) {
                        return '<span class="text-xs font-bold uppercase">'
                        .__('nova-cms-portfolio::works.is_artist_portfolio_image')
                        .'</span>';
                    }

                    return '';
                })->asHtml(),

                Line::make('', function () {
                    if ($this->is_artist_discipline_image) {
                        return '<span class="text-xs font-bold uppercase">'
                        .__('nova-cms-portfolio::works.is_artist_discipline_image')
                        .'</span>';
                    }

                    return '';
                })->asHtml(),
            ]),

            Textarea::make(__('nova-cms-portfolio::works.embed_code'), 'embed_code')
                ->onlyOnForms(),

            Text::make(__('nova-cms-portfolio::works.embed_code_ratio'), 'embed_code_ratio')
                ->onlyOnForms(),

            Text::make(__('nova-cms::pages.title'), 'title')
                ->onlyOnForms(),

            Boolean::make(__('nova-cms-portfolio::works.show_in_overview'), 'show_in_overview')
                ->onlyOnForms(),

            BooleanGroup::make(
                __('nova-cms-portfolio::works.show_in_overview_category'),
                'show_in_overview_category'
            )
                ->options(function () use ($request) {
                    $slideshow = $this->slideshow ?: Slideshow::find($request->viaResourceId);
                    $options = [];

                    foreach (optional($slideshow)->categories ?: [] as $category) {
                        $options[$category->id] = $category->title;
                    }

                    return $options;
                })
                ->onlyOnForms(),

            Boolean::make(__('nova-cms-portfolio::works.is_artist_portfolio_image'), 'is_artist_portfolio_image')
                ->onlyOnForms(),

            Boolean::make(__('nova-cms-portfolio::works.is_artist_discipline_image'), 'is_artist_discipline_image')
                ->onlyOnForms(),

            Select::make(__('nova-cms-portfolio::works.width_in_overview'), 'width_in_overview')
                ->options([
                    'regular' => 'one column',
                    'double' => 'two columns',
                ])
                ->onlyOnForms()
                ->default('regular')
                ->required(),

            Select::make(__('nova-cms-portfolio::works.width_in_frame'), 'width_in_frame')
                ->options([
                    'full' => 'full width',
                    'two_thirds' => 'two thirds',
                    'half' => 'half',
                ])
                ->onlyOnForms()
                ->default('full')
                ->required(),

            BooleanGroup::make(
                __('nova-cms-portfolio::works.represents_artist_in_discipline_category'),
                'represents_artist_in_discipline_category'
            )
                ->options(function () use ($request) {
                    $slideshow = $this->slideshow ?: Slideshow::find($request->viaResourceId);

                    $disciplineId = optional(optional($slideshow)->getDiscipline())->id;

                    $options = [];

                    foreach (optional($slideshow)->categories ?: [] as $category) {
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
        $sendActions = [
            ToggleShowInOverview::make()
                ->onlyOnTableRow()
                ->withoutConfirmation(),

            ToggleArtistPortfolioImage::make()
                ->onlyOnTableRow()
                ->withoutConfirmation(),

            ToggleArtistDisciplineImage::make()
                ->onlyOnTableRow()
                ->withoutConfirmation(),
        ];

        if ($request->viaResourceId) {
            $categoryIds = Cache::remember('novaSlideshowCategoryIds.'.$request->viaResourceId, now()->addSeconds(15), function () use ($request) {
                return Slideshow::find($request->viaResourceId)->categories->pluck('id');
            });
            session(['lastNovaSlideshowCategoryIds' => $categoryIds]);
        }

        foreach (session('lastNovaSlideshowCategoryIds') ?: [] as $categoryId) {
            $sendActions[] = ToggleShowInOverviewCategory::make($categoryId)
            ->onlyOnTableRow()
            ->withoutConfirmation();
        }

        foreach (session('lastNovaSlideshowCategoryIds') ?: [] as $categoryId) {
            $sendActions[] = ToggleRepresentsArtistInCategory::make($categoryId)
            ->onlyOnTableRow()
            ->withoutConfirmation();
        }

        return $sendActions;
    }
}
