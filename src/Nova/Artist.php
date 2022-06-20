<?php
namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Eminiarts\Tabs\Tabs;
use Eminiarts\Tabs\TabsOnEdit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kraenkvisuell\BelongsToManyField\BelongsToManyField;
use Kraenkvisuell\NovaCms\Tabs\Seo;
use Kraenkvisuell\NovaCmsBlocks\Blocks;
use Kraenkvisuell\NovaCmsMedia\MediaLibrary;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Discipline;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Filters\Published;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Resource;
use Kraenkvisuell\NovaCmsPortfolio\ZipUpdateProjectsCard;
use KraenkVisuell\NovaSortable\Traits\HasSortableRows;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Manogi\Tiptap\Tiptap;
use Timothyasp\Color\Color;

class Artist extends Resource
{
    use TabsOnEdit;
    use HasSortableRows;

    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Artist::class;

    public static $title = 'name';

    public static $sortable = false;

    public static $perPageOptions = [200, 400];

    public static $search = [
        'name', 'slug',
    ];

    public static function sortableHasDropdown()
    {
        return config('nova-cms-portfolio.artists_sortable_dropdown') ?: false;
    }

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

    public function authorizedToView(Request $request)
    {
        if (Auth::user()->cms_role == 'artist') {
            return Auth::user()->artist_id == $this->id;
        }

        return true;
    }

    public static function authorizedToCreate(Request $request)
    {
        return Auth::user()->cms_role != 'artist';
    }

    public function authorizedToDelete(Request $request)
    {
        if (Auth::user()->cms_role == 'artist') {
            return Auth::user()->artist_id == $this->id;
        }

        return true;
    }

    public function authorizedToUpdate(Request $request)
    {
        if (Auth::user()->cms_role == 'artist') {
            return Auth::user()->artist_id == $this->id;
        }

        return true;
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
                ->creationRules('unique:' . config('nova-cms-portfolio.db_prefix') . 'artists,slug')
                ->updateRules('unique:' . config('nova-cms-portfolio.db_prefix') . 'artists,slug,{{resourceId}}')
                ->help(__('nova-cms-portfolio::artists.slug_explanation'))
                ->onlyOnForms(),

            Boolean::make(__('Veröffentlicht'), 'is_published')
                ->hideFromDetail(),

            BelongsToManyField::make(__('nova-cms-portfolio::disciplines.disciplines'), 'disciplines', Discipline::class)
                ->optionsLabel('title')
                ->hideFromDetail(),

            Blocks::make(__('nova-cms::content_blocks.social_links'), 'social_links')
                ->addLayout(__('nova-cms::content_blocks.link'), 'link', [
                    Text::make(__('nova-cms::content_blocks.link_title'), 'link_title')->translatable(),

                    Text::make(__('nova-cms::content_blocks.link_url'), 'link_url')->translatable(),

                    Text::make(__('nova-cms::content_blocks.id'), 'slug'),

                    MediaLibrary::make(__('nova-cms::content_blocks.link_icon'), 'link_icon')
                        ->types(['Image']),

                    Code::make(__('nova-cms::content_blocks.svg_tag'), 'svg_tag')->language('xml'),
                ])
                ->button(__('nova-cms::content_blocks.add_social_link'))
                ->stacked()
                ->onlyOnForms(),

            Text::make('Website', 'website')
                ->onlyOnForms(),

            Text::make('E-Mail', 'email')
                ->rules('nullable', 'email')
                ->onlyOnForms(),

            Boolean::make(__('nova-cms-portfolio::artists.can_login'), 'can_login')
                ->onlyOnForms(),
        ];

        if (config('nova-cms-portfolio.artists_have_custom_bg')) {
            $tabs[__('nova-cms::pages.content')][] = Color::make(__('nova-cms-portfolio::portfolio.background_color'), 'bgcolor')
                ->sketch()
                ->hideFromDetail();
        }

        $tabs[__('nova-cms::pages.content')] = [
            TipTap::make(__('nova-cms-portfolio::artists.description'), 'description')
                ->translatable()
                ->onlyOnForms(),

            MediaLibrary::make(__('nova-cms-portfolio::artists.portfolio_image'), 'portfolio_image')
                ->uploadOnly($uploadOnly)
                ->onlyOnForms(),

            MediaLibrary::make(__('nova-cms-portfolio::artists.portrait_image'), 'portrait_image')
                ->uploadOnly($uploadOnly)
                ->onlyOnForms(),

            MediaLibrary::make(__('nova-cms-portfolio::artists.sedcard_pdf'), 'sedcard_pdf')
                ->uploadOnly($uploadOnly)
                ->onlyOnForms(),

            Blocks::make('Testimonials', 'testimonials')
                ->addLayout('Testimonial', 'testimonial', [
                    Textarea::make('Text', 'text')
                        ->translatable(),
                    Text::make('Kunde', 'client'),
                ])
                ->useAsTitle(['testimonial' => 'client'])
                ->button('Testimonial hinzufügen')
                ->collapsed()
                ->stacked()
                ->onlyOnForms(),
        ];

        $tabs[__('nova-cms::seo.seo')] = Seo::make();

        return [
            Stack::make('Details', [
                Line::make('', 'name')->asBase(),
                Line::make('', function () {
                    return '/' . $this->slug;
                })->asSmall(),
            ]),

            (new Tabs(static::singularLabel(), $tabs))->withToolbar(),

            Stack::make('', [
                Line::make($slideshowLabel, function () use ($slideshowLabel, $slideshowSingularLabel) {
                    return '<button
                        onclick="window.location.href=\'/nova/resources/artists/' . $this->id . '\'"
                        class="btn btn-xs 
                        ' . ($this->slideshows->count() ? 'btn-primary' : 'btn-danger') . '
                        "
                        >'
                        . $this->slideshows->count() . ' ' . ($this->slideshows->count() != 1 ? $slideshowLabel : $slideshowSingularLabel)
                        . '</button>';
                })->asHtml(),
            ])
            ->onlyOnIndex(),

            HasMany::make($slideshowLabel, 'slideshows', Slideshow::class),
        ];
    }

    public function cards(Request $request)
    {
        $cards = [];

        if (config('nova-cms-portfolio.has_projects_zip_upload')) {
            $cards[] = (new ZipUpdateProjectsCard())->addMeta($request->resourceId)->onlyOnDetail();
        }

        return $cards;
    }

    public function filters(Request $request)
    {
        return [
            new Published,
        ];
    }
}
