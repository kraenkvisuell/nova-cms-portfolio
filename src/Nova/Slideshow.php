<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Illuminate\Http\Request;
use Kraenkvisuell\BelongsToManyField\BelongsToManyField;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;
use Kraenkvisuell\NovaCmsPortfolio\Models\Discipline;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Actions\ToggleSlideshowIsPublished;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Actions\ToggleVisibilityInOverview;
use Kraenkvisuell\NovaCmsPortfolio\QuickWorksCard;
use Kraenkvisuell\NovaCmsPortfolio\SlideshowArtistCard;
use Kraenkvisuell\NovaCmsPortfolio\Traits\OptionalHasSortableRows;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use OwenMelbz\RadioField\RadioButton;

class Slideshow extends Resource
{
    use OptionalHasSortableRows;

    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow::class;

    public static $title = 'title';

    public static $sortable = false;

    public static $searchable = false;

    public static $displayInNavigation = false;

    public static $perPageViaRelationship = 1000;

    public static function sortableHasDropdown()
    {
        return config('nova-cms-portfolio.slideshows_sortable_dropdown') ?: false;
    }

    public static function label()
    {
        return config('nova-cms-portfolio.custom_slideshows_label')
            ?: ucfirst(__('nova-cms-portfolio::slideshows.slideshows'));
    }

    public static function singularLabel()
    {
        return config('nova-cms-portfolio.custom_slideshow_label')
            ?: ucfirst(__('nova-cms-portfolio::slideshows.slideshow'));
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        $query->with(['works', 'categories', 'artist']);

        return $query;
    }

    public static function formQuery(NovaRequest $request, $query)
    {
        return $query->with(['works', 'categories', 'artist']);
    }

