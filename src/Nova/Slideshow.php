<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Illuminate\Http\Request;
use Kraenkvisuell\BelongsToManyField\BelongsToManyField;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Category;
use Kraenkvisuell\NovaCmsPortfolio\QuickWorksCard;
use Kraenkvisuell\NovaCmsPortfolio\SlideshowArtistCard;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use OptimistDigital\NovaSortable\Traits\HasSortableRows;
use OwenMelbz\RadioField\RadioButton;

class Slideshow extends Resource
{
    use HasSortableRows;

    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow::class;

    public static $title = 'title';

    public static $sortable = false;

    public static $searchable = false;

    public static $displayInNavigation = false;

    public static $perPageViaRelationship = 1000;

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
        return $query->with(['works']);
    }

    public function fields(Request $request)
    {
        $workLabel = __(config('nova-cms-portfolio.custom_works_label'))
                       ?: __('nova-cms-portfolio::works.works');

        $workSingularLabel = __(config('nova-cms-portfolio.custom_work_label'))
        ?: __('nova-cms-portfolio::works.work');

        $fields = [
            Text::make(__('nova-cms::pages.title'), 'title')
                ->rules('required'),

            BelongsToManyField::make(__('nova-cms-portfolio::categories.categories'), 'categories', Category::class)
                ->optionsLabel('title'),

            Stack::make($workLabel, [
                Line::make('', function () {
                    $html = '<a
                        href="/nova/resources/slideshows/'.$this->id.'"
                        class=""
                    >';
                    foreach ($this->works->take(3) as $work) {
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
                    }
                    $html .= '</a>';

                    return $html;
                })->asHtml(),
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

            Boolean::make(ucfirst(__('nova-cms-portfolio::slideshows.visible_in_artist_overview')), 'is_visible_in_overview')
                ->onlyOnForms(),

            Select::make(__('nova-cms-portfolio::works.title_position'), 'title_position')
                ->options([
                    'bottom_left' => 'bottom left',
                    'bottom_right' => 'bottom right',
                    'top_left' => 'top left',
                    'top_right' => 'top right',
                ])
                ->onlyOnForms(),

            Select::make(__('nova-cms-portfolio::slideshows.break_after_in_overviews'), 'break_after_in_overviews')
                ->options(config('nova-cms-portfolio.break_sizes'))
                ->onlyOnForms(),

            HasMany::make($workLabel, 'works', Work::class),
        ];

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
        return [
            (new QuickWorksCard)->addMeta($request->resourceId)->onlyOnDetail(),
            (new SlideshowArtistCard)->addMeta($request->resourceId)->onlyOnDetail(),
        ];
    }
}
