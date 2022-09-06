<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Eminiarts\Tabs\Tabs;
use Eminiarts\Tabs\TabsOnEdit;
use Illuminate\Http\Request;
use Kraenkvisuell\BelongsToManyField\BelongsToManyField;
use Kraenkvisuell\NovaCms\Tabs\Seo;
use Kraenkvisuell\NovaCmsBlocks\Blocks;
use Kraenkvisuell\NovaCmsMedia\MediaLibrary;
use Kraenkvisuell\NovaCmsPortfolio\CreateProjectsViaUploadCard;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Filters\Published;
use Kraenkvisuell\NovaCmsPortfolio\ZipUpdateProjectsCard;
use KraenkVisuell\NovaSortable\Traits\HasSortableRows;
use Laravel\Nova\Fields\BelongsToMany;
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

    public function fields(Request $request)
    {
        $slideshowLabel = __(config('nova-cms-portfolio.custom_slideshows_label'))
                       ?: __('nova-cms-portfolio::slideshows.slideshows');

        $slideshowSingularLabel = __(config('nova-cms-portfolio.custom_slideshow_label'))
        ?: __('nova-cms-portfolio::slideshows.slideshow');

        $startpageImageLabel = config('nova-cms-portfolio.custom_startpage_image_label')
            ?: __('Startseiten-Bild');

        $portfolioImagesLabel = config('nova-cms-portfolio.custom_portfolio_images_label')
            ?: __('Portfolio-Bilder');

        $overviewImagesLabel = ucfirst(config('nova-cms-portfolio.custom_show_in_overview_label'))
            ?: __('Übersicht-Bilder');

        $tabs = [];

        $uploadOnly = config('nova-cms-portfolio.media.upload_only') ?: false;

        $tabs[ucfirst(__('nova-cms::settings.settings'))] = [
            Text::make(ucfirst(__('nova-cms-portfolio::artists.title')), 'name')
                ->rules('required')
                ->onlyOnForms(),

            Slug::make(ucfirst(__('nova-cms::pages.slug')), 'slug')->from('name')
                ->rules('required')
                ->creationRules('unique:'.config('nova-cms-portfolio.db_prefix').'artists,slug')
                ->updateRules('unique:'.config('nova-cms-portfolio.db_prefix').'artists,slug,{{resourceId}}')
                ->help(__('nova-cms-portfolio::artists.slug_explanation'))
                ->onlyOnForms(),

            Boolean::make(ucfirst(__('Veröffentlicht')), 'is_published')
                ->hideFromDetail(),

            BelongsToManyField::make(ucfirst(__('nova-cms-portfolio::disciplines.disciplines')), 'disciplines', Discipline::class)
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

            // Text::make('E-Mail', 'email')
            //     ->rules('nullable', 'email')
            //     ->onlyOnForms(),

            // Boolean::make(ucfirst(__('nova-cms-portfolio::artists.can_login')), 'can_login')
            //     ->onlyOnForms(),
        ];

        $tabs[ucfirst(__('nova-cms::pages.content'))] = [
            TipTap::make(ucfirst(__('nova-cms-portfolio::artists.description')), 'description')
                ->translatable()
                ->onlyOnForms(),
        ];

        if (config('nova-cms-portfolio.has_custom_portfolio_image')) {
            $tabs[ucfirst(__('nova-cms::pages.content'))][] = MediaLibrary::make(ucfirst(__('nova-cms-portfolio::artists.portfolio_image')), 'portfolio_image')
                ->uploadOnly($uploadOnly)
                ->onlyOnForms();
        }

        $tabs[ucfirst(__('nova-cms::pages.content'))][] = MediaLibrary::make(ucfirst(__('nova-cms-portfolio::artists.portrait_image')), 'portrait_image')
                ->uploadOnly($uploadOnly)
                ->onlyOnForms();

        $tabs[ucfirst(__('nova-cms::pages.content'))][] = MediaLibrary::make(ucfirst(__('nova-cms-portfolio::artists.sedcard_pdf')), 'sedcard_pdf')
                ->uploadOnly($uploadOnly)
                ->onlyOnForms();

        $tabs[ucfirst(__('nova-cms::pages.content'))][] = Blocks::make('Testimonials', 'testimonials')
                ->addLayout('Testimonial', 'testimonial', [
                    Textarea::make('Text', 'text')
                        ->translatable(),
                    Text::make('Kunde', 'client'),
                ])
                ->useAsTitle(['testimonial' => 'client'])
                ->button('Testimonial hinzufügen')
                ->collapsed()
                ->stacked()
                ->onlyOnForms();

        if (config('nova-cms-portfolio.artists_have_custom_bg')) {
            $tabs[ucfirst(__('nova-cms::pages.content'))][] = Color::make(ucfirst(__('nova-cms-portfolio::portfolio.background_color')), 'bgcolor')
                ->sketch()
                ->hideFromDetail();
        }

        $tabs[__('nova-cms::seo.seo')] = Seo::make();

        $fields = [
            Stack::make('Details', [
                Line::make('', 'name')->asBase(),
                Line::make('', function () {
                    return '/'.$this->slug;
                })->asSmall(),
            ]),

            Text::make($portfolioImagesLabel, function () {
                $html = '<div
                    class="block whitespace-normal"
                >';
                foreach (collect($this->portfolioImages())->take(config('nova-cms-portfolio.number_of_portfolio_images') ?: 3) as $portfolioImage) {
                    $html .= '<a
                        href="'.nova_cms_file($portfolioImage).'"
                        download
                    >';

                    if (nova_cms_mime($portfolioImage) == 'video') {
                        $html .= '<video
                            autoplay muted loop playsinline
                            class="w-auto h-12 mr-1 inline-block"
                        >
                            <source src="'.nova_cms_file($portfolioImage).'" type="video/'.nova_cms_extension($portfolioImage).'">
                        </video>';
                    } else {
                        $html .= '<img
                            class="w-auto h-12 mr-1 inline-block"
                            src="'.nova_cms_image($portfolioImage, 'thumb').'"
                        />';
                    }

                    $html .= '</a>';
                }
                $html .= '</div>';

                return $html;
            })->asHtml(),
        ];

        if (config('nova-cms-portfolio.has_select_startpage_image')) {
            $fields[] = Text::make($startpageImageLabel, function () {
                $html = '<div
                    class="block whitespace-normal"
                >';
                foreach (collect($this->startpageImages()) as $startpageImage) {
                    $html .= '<a
                        href="'.nova_cms_file($startpageImage).'"
                        download
                    >';

                    if (nova_cms_mime($startpageImage) == 'video') {
                        $html .= '<video
                            autoplay muted loop playsinline
                            class="w-auto h-12 mr-1 inline-block"
                        >
                            <source src="'.nova_cms_file($startpageImage).'" type="video/'.nova_cms_extension($startpageImage).'">
                        </video>';
                    } else {
                        $html .= '<img
                            class="w-auto h-12 mr-1 inline-block"
                            src="'.nova_cms_image($startpageImage, 'thumb').'"
                        />';
                    }

                    $html .= '</a>';
                }
                $html .= '</div>';

                return $html;
            })->asHtml();
        }

        if (config('nova-cms-portfolio.artists_have_overview_preview')) {
            $fields[] = Text::make($overviewImagesLabel, function () {
                $html = '<div
                    class="block whitespace-normal"
                >';
                foreach (collect($this->overviewImages()) as $overviewImage) {
                    $html .= '<a
                        href="'.nova_cms_file($overviewImage).'"
                        download
                    >';

                    if (nova_cms_mime($overviewImage) == 'video') {
                        $html .= '<video
                            autoplay muted loop playsinline
                            class="w-auto h-12 mr-1 inline-block"
                        >
                            <source src="'.nova_cms_file($overviewImage).'" type="video/'.nova_cms_extension($overviewImage).'">
                        </video>';
                    } else {
                        $html .= '<img
                            class="w-auto h-12 mr-1 inline-block"
                            src="'.nova_cms_image($overviewImage, 'thumb').'"
                        />';
                    }

                    $html .= '</a>';
                }
                $html .= '</div>';

                return $html;
            })->asHtml()->onlyOnDetail();
        }

        $fields[] = (new Tabs(static::singularLabel(), $tabs))->withToolbar();

        $fields[] = Stack::make('', [
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
        ->onlyOnIndex();

        $fields[] = HasMany::make($slideshowLabel, 'slideshows', Slideshow::class);

        if (config('nova-cms-portfolio.has_artist_category')) {
            $fields[] = BelongsToMany::make(
                __('nova-cms-portfolio::categories.sort_categories'),
                'categories',
                ArtistCategory::class
            );
        }

        return $fields;
    }

    public function cards(Request $request)
    {
        $cards = [];

        if (config('nova-cms-portfolio.has_projects_zip_upload')) {
            $cards[] = (new ZipUpdateProjectsCard())->addMeta($request->resourceId)->onlyOnDetail();
        }

        if (config('nova-cms-portfolio.has_projects_via_upload')) {
            $cards[] = (new CreateProjectsViaUploadCard())->addMeta($request->resourceId)->onlyOnDetail();
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