    public function fields(Request $request)
    {
        $workLabel = config('nova-cms-portfolio.custom_works_label')
            ?: __('nova-cms-portfolio::works.works');

        $workSingularLabel = config('nova-cms-portfolio.custom_work_label')
            ?: __('nova-cms-portfolio::works.work');

        $visibleInArtistOverviewLabel = config('nova-cms-portfolio.custom_visible_in_artist_overview_label')
            ?: ucfirst(__('nova-cms-portfolio::slideshows.visible_in_artist_overview'));

        $fields = [
            Stack::make('Details', [
                Line::make('', function () use ($visibleInArtistOverviewLabel) {
                    $html = '<div class="font-bold leading-tight mb-1 whitespace-normal">'.$this->title.'</div>';

                    $html .= '<div class="whitespace-normal mb-1">';
                    foreach ($this->categories as $n => $category) {
                        $html .= '<div class="inline-block mr-1 leading-tight text-80 uppercase text-xs border border-80 px-1  pt-1 pb-px">'.$category->title.'</div>';
                    }
                    $html .= '</div>';

                    if (! $this->is_published) {
                        $html .= '<div class="font-bold text-xs text-60 line-through uppercase">'
                            .__('nova-cms-portfolio::portfolio.published')
                            .'</div>';
                    }
                    if (! $this->is_visible_in_overview) {
                        $html .= '<div class="font-bold text-xs text-60 line-through uppercase">'
                            .$visibleInArtistOverviewLabel
                            .'</div>';
                    }

                    return $html;
                })->asHtml(),
            ])
            ->onlyOnIndex(),

            Text::make(__('nova-cms::pages.title'), 'title')
                ->rules('required')
                ->hideFromIndex(),

            Select::make(ucfirst(__('nova-cms-portfolio::disciplines.discipline')), 'discipline_id')
                ->nullable()
                ->options(function () {
                    return Discipline::with([
                        'artists' => function ($b) {
                            return $b->select('id');
                        },
                    ])
                    ->get()
                    ->filter(function ($discipline) {
                        return $discipline->artists->where('id', $this->artist_id)->count();
                    })
                    ->pluck('title', 'id');
                })
                ->help('Eventuell notwendig wenn der Künstler mehrere Disziplinen hat')
                ->onlyOnForms(),

            BelongsToManyField::make('Kategorien', 'categories', Category::class)
                ->optionsLabel('title')
                ->required()
                ->rules('required')
                ->onlyOnForms(),

            Stack::make('', [
                Text::make('', function () {
                    $html = '<div
                        class="block whitespace-normal"
                    >';
                    foreach ($this->works->take(config('nova-cms-portfolio.max_thumbnails') ?: 3) as $work) {
                        $html .= '<a
                            href="'.nova_cms_file($work->file).'"
                            download
                        >';

                        if (nova_cms_mime($work->file) == 'video') {
                            $html .= '<video
                                autoplay muted loop playsinline
                                class="w-auto h-12 mr-1 inline-block"
                            >
                                <source src="'.nova_cms_file($work->file).'" type="video/'.nova_cms_extension($work->file).'">
                            </video>';
                        } else {
                            $html .= '<img
                                class="w-auto h-12 mr-1 inline-block"
                                src="'.nova_cms_image($work->file, 'thumb').'"
                            />';
                        }

                        $html .= '</a>';
                    }
                    $html .= '</div>';

                    return $html;
                })->asHtml(),

                // Line::make('', function () {
                //     if ($this->works->where('is_artist_discipline_image', true)->count()) {
                //         return '<div class="text-xs font-bold uppercase">'
                //         .__('nova-cms-portfolio::works.is_artist_discipline_image')
                //         .'</div>';
                //     }

                //     return '';
                // })->asHtml(),

                // Line::make('', function () {
                //     if ($this->works->where('is_artist_portfolio_image', true)->count()) {
                //         return '<div class="text-xs font-bold uppercase">'
                //         .__('nova-cms-portfolio::works.is_artist_portfolio_image')
                //         .'</div>';
                //     }

                //     return '';
                // })->asHtml(),
            ])
            ->onlyOnIndex(),

            Stack::make('', [
                Line::make('', function () use ($workLabel, $workSingularLabel) {
                    return '<button
                        onclick="window.location.href=\'/nova/resources/slideshows/'.$this->id.'\'"
                        class="btn btn-xs
                        '.($this->works->count() ? 'btn-primary' : 'btn-danger').'
                        "
                        >'
                        .$this->works->count().' '.($this->works->count() != 1 ? $workLabel : $workSingularLabel)
                        .'</button>';
                })->asHtml(),
            ])
            ->onlyOnIndex(),

            Slug::make(__('nova-cms::pages.slug'), 'slug')->from('title')
                ->rules('required')
                ->onlyOnForms(),

            Boolean::make(ucfirst(__('nova-cms-portfolio::portfolio.published')), 'is_published')
                ->onlyOnForms(),

        ];

        if (config('nova-cms-portfolio.has_visible_in_artist_overview')) {
            $fields[] = Boolean::make($visibleInArtistOverviewLabel, 'is_visible_in_overview')
            ->onlyOnForms();
        }

        if (config('nova-cms-portfolio.has_starts_right')) {
            $fields[] = Boolean::make(ucfirst(__('nova-cms-portfolio::slideshows.starts_right')), 'starts_right')
                ->onlyOnForms();
        }

        if (config('nova-cms-portfolio.has_show_title')) {
            $fields[] = Boolean::make(ucfirst(__('nova-cms-portfolio::slideshows.show_title')), 'show_title')
                ->onlyOnForms();
        }

        if (config('nova-cms-portfolio.has_title_position')) {
            $fields[] = Select::make(__('nova-cms-portfolio::works.title_position'), 'title_position')
                ->options([
                    'bottom_left' => 'bottom left',
                    'bottom_right' => 'bottom right',
                    'top_left' => 'top left',
                    'top_right' => 'top right',
                ])
                ->default('bottom_left')
                ->required()
                ->onlyOnForms();
        }

        if (config('nova-cms-portfolio.has_break_after_in_overviews')) {
            $fields[] = Select::make(__('nova-cms-portfolio::slideshows.break_after_in_overviews'), 'break_after_in_overviews')
                ->options(config('nova-cms-portfolio.break_sizes'))
                ->default('none')
                ->required()
                ->onlyOnForms();
        }

        $fields[] = HasMany::make($workLabel, 'works', Work::class);

        $artist = $this->artist;
        if (! $artist) {
            $artist = $request->viaResourceId ? Artist::find($request->viaResourceId) : null;
        }

        if ($artist && $artist->disciplines->count() > 1) {
            $options = $artist->disciplines->pluck('title', 'id')->toArray();
            $fields[] = RadioButton::make(ucfirst(__('nova-cms-portfolio::disciplines.discipline')), 'discipline_id')
                ->options($options)
                ->default(key($options));
        }

        return $fields;
    }

    public function cards(Request $request)
    {
        $cards = [];

        $cards[] = (new SlideshowArtistCard)->addMeta($request->resourceId)->onlyOnDetail();
        $cards[] = (new QuickWorksCard())->addMeta($request->resourceId)->onlyOnDetail();

        return $cards;
    }

    public function actions(Request $request)
    {
        return [
            ToggleSlideshowIsPublished::make()
                ->onlyOnTableRow()
                ->withoutConfirmation(),

            ToggleVisibilityInOverview::make()
                ->onlyOnTableRow()
                ->withoutConfirmation(),
        ];
    }

    public function filters(Request $request)
    {
        return [
            new \Kraenkvisuell\NovaCmsPortfolio\Nova\Filters\CategoryFilter,
        ];
    }
}
