<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Eminiarts\Tabs\Tabs;
use Eminiarts\Tabs\TabsOnEdit;
use Illuminate\Http\Request;
use Kraenkvisuell\BelongsToManyField\BelongsToManyField;
use Kraenkvisuell\NovaCms\Tabs\Seo;
use Kraenkvisuell\NovaCmsMedia\MediaLibrary;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Discipline;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Resource;
use Kraenkvisuell\NovaCmsPortfolio\ZipUpdateProjectsCard;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Manogi\Tiptap\Tiptap;
use Timothyasp\Color\Color;

class Artist extends Resource
{
    use TabsOnEdit;

    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Artist::class;

    public static $title = 'name';

    public static $perPageOptions = [100, 200];

    public static $orderBy = [
        'name' => 'asc',
    ];

    public static $search = [
        'name', 'slug',
    ];

    public static function label()
    {
        return __(config('nova-cms-portfolio.custom_artists_label'))
        ?: ucfirst(__('nova-cms-portfolio::artists.artists'));
    }

    public static function singularLabel()
    {
        return __(config('nova-cms-portfolio.custom_artist_label'))
        ?: ucfirst(__('nova-cms-portfolio::artists.artist'));
    }

    public function fields(Request $request)
    {
        $slideshowLabel = __(config('nova-cms-portfolio.custom_slideshows_label'))
                       ?: __('nova-cms-portfolio::slideshows.slideshows');

        $slideshowSingularLabel = __(config('nova-cms-portfolio.custom_slideshow_label'))
        ?: __('nova-cms-portfolio::slideshows.slideshow');

        $tabs = [];

        $uploadOnly = config('nova-cms-portfolio.media.upload_only') ?: false;

        $tabs[__('nova-cms::settings.settings')] = [
            Text::make(__('nova-cms-portfolio::artists.title'), 'name')
                ->rules('required')
                ->onlyOnForms(),

            Slug::make(__('nova-cms::pages.slug'), 'slug')->from('name')
                ->rules('required')
                ->creationRules('unique:'.config('nova-cms-portfolio.db_prefix').'artists,slug')
                ->updateRules('unique:'.config('nova-cms-portfolio.db_prefix').'artists,slug,{{resourceId}}')
                ->help(__('nova-cms-portfolio::artists.slug_explanation'))
                ->onlyOnForms(),

            BelongsToManyField::make(__('nova-cms-portfolio::disciplines.disciplines'), 'disciplines', Discipline::class)
            ->optionsLabel('title')
            ->hideFromDetail(),

            Color::make(__('nova-cms-portfolio::portfolio.background_color'), 'bgcolor')
            ->sketch()
            ->hideFromDetail(),
        ];

        $tabs[__('nova-cms::pages.content')] = [
            TipTap::make(__('nova-cms-portfolio::artists.description'), 'description')
            ->onlyOnForms(),

            MediaLibrary::make(__('nova-cms-portfolio::artists.portfolio_image'), 'portfolio_image')
                ->uploadOnly($uploadOnly)
                ->onlyOnForms(),

            MediaLibrary::make(__('nova-cms-portfolio::artists.portrait_image'), 'portrait_image')
                ->uploadOnly($uploadOnly)
                ->onlyOnForms(),
        ];

        $tabs[__('nova-cms::seo.seo')] = Seo::make();

        return [
            Stack::make('Details', [
                Line::make('', 'name')->asBase(),
                Line::make('', function () {
                    return '/'.$this->slug;
                })->asSmall(),
            ]),

            (new Tabs(static::singularLabel(), $tabs))->withToolbar(),

            Stack::make('', [
                Line::make($slideshowLabel, function () use ($slideshowLabel, $slideshowSingularLabel) {
                    return '<button
                        onclick="window.location.href=\'/nova/resources/artists/'.$this->id.'\'"
                        class="btn btn-xs 
                        '.($this->slideshows->count() ? 'btn-primary' : 'btn-danger').'
                        "
                        >'
                        .$this->slideshows->count().' '.($this->slideshows->count() != 1 ? $slideshowLabel : $slideshowSingularLabel)
                        .'</button>';
                })->asHtml(),
            ])
            ->onlyOnIndex(),

            HasMany::make($slideshowLabel, 'slideshows', Slideshow::class),
        ];
    }

    public function cards(Request $request)
    {
        return [
            (new ZipUpdateProjectsCard())->addMeta($request->resourceId)->onlyOnDetail(),
        ];
    }
}
