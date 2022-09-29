<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Eminiarts\Tabs\Tabs;
use Eminiarts\Tabs\TabsOnEdit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kraenkvisuell\NovaCms\Tabs\Seo;
use Kraenkvisuell\NovaCmsMedia\MediaLibrary;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Manogi\Tiptap\Tiptap;

class Category extends Resource
{
    use TabsOnEdit;

    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Category::class;

    // public static $sortable = false;

    public static function orderBy()
    {
        return [
            'title->'.app()->getLocale() => 'asc',
        ];
    }

    public static $searchable = false;

    public static $perPageOptions = [100, 200];

    public function title()
    {
        return $this->resource->title;
    }

    public static function label()
    {
        return ucfirst(__('nova-cms-portfolio::categories.categories'));
    }

    public static function singularLabel()
    {
        return ucfirst(__('nova-cms-portfolio::categories.category'));
    }

    public static function authorizedToViewAny(Request $request)
    {
        return Auth::user()->cms_role != 'artist';
    }

    public function fields(Request $request)
    {
        $uploadOnly = config('nova-cms-portfolio.media.upload_only') ?: false;

        $slideshowLabel = __(config('nova-cms-portfolio.custom_slideshows_label'))
                       ?: __('nova-cms-portfolio::slideshows.slideshows');

        $slideshowSingularLabel = __(config('nova-cms-portfolio.custom_slideshow_label'))
        ?: __('nova-cms-portfolio::slideshows.slideshow');

        $tabs = [];

        $tabs[__('nova-cms::settings.settings')] = [
            Text::make(__('nova-cms-portfolio::portfolio.title'), 'title')
                ->translatable(),

            Text::make(__('nova-cms::pages.slug'), 'slug')
                ->translatable()
                ->help(__('nova-cms-portfolio::artists.slug_explanation'))
                ->hideFromDetail(),

            Boolean::make(__('nova-cms-portfolio::categories.show_in_home_navi'), 'show_in_home_navi')
                ->onlyOnForms(),

            Boolean::make(__('nova-cms-portfolio::categories.show_in_main_menu'), 'show_in_main_menu')
                ->onlyOnForms(),
        ];

        $tabs[__('nova-cms::pages.content')] = [
            MediaLibrary::make(__('nova-cms-portfolio::categories.main_image'), 'main_image')
                ->uploadOnly($uploadOnly)
                ->hideFromDetail(),

            TipTap::make(__('nova-cms-portfolio::categories.description'), 'description')
                ->translatable()
                ->onlyOnForms(),
        ];

        $tabs[__('nova-cms::seo.seo')] = Seo::make();

        $fields = [
            (new Tabs(static::singularLabel(), $tabs))->withToolbar(),
        ];

        if (config('nova-cms-portfolio.has_category_slideshows')) {
            $fields[] = Stack::make('', [
                Line::make($slideshowLabel, function () use ($slideshowLabel, $slideshowSingularLabel) {
                    return '<button
                                onclick="window.location.href=\'/nova/resources/categories/'.$this->id.'\'"
                                class="btn btn-xs 
                                '.($this->slideshows->count() ? 'btn-primary' : 'btn-danger').'
                                "
                                >'
                        .$this->slideshows->count().' '.($this->slideshows->count() != 1 ? $slideshowLabel : $slideshowSingularLabel)
                        .'</button>';
                })->asHtml(),
            ])
            ->onlyOnIndex();

            $fields[] = BelongsToMany::make($slideshowLabel, 'slideshows', CategorySlideshow::class);
        } else {
            $fields[] = Stack::make('', [
                Line::make($slideshowLabel, function () use ($slideshowLabel, $slideshowSingularLabel) {
                    return '<div
                                class="
                                '.(! $this->slideshows->count() ? 'text-60' : '').'
                                "
                        >'
                        .$this->slideshows->count().' '.($this->slideshows->count() != 1 ? $slideshowLabel : $slideshowSingularLabel)
                        .'</div>';
                })->asHtml(),
            ])
            ->onlyOnIndex();
        }

        return $fields;
    }

    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return '/resources/categories';
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/categories';
    }
}
