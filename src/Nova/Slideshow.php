<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Laravel\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use OwenMelbz\RadioField\RadioButton;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Category;
use Kraenkvisuell\NovaCmsPortfolio\QuickWorksCard;
use Kraenkvisuell\NovaCmsPortfolio\SlideshowArtistCard;
use Kraenkvisuell\BelongsToManyField\BelongsToManyField;
use OptimistDigital\NovaSortable\Traits\HasSortableRows;

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

    public function fields(Request $request)
    {
        $workLabel = __(config('nova-cms-portfolio.custom_works_label'))
                       ?: __('nova-cms-portfolio::works.works');

        $workSingularLabel = __(config('nova-cms-portfolio.custom_work_label'))
        ?: __('nova-cms-portfolio::works.work');

        $fields = [
            Text::make(__('nova-cms::pages.title'), 'title')
                ->rules('required'),

            Slug::make(__('nova-cms::pages.slug'), 'slug')->from('title')
                ->rules('required')
                ->creationRules('unique:'.config('nova-cms-portfolio.db_prefix').'slideshows,slug')
                ->updateRules('unique:'.config('nova-cms-portfolio.db_prefix').'slideshows,slug,{{resourceId}}')
                ->onlyOnForms(),

            Boolean::make(ucfirst(__('nova-cms-portfolio::portfolio.published')), 'is_published')
                ->onlyOnForms(),

            Boolean::make(ucfirst(__('nova-cms-portfolio::slideshows.visible_in_artist_overview')), 'is_visible_in_overview')
                ->onlyOnForms(),

            BelongsToManyField::make(__('nova-cms-portfolio::categories.categories'), 'categories', Category::class)
                ->optionsLabel('title'),

            Stack::make('', [
                Line::make($workLabel, function () use ($workLabel, $workSingularLabel) {
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

            HasMany::make($workLabel, 'works', Work::class),
        ];

        $artist = $this->artist;
        if (!$artist) {
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
